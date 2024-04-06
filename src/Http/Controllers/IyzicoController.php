<?php

namespace Botble\Iyzico\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Botble\Payment\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Exception;

use Botble\Iyzico\Http\Controllers\IyzicoConfig;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\Locale;
use Iyzipay\Options;

class IyzicoController extends BaseController
{
    public function paymentCallback(Request $request, BaseHttpResponse $response)
    {
        try {
            /**
             * @var IyzicoApiClient $api
             */
            $api = new RetrieveCheckoutFormRequest();
            $api->setLocale($request->getLocale());
            $api->setConversationId($request->session()->get('tracked_start_checkout'));
            $api->setToken($request->input('token'));
            
            # make request
            $checkoutForm = CheckoutForm::retrieve($api, (new IyzicoConfig)->options());
            
            if(strtolower($checkoutForm->getPaymentStatus()) !== \Iyzipay\Model\Status::SUCCESS) {
                return $response
                    ->setError()
                    ->setNextUrl(PaymentHelper::getCancelURL())
                    ->setMessage($checkoutForm->geterrorCode() . ", message: " . $checkoutForm->geterrorMessage());                
            }
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->setMessage($exception->getMessage());
        }

        $status = PaymentStatusEnum::COMPLETED;
        if (in_array($status, [PaymentStatusEnum::FAILED, PaymentStatusEnum::FRAUD])) {
            $status = PaymentStatusEnum::PENDING;
        }
        
        do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
            'amount' => $checkoutForm->getPaidPrice(),
            'currency' => $checkoutForm->getCurrency(),
            'charge_id' => $checkoutForm->getPaymentId(),
            'payment_channel' => IYZICO_PAYMENT_METHOD_NAME,
            'status' => $status,
            'customer_id' => $request->session()->get('customer_id'),
            'customer_type' => $request->session()->get('customer_type'),
            'payment_type' => 'direct',
            'order_id' => array_map('intval', explode(',', $checkoutForm->getBasketId())),
        ]);
		
        $payments = app(PaymentInterface::class)->allBy([
            'charge_id' => $checkoutForm->getPaymentId(),
            ['order_id', 'IN', array_map('intval', explode(',', $checkoutForm->getBasketId()))],
        ]);
		
		foreach ($payments as $payment) {
			$paymentItems = $checkoutForm->getPaymentItems();			
			foreach ($paymentItems as $paymentItem) {
				$paymentMetadata = app(PaymentInterface::class)->getFirstBy([
					'id' => $payment->id,
					'amount' => number_format((float)($paymentItem->getPaidPrice()), 2, '.', ''),
				]);
				
				if( !empty($paymentMetadata) ) {
					Arr::set($metadata, 'conversation_id', $request->session()->get('tracked_start_checkout'));
					Arr::set($metadata, 'paymentTransaction_id', $paymentItem->getPaymentTransactionId());
					$paymentMetadata->metadata = $metadata;
					$paymentMetadata->save();
				}
			}
		}
					
        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL())
            ->setMessage(__('Checkout successfully!'));
    }
}
