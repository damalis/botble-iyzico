<?php

namespace Botble\Iyzico\Services\Gateways;

use Botble\Iyzico\Services\Abstracts\IyzicoPaymentAbstract;
use Exception;
use Illuminate\Http\Request;

class IyzicoPaymentService extends IyzicoPaymentAbstract
{
    /**
     * Make a payment
     *
     * @param Request $request
     *
     * @return mixed
     * @throws Exception
     */
    public function makePayment(Request $request)
    {
    }

    /**
     * Use this function to perform more logic after user has made a payment
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function afterMakePayment(Request $request)
    {
    }
}
