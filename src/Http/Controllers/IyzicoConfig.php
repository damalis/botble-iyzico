<?php

namespace Botble\Iyzico\Http\Controllers;

use Iyzipay\Options;

class IyzicoConfig
{

    public function options()
    {
        $options = new Options();
        $public = get_payment_setting('public', IYZICO_PAYMENT_METHOD_NAME);
        $secret = get_payment_setting('secret', IYZICO_PAYMENT_METHOD_NAME);
        $options->setApiKey($public);
        $options->setSecretKey($secret);
        
        $baseUrl = $this->environment();
        $options->setBaseUrl($baseUrl);
        
        return $options;
    }

    /**
     * Setting up and Returns İyzico SDK environment with İyzico Access credentials.
     * For demo purpose, we are using SandboxEnvironment. In production this will be
     * ProductionEnvironment.
     */
	public function environment()
    { 
        $iyzicoMode = setting('payment_iyzico_mode');

        if ($iyzicoMode) {			
            return "https://sandbox-api.iyzipay.com";
        }

        return "https://api.merchant.iyzico.com";
    }
}