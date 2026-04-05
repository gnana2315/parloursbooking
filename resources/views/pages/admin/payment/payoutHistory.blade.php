<table id="payoutHistoryTable" class="table table-bordered">
    <thead>
        <tr>
            <th>Reference</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payoutHistory as $history)
            <tr>
                <td>{{ $history->pbvph_reference }} <span class="badge badge-info"> {{ $history->pbvph_payment_method }}</span></td>
                <td>{{ date('Y-m-d', $history->created_at) }}</td>
                <td>{{ 'Rs. ' . number_format($history->pbvph_amount, 2) }}</td>
                <td>{{ $history->pbvph_description }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No payout history found</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
    $(document).ready(function () {
        $("#payoutHistoryTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": true,
            "paging": true, 
            "buttons": ["csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#payoutHistoryTable_wrapper .col-md-6:eq(0)');
    });
</script>