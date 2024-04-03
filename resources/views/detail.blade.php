@if ($payment)
	<br />
	<p>{{ trans('plugins/payment::payment.payment_id') }}: {{ $payment->getPayments()[0]->paymentId }}</p>
	<p>{{ trans('plugins/payment::payment.amount') }}: {{ number_format($payment->getPayments()[0]->paidPrice, 2) }} {{ $payment->getPayments()[0]->currency}}</p>
	<!--<p>{{-- trans('plugins/payment::payment.amount_remaining') --}}: {{-- $payment->amountRemaining->value --}} {{-- $payment->amountRemaining->currency --}}</p>-->
	@php
		$paymentStatus = "Success";
		if ($payment->getPayments()[0]->paymentStatus !== 1) $paymentStatus = "Failure";
	@endphp
    <p>{{ trans('plugins/payment::payment.status') }}: {{ __($paymentStatus) }}</p>
	<p>{{ trans('plugins/payment::payment.paid_at') }}: {{ Carbon\Carbon::now()->parse($payment->getPayments()[0]->createdDate) }}</p>
	
	@if ($payment->getPayments()[0]->itemTransactions[0]->refunds[0]->refundStatus)
        @php
            $amountRefunded = '';
            if ((float) $payment->getPayments()[0]->itemTransactions[0]->refunds[0]->refundPrice) {
                $amountRefunded = ' (' . number_format($payment->getPayments()[0]->itemTransactions[0]->refunds[0]->refundPrice, 2) . ' ' . $payment->getPayments()[0]->itemTransactions[0]->refunds[0]->currencyCode . ')';
            }
            $refunds = $payment->getPayments()[0]->itemTransactions[0]->refunds;
        @endphp
        <br />
        <h4 class="alert-heading">{{ trans('plugins/payment::payment.refunds.title') . $amountRefunded }}</h6>
        <hr class="m-0 mb-4">
	
        @foreach ($refunds as $refund)		
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
            <br />
        @endforeach
    @endif

    @include('plugins/payment::partials.view-payment-source')
@endif
