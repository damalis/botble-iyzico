<?php

namespace Botble\Iyzico\Providers;

use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Ecommerce\Facades\Cart;
use Collective\Html\HtmlFacade as Html;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Exception;

use Botble\Iyzico\Services\Gateways\IyzicoPaymentService;
use Botble\Iyzico\Http\Controllers\IyzicoConfig;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
//use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Options;
//use Iyzipay\Request\CreateCheckoutFormInitializeRequest;

use Iyzipay\Model\PayWithIyzicoInitialize;
use Iyzipay\Request\CreatePayWithIyzicoInitializeRequest;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    { 
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerIyzicoMethod'], 117, 2);
        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithIyzico'], 117, 2);        
        
        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 199);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['IYZICO'] = IYZICO_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 23, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == IYZICO_PAYMENT_METHOD_NAME) {
                $value = 'Iyzico';
            }

            return $value;
        }, 23, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == IYZICO_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 23, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == IYZICO_PAYMENT_METHOD_NAME) {
                $data = IyzicoPaymentService::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == IYZICO_PAYMENT_METHOD_NAME) {
                try {
                    $paymentService = (new IyzicoPaymentService());
                    $paymentDetail = $paymentService->getPaymentDetails(Arr::get($payment->metadata, 'conversation_id', ''));
					
                    if ($paymentDetail) {
                        $data = view('plugins/iyzico::detail', ['payment' => $paymentDetail])->render();
                    }
                } catch (Exception) {
                    return $data;
                }
            }

            return $data;
        }, 20, 2);

    }

    public function addPaymentSettings(string|null $settings): string
    {
        return $settings . view('plugins/iyzico::settings')->render();
    }

    public function registerIyzicoMethod(string|null $html, array $data): string|null
    {           
        PaymentMethods::method(IYZICO_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/iyzico::methods', $data)->render(),
        ]);
        
        return $html;
    }

    public function checkoutWithIyzico(array $data, Request $request)
    {
        if ($request->input('payment_method') == IYZICO_PAYMENT_METHOD_NAME) {
            
            $orderIds = implode(",", $request->input('order_id', []));
            $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);
            
            $checkoutToken = $request->input('checkout-token');
            $products = Cart::instance('cart')->products();
            $groupedProducts = $this->cartGroupByStore($products);

            try {
                /** @var IyzicoApiClient $api */				
                # create request class
                //$api = new CreateCheckoutFormInitializeRequest();
                $api = new CreatePayWithIyzicoInitializeRequest();
                $api->setLocale($request->getLocale());
                $api->setConversationId($checkoutToken);
                //$api->setPrice("1");
                //$api->setPaidPrice("1.2");
                $currency = $request->input('currency');
                if($currency == "TRY") $currency = "TL";
                $api->setCurrency(constant('Iyzipay\Model\Currency::' . $currency));
                $api->setBasketId($orderIds);
                $api->setPaymentGroup(PaymentGroup::PRODUCT);
                $api->setCallbackUrl(request()->getSchemeAndHttpHost() . "/iyzico/payment/callback/" . $checkoutToken);
                //$api->setEnabledInstallments(array(3, 6, 9, 12));
				
				$buyer = new Buyer();
                $request->session()->put('customer_id', $request->input('customer_id'));
                $request->session()->put('customer_type', $request->input('customer_type'));
                $customerId = $request->input('customer_id');
                if(! $customerId ) $customerId = $checkoutToken;
                $buyer->setId($customerId);
                $buyer->setName($request->input('address')['name']);
                $buyer->setSurname($request->input('address')['name']);
                $buyer->setGsmNumber($request->input('address')['phone']);
                $buyer->setEmail($request->input('address')['email']);
                $buyer->setIdentityNumber("74300864791");
                //$buyer->setLastLoginDate(date('Y-m-d H:i:s'));
                //$buyer->setRegistrationDate(date('Y-m-d H:i:s'));
                $buyer->setRegistrationAddress($request->input('address')['address']);
                $buyer->setIp($request->ip());
                $buyer->setCity($request->input('address')['city']);
                $buyer->setCountry($request->input('address')['country']);
                $buyer->setZipCode($request->input('address')['zip_code']);
                $api->setBuyer($buyer);

                $shippingAddress = new Address();
                $shippingAddress->setContactName($request->input('address')['name']);
                $shippingAddress->setCity($request->input('address')['city']);
                $shippingAddress->setCountry($request->input('address')['country']);
                $shippingAddress->setAddress($request->input('address')['address']);
                $shippingAddress->setZipCode($request->input('address')['zip_code']);
                $api->setShippingAddress($shippingAddress);

                $billingAddress = new Address();
                $billingAddress->setContactName($request->input('address')['name']);
                $billingAddress->setCity($request->input('address')['city']);
                $billingAddress->setCountry($request->input('address')['country']);
                $billingAddress->setAddress($request->input('address')['address']);
                $billingAddress->setZipCode($request->input('address')['zip_code']);

                if( $request->input('billing_address_same_as_shipping_address') == 0 ) {
                    $billingAddress->setContactName($request->input('billing_address')['name']);
                    $billingAddress->setCity($request->input('billing_address')['city']);
                    $billingAddress->setCountry($request->input('billing_address')['country']);
                    $billingAddress->setAddress($request->input('billing_address')['address']);
                    $billingAddress->setZipCode($request->input('billing_address')['zip_code']);	
                }
                $api->setBillingAddress($billingAddress);

                $basketItems = array();
                $amountTotal = 0;                
                $i = 0;
                foreach ($groupedProducts as $grouped) {                    
                    $store = $grouped['store'];
                    if (!$store->exists) {
                        $store->id = 0;
                        $store->name = theme_option('site_title');
                        $store->logo = theme_option('logo');
                    }                    
                    $storeName = $store->name;

                    foreach($grouped['products'] as $product) {
                        $BasketItem = new BasketItem();
                        $BasketItem->setId($product->id);
                        $BasketItem->setName($product->name);
                        $BasketItem->setCategory1($storeName);
                        $BasketItem->setCategory2('category 2');
                        $BasketItem->setItemType(BasketItemType::VIRTUAL);
                        $BasketItem->setPrice(number_format((float)($paymentData['products'][$i]['price_per_order']), 2, '.', ''));
                        $basketItems[$i] = $BasketItem;
                        
                        $i++;
                    }
                }

                $api->setBasketItems($basketItems);
                $api->setPrice(number_format((float)$request->input('amount'), 2, '.', ''));
                $api->setPaidPrice(number_format((float)$request->input('amount'), 2, '.', ''));
                
                # make request
                //$checkoutFormInitialize = CheckoutFormInitialize::create($api, (new IyzicoConfig)->options());
                $checkoutFormInitialize = PayWithIyzicoInitialize::create($api, (new IyzicoConfig)->options());
                
                if( $checkoutFormInitialize->getStatus() != "success" ) {
                    $data['error'] = true;
                    $data['message'] = $checkoutFormInitialize->geterrorCode() . ", message: " . $checkoutFormInitialize->geterrorMessage();
                } else {					
                    $data['checkoutUrl'] = request()->getSchemeAndHttpHost() . "/checkout/" . $checkoutToken;
                    $data['message'] = __('İnitialize Checkout successfully!');
                    //$request->session()->put('paymentcontent_msg', $checkoutFormInitialize->getCheckoutFormContent());
                    $request->session()->put('paymentcontent_msg', $checkoutFormInitialize->getPayWithIyzicoPageUrl());
                    return $data;
                }
            } catch (Exception $exception) {
                $data['error'] = true;
                $data['message'] = $exception->getMessage();
            }
        }
        
        return $data;
    }

    protected function cartGroupByStore(EloquentCollection $products): array|Collection
    {
        if (! $products->count()) {
            return $products;
        }

        $products->loadMissing([
            'variationInfo',
            'variationInfo.configurableProduct',
            'variationInfo.configurableProduct.store',
        ]);

        $groupedProducts = collect();
        foreach ($products as $product) {
            $storeId = ($product->original_product && $product->original_product->store_id) ? $product->original_product->store_id : 0;
            if (! Arr::has($groupedProducts, $storeId)) {
                $groupedProducts[$storeId] = collect([
                    'store' => $product->original_product->store,
                    'products' => collect([$product]),
                ]);
            } else {
                $groupedProducts[$storeId]['products'][] = $product;
            }
        }

        return $groupedProducts;
    }
}
