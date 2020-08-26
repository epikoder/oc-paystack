<?php

namespace Epikoder\Ocpaystack\Classes;

use ReflectionObject;

class PaystackResponse
{
    function __toString() : string
    {
        return utf8_encode($this->data->status);
    }

    function isSuccessful() : bool
    {
        return utf8_encode($this->status);
    }

    function getMessage() : string
    {
        return utf8_encode($this->data->gateway_response);
    }

    function getCode() : int
    {
        return ($this->data->status == 'success') ? 200 : 400;
    }

}
