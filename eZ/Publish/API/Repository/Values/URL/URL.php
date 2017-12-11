<?php

namespace eZ\Publish\API\Repository\Values\URL;

use eZ\Publish\API\Repository\Values\ValueObject;

class URL extends ValueObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $originalUrlMd5;

    /**
     * @var bool
     */
    protected $isValid;

    /**
     * @var \DateTimeInterface
     */
    protected $lastChecked;

    /**
     * @var \DateTimeInterface
     */
    protected $created;

    /**
     * @var \DateTimeInterface
     */
    protected $modified;
}
