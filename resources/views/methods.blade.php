@if (get_payment_setting('status', IYZICO_PAYMENT_METHOD_NAME) == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_{{ IYZICO_PAYMENT_METHOD_NAME }}"
            value="{{ IYZICO_PAYMENT_METHOD_NAME }}" @if ($selecting == IYZICO_PAYMENT_METHOD_NAME) checked @endif>
        <label for="payment_{{ IYZICO_PAYMENT_METHOD_NAME }}">{{ get_payment_setting('name', IYZICO_PAYMENT_METHOD_NAME) }}</label>
        <div class="payment_{{ IYZICO_PAYMENT_METHOD_NAME }}_wrap payment_collapse_wrap collapse @if ($selecting == IYZICO_PAYMENT_METHOD_NAME) show @endif">
            <p>{!! get_payment_setting('description', IYZICO_PAYMENT_METHOD_NAME, __('Payment with Iyzico')) !!}</p>
        </div>
    </li>
@endif

@if (session()->has('success_msg') && session('success_msg') && session()->has('paymentcontent_msg') && session('paymentcontent_msg'))
    {!! session('paymentcontent_msg') !!}
@endif
