<?php

namespace Epikoder\Ocpaystack\Controllers;

use Cms\Classes\Controller;
use Epikoder\Ocpaystack\Classes\Paystack;
use Epikoder\Ocpaystack\Classes\PaystackResponse;
use October\Rain\Auth\Models\User as ModelsUser;
use OFFLINE\Mall\Classes\Payments\PaymentResult;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\User;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Response;

class PaystackController extends Controller
{
    /**
     * Redirect to paystack
     * 
     *
     */
    function callback_url()
    {
        $reference = isset($_GET['reference']) ? trim($_GET['reference']) : '';
        if (!$reference) {
            die('No reference supplied');
        }

        $paystack = new Paystack;
        $gateway = $paystack->create();

        try {
            $tranx = $gateway->transaction->verify([
                'reference' => $reference
            ]);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            print_r($e->getResponseObject());
            die($e->getMessage());
        }


        $tranx = $this->objectToObject($tranx, '\Epikoder\Ocpaystack\Classes\PaystackResponse');
        //dd($tranx->isSuccessful());

        //dd(null !== $tranx && !\is_string($tranx) && !is_numeric($tranx) && !\is_callable([$tranx, '__toString']));
        //$res = new Response($tranx);
        //dd($res);
        $order = Order::find($tranx->data->metadata->id);
        $result = new PaymentResult($paystack, $order);
        if ($tranx->status == true && $tranx->data->status == 'success')
        {
            return $paystack->complete($result, $tranx);
        }
        return $paystack->failed($result, $tranx);
    }

    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    function cast($destination, $sourceObject)
    {
        if (is_string($destination)) {
            $destination = new $destination();
        }
        $sourceReflection = new ReflectionObject($sourceObject);
        $destinationReflection = new ReflectionObject($destination);
        $sourceProperties = $sourceReflection->getProperties();
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue($sourceObject);
            if ($destinationReflection->hasProperty($name)) {
                $propDest = $destinationReflection->getProperty($name);
                $propDest->setAccessible(true);
                $propDest->setValue($destination, $value);
            } else {
                $destination->$name = $value;
            }
        }
        return $destination;
    }

    function objectToObject($instance, $className) {
    return unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(strstr(serialize($instance), '"'), ':')
    ));
}
}
