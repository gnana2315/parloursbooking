<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\booking;
use App\Models\notification;
use App\Models\User;

use App\Services\OneSignalService;
use Illuminate\Support\Facades\Log;

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
    public function handle(OneSignalService $oneSignalService)
    {
        $now = Carbon::now();
        $durationVariable = 3;

        // 🔔 Send reminder 30 minutes before booking
        $bookings = booking::with(['customer', 'vendors'])
            ->where('pbb_status', 1) // confirmed
            ->where('pbb_reminder_sent', 0)
            ->whereRaw("
                TIMESTAMP(pbb_booking_date, pbb_booking_start_time)
                BETWEEN ? AND ?
            ", [
                $now->copy()->addHours($durationVariable),
                $now->copy()->addHours($durationVariable)->addMinutes(1)
            ])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings found for reminders at this time.');
            Log::info('Booking reminder command triggered at ' . now() . ' - No bookings to process.');
            return Command::SUCCESS;
        }

        foreach ($bookings as $booking) {

            $vendor = $booking->vendors->first();
            $customer = $booking->customer;
            $getUser = User::where('pbu_vid', $vendor->pbv_id)->first();
            $vendorName = $booking->vendors->first()?->pbv_business_name ?? 'N/A';
            $vendorContactNo = $booking->vendors->first()?->pbv_contactno ? $booking->vendors->first()?->pbv_contactno : $getUser?->pbu_mobileno ?? 'N/A';

            Log::info("Preparing to send reminder for booking ID: {$booking->pbb_id} to customer user ID: {$customer->pbc_user_id}"); 
            // 🔔 Push Notification
            $customerReminderNotification = $oneSignalService->sendToUser(
                $customer->pbc_user_id,
                '⏰ Booking Reminder',
                "Gentle Reminder: Your appointment today at {$booking->pbb_booking_start_time->format('h:i A')} with {$vendorName}. For assistance, please contact the parlour at {$vendorContactNo}.",
                [
                    'booking_id' => $booking->pbb_id,
                    'booking_ref_no' => $booking->pbb_ref_no,
                ]
            );

            if ($customerReminderNotification) {
                // ✅ Mark reminder sent
                $booking->update(['pbb_reminder_sent' => 1]);

                // 📲 Log notification
                notification::create([
                    'pbn_user_id' => $customer->pbc_user_id,
                    'pbn_type' => 'reminder',
                    'pbn_title' => 'Booking Reminder',
                    'pbn_message' => "Gentle Reminder: Your appointment today at {$booking->pbb_booking_start_time->format('h:i A')} with {$vendorName}. For assistance, please contact the parlour at {$vendorContactNo}.",
                ]);

                $this->info("Reminder sent for booking ID: {$booking->pbb_id}");
                Log::info("Booking reminder command triggered at " . now());
            }else{
                Log::error("Failed to send reminder notification for booking ID: {$booking->pbb_id}");
                continue; // Skip to next booking if notification fails
            }
        }
        return Command::SUCCESS;
    }
}
