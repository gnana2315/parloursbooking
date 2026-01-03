<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\booking;
use App\Services\OneSignalService;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send booking reminder notifications to Customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 🔔 Send reminder 30 minutes before booking
        $bookings = booking::with(['customer', 'vendors'])
            ->where('pbb_status', 1) // confirmed
            ->where('pbb_reminder_sent', 0)
            ->whereRaw("
                TIMESTAMP(pbb_booking_date, pbb_booking_start_time)
                BETWEEN ? AND ?
            ", [
                $now->copy()->addMinutes(29),
                $now->copy()->addMinutes(30)
            ])
            ->get();

        foreach ($bookings as $booking) {

            $vendor = $booking->vendors->first();
            $customer = $booking->customer;

            // 🔔 Push Notification
            $oneSignalService->sendToUser(
                $customer->pbc_user_id,
                '⏰ Booking Reminder',
                "Reminder: Your appointment is at {$booking->pbb_booking_start_time->format('h:i A')}. Your Booking Reference No is {$booking->pbb_ref_no}.",
                [
                    'booking_id' => $booking->pbb_id,
                    'booking_ref_no' => $booking->pbb_ref_no,
                ]
            );

            // ✅ Mark reminder sent
            $booking->update(['pbb_reminder_sent' => 1]);

            $this->info("Reminder sent for booking ID: {$booking->pbb_id}");
        }

        return Command::SUCCESS;
    }
}
