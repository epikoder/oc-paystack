<?php

Route::group(['prefix' => 'ocpaystack', 'middleware' => ['web']], function () {
    Route::get('ocpaystack/webhook_url', '\Epikoder\Ocpaystack\Controllers\PaystackController@webhook_url');
});
?>