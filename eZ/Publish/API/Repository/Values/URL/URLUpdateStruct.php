<?php


namespace eZ\Publish\API\Repository\Values\URL;

use eZ\Publish\API\Repository\Values\ValueObject;

class URLUpdateStruct extends ValueObject
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var bool
     */
    public $isValid;

    /**
     * @var \DateTimeInterface
     */
    public $lastChecked;
}
