<?php

namespace Epikoder\Ocpaystack\Classes;

use Config;
use Request;
use Illuminate\Support\Facades\DB;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use OFFLINE\Mall\Classes\Payments\PaymentResult;
use RainLab\User\Facades\Auth;
use Yabacon\Paystack as PaystackAPI;

use function Opis\Closure\serialize;

class Paystack extends \OFFLINE\Mall\Classes\Payments\PaymentProvider
{
    /**
     * The order that is being paid.
     *
     * @var \OFFLINE\Mall\Models\Order
     */
    public $order;

    /**
     * Paystack data configuration
     * @var array
     */
    public $paystack;

    /**
     * Return the display name of your payment provider.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Paystack';
    }

    /**
     * Return a unique identifier for this payment provider.
     *
     * @return string
     */
    public function identifier(): string
    {
        return 'paystack';
    }

    /**
     * Validate the given input data for this payment.
     *
     * @return bool
     * @throws \October\Rain\Exception\ValidationException
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * Return any custom backend settings fields.
     * 
     * These fields will be rendered in the backend
     * settings page of your provider. 
     *
     * @return array
     */
    public function settings(): array
    {
        return [
            'secret_key'     => [
                'label'   => 'Secret Key',
                'comment' => 'Secret key from paystack dashboard',
                'span'    => 'left',
                'type'    => 'text',
            ],
            'currency' => [
                'label'   => 'Currency Code',
                'comment' => 'used if defined',
                'span'    => 'left',
                'type'    => 'text',
            ],
            'unit' => [
                'label'   => 'Unit',
                'comment' => 'Currency unit use 100 for NGN ,e.g 100 kobo = 1 NGN',
                'span'    => 'left',
                'type'    => 'text',
                'value' => '100'
            ],
            'rate' => [
                'label'   => 'Rate',
                'comment' => 'Rate of to default Store Currency e.g NGN455.11 = 1EURO',
                'span'    => 'right',
                'type'    => 'text',
            ],
        ];
    }

    /**
     * Setting keys returned from this method are stored encrypted.
     *
     * Use this to store API tokens and other secret data
     * that is needed for this PaymentProvider to work.
     *
     * @return array
     */
    public function encryptedSettings(): array
    {
        return ['secret_key', 'currency', 'unit', 'rate'];
    }

    /**
     * Process the payment.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function process(PaymentResult $result): PaymentResult
    {
        $gateway = $this->init();
        try {
            $tranx = $gateway->transaction->initialize([
                'amount'    => $this->paystack['amount'],
                'currency'  => $this->paystack['currency'],
                'email' => $this->paystack['email'],
                'callback_url' => $this->returnUrl()
            ]);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            print_r($e->getResponseObject());
            die($e->getMessage());
        }

        return $result->redirect($tranx->data->authorization_url);
    }

    /**
     * Complete payment from callback_url
     */
    public function complete(PaymentResult $result): PaymentResult
    {
        $reference = Request::input('reference');
        if (!$reference) {
            return $result->fail([
                'msg'   => 'Missing payment data'
            ], null);
        }

        $gateway = $this->create();
        try {
            $tranx = $gateway->transaction->verify([
                'reference' => $reference
            ]);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            print_r($e->getResponseObject());
            die($e->getMessage());
        }
        
        $this->setOrder($result->order);
        $tranx = new PaystackResponse($tranx);
        if (get_class($tranx) != 'Epikoder\Ocpaystack\Classes\PaystackResponse') {
            throw new \UnexpectedValueException(sprintf('the class type must be an instance of \Epikoder\Ocpaystack\Classes\PaystackResponse, "%s" given', \get_class($tranx)));
        }
        if ($tranx->data->status != 'success' || !$tranx->isSuccessful()) {
            return $result->fail((array) $tranx->data->authorization, $tranx);
        }

        return $result->success((array) $tranx->data->authorization, $tranx);
    }

    /**
     * Initialize paystack and set configuration 
     * defined in backend if available
     */
    public function init(): \Yabacon\Paystack
    {
        $currency = decrypt(PaymentGatewaySettings::get('currency'));
        $rate = decrypt(PaymentGatewaySettings::get('rate'));
        $unit = decrypt(PaymentGatewaySettings::get('unit'));
        if ($currency != null && strlen($currency) == 3) {
            $this->paystack['currency'] = $currency;
            $this->paystack['amount'] = number_format($unit * $rate * $this->order->total_in_currency, 2, '.', '');
        } else {
            $this->paystack['currency'] = $this->order->currency['code'];
            $this->paystack['amount'] = $this->order->total_in_currency;
        }
        $user = Auth::user();
        $this->paystack['email'] = $user->email;
        if (!$user) {
            die('You are not logged in');
        }
        return $this->create();
    }

    /**
     * Create the gateway instance
     */
    public function create()
    {
        return new PaystackAPI(decrypt(PaymentGatewaySettings::get('secret_key')));
    }
}
