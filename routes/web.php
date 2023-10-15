<?php

use App\Http\Middleware\VerifyCsrfToken;

Route::group(['namespace' => 'Botble\Iyzico\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::post('iyzico/payment/callback/{token}', [
        'as' => 'iyzico.payment.callback',
        'uses' => 'IyzicoController@paymentCallback',
    ])->withoutMiddleware(VerifyCsrfToken::class);
});
