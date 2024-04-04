@if ($payment)
    <br />
    <p>{{ trans('plugins/payment::payment.payment_id') }}: {{ $payment->getPayments()[0]->paymentId }}</p>
    <p>{{ trans('plugins/payment::payment.amount') }}: {{ number_format($payment->getPayments()[0]->paidPrice, 2) }} {{ $payment->getPayments()[0]->currency}}</p>
    @php
        $amountRemaining = $payment->getPayments()[0]->paidPrice - json_decode($payment->getRawResult())->payments[0]->itemTransactions[1]->refunds[0]->refundPrice;
    @endphp
    <p>{{ trans('plugins/payment::payment.amount_remaining') }}: {{ $amountRemaining }} {{ json_decode($payment->getRawResult())->payments[0]->currency }}</p>
    @php
        $paymentStatus = "Success";
        if ($payment->getPayments()[0]->paymentStatus !== 1) $paymentStatus = "Failure";
    @endphp
    <p>{{ trans('plugins/payment::payment.status') }}: {{ __($paymentStatus) }}</p>
    <p>{{ trans('plugins/payment::payment.paid_at') }}: {{ Carbon\Carbon::now()->parse($payment->getPayments()[0]->createdDate) }}</p>

    @if ($payment->getPayments()[0]->paymentRefundStatus !== 'NOT_REFUNDED')
        @php
            $amountRefunded = '';
            if (json_decode($payment->getRawResult())->payments[0]->itemTransactions[1]->refunds[0]->refundPrice) {
                $amountRefunded = ' (' . number_format(json_decode($payment->getRawResult())->payments[0]->itemTransactions[1]->refunds[0]->refundPrice, 2) . ' ' . json_decode($payment->getRawResult())->payments[0]->itemTransactions[1]->refunds[0]->currencyCode . ')';
            }
            $itemTransactions = $payment->getPayments()[0]->itemTransactions;
        @endphp		
        <br />
        <h4 class="alert-heading">{{ trans('plugins/payment::payment.refunds.title') . $amountRefunded }}</h6>
        <hr class="m-0 mb-4">
        @foreach ($itemTransactions as $key => $value)		
            @foreach ($value->refunds as $refund)		
                <div class="alert alert-warning" role="alert">
                    <p>{{ trans('plugins/payment::payment.refunds.id') }}: {{ htmlspecialchars($refund->refundTxId) }}</p>
                    <p>{{ trans('plugins/payment::payment.amount') }}: {{ number_format($refund->refundPrice, 2) }} {{ $refund->currencyCode }}</p>
                    <p>{{ trans('plugins/payment::payment.refunds.description') }}: {{ $payment->getPayments()[0]->paymentRefundStatus }}</p>
                    @php
                        $refundStatus = "Success";
                        if ($refund->refundStatus !== 1) $refundStatus = "Failure";
                    @endphp
                    <p>{{ trans('plugins/payment::payment.refunds.status') }}: {{ __($refundStatus) }}</p>
                    <p>{{ trans('plugins/payment::payment.refunds.create_time') }}: {{ Carbon\Carbon::now()->parse($refund->createdDate) }}</p>
                </div>
            @endforeach      
        @endforeach
        <br />
    @endif

    @include('plugins/payment::partials.view-payment-source')
@endif
