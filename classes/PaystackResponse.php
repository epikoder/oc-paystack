<?php

namespace Epikoder\Ocpaystack\Classes;

use ReflectionObject;

class PaystackResponse
{
    /**
     * Response status
     * 
     * @var bool
     */
    public $status;

    /**
     * Response message
     * @var string
     */
    public $message;

    /**
     * Response data
     * @var json
     */
    public $data;

    function __construct($sourceObject)
    {
        $sourceReflection = new ReflectionObject($sourceObject);
        $destinationReflection = new ReflectionObject($this);
        $sourceProperties = $sourceReflection->getProperties();
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue($sourceObject);
            if ($destinationReflection->hasProperty($name)) {
                $propDest = $destinationReflection->getProperty($name);
                $propDest->setAccessible(true);
                $propDest->setValue($this, $value);
            } else {
                $this->$name = $value;
            }
        }
    }
    function __toString(): string
    {
        return utf8_encode($this->data->status);
    }

    function isSuccessful(): bool
    {
        return utf8_encode($this->status);
    }

    function getMessage(): string
    {
        return utf8_encode($this->data->gateway_response);
    }

    function getCode(): int
    {
        return ($this->data->status == 'success') ? 200 : 400;
    }
}
