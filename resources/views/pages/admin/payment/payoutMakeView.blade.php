<table id="payoutMakeView" class="table table-bordered">
    <thead>
        <tr>
            <th>Select All</th>
            <th>Vendor</th>
            <th>Booking</th>
            <th>Transection</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($vendorPayoutItems as $item)
            <tr>
                <td><input type="checkbox" class="payout-item-checkbox" data-amount="{{ $item->pbvpi_amount }}" data-id="{{ $item->pbvpi_id }}"></td>
                <td>{{ $item->vendor->pbv_business_name }}</td>
                <td>{{ $item->booking->pb_booking_id }}</td>
                <td>{{ $item->payment->pbpt_reference }}</td>
                <td>{{ 'Rs. ' . number_format($item->pbvpi_amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No payout items found</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
    $(document).ready(function () {
    });
</script>