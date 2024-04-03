<?php

namespace Botble\Iyzico\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\Support\Services\ProduceServiceInterface;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Botble\Iyzico\Http\Controllers\IyzicoConfig;
use Iyzipay\Request\CreateRefundRequest;
use Iyzipay\Request\ReportingPaymentDetailRequest;
use Iyzipay\Model\ReportingPaymentDetail;
use Iyzipay\Model\Refund;
use Iyzipay\Model\Locale;
use Iyzipay\Options;

abstract class IyzicoPaymentAbstract implements ProduceServiceInterface
{
    use PaymentErrorTrait;

    protected string $paymentCurrency;
	
	 /**
     * @var object
     */
    protected $api;

    protected bool $supportRefundOnline;

    public function __construct()
    {
        $this->paymentCurrency = config('plugins.payment.payment.currency');

        $this->setApi();

        $this->supportRefundOnline = true;

    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }
	
	public function setApi(): self
    {
		# create request class
        $this->api = new CreateRefundRequest();

        return $this;
    }

    /**
     * @return object
     */
    public function getApi()
    {
        return $this->api;
    }
	
	public function getPaymentDetails(string $conversationId)
    {
        try {

			$this->api = new ReportingPaymentDetailRequest();
			$this->api->setPaymentConversationId($conversationId);
			$response = ReportingPaymentDetail::create($this->api, (new IyzicoConfig)->options());

        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }

        return $response;
    }
	
    /**
     * This function can be used to preform refund on the capture.
     */
    public function refundOrder($paymentId, $amount, array $options = [])
    {
        try {

			$this->api->setLocale(\Request::getLocale());
			
			$payment = app(PaymentInterface::class)->getFirstBy([
				'charge_id' => $paymentId,
				['order_id', 'IN', array_map('intval', explode(',', Arr::get($options, 'order_id', 0)))],
			]);
			$this->api->setConversationId(Arr::get($payment->metadata, 'conversation_id', ''));
			
			$this->api->setPaymentTransactionId(Arr::get($payment->metadata, 'paymentTransaction_id', ''));
			$this->api->setPrice(number_format((float) $amount, 2, '.', ''));
			$currency = $this->paymentCurrency;
            if($currency == "TRY") $currency = "TL";
			$this->api->setCurrency(constant('Iyzipay\Model\Currency::' . $currency));
			
			$this->api->setIp(\Request::ip());
			$this->api->setReason(\Iyzipay\Model\RefundReason::OTHER);			
			$description = 'Order information: ' . get_order_code(Arr::get($options, 'order_id', ''));
			$description .= ', Detail: ' . Arr::get($options, 'refund_note', '');
			$this->api->setDescription(Str::limit($description, 140));

			# make request
			$refund = Refund::create($this->api, (new IyzicoConfig)->options());

            if ( $refund->getStatus() == \Iyzipay\Model\Status::SUCCESS && $refund->getCurrency() == $this->paymentCurrency && (float) $refund->getPrice() >= (float) $amount) {                

                return [
                    'error' => false,
                    'message' => "{$refund->getCurrency()} {$refund->getPrice()} of payment {$paymentId} refunded.",
                    'data' => (array) $refund,
                ];
            }

            return [
                'error' => true,
		        'message' => "Payment {$paymentId} can not be refunded. Code: {$refund->geterrorCode()}, Message: {$refund->geterrorMessage()}",
            ];
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function execute(Request $request)
    {
        try {
            return $this->makePayment($request);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }
    }

    abstract public function makePayment(Request $request);

    abstract public function afterMakePayment(Request $request);
}
