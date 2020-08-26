<?php

Route::group(['prefix' => 'epikoder/ocpaystack', 'middleware' => ['web']], function () {
    Route::get('callback_url', 'Epikoder\Ocpaystack\Controllers\PaystackController@callback_url')->name('ocpaystack/callback_url');
    Route::get('webhook_url', 'Epikoder\Ocpaystack\Controllers\PaystackController@webhook_url')->name('ocpaystack/webhook_url');
});
?>