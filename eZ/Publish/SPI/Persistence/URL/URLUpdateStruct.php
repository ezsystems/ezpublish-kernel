<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\URL;

use eZ\Publish\SPI\Persistence\ValueObject;

class URLUpdateStruct extends ValueObject
{
    /**
     * URL address.
     *
     * @var string
     */
    public $url;

    /**
     * Is URL valid?
     *
     * @var bool
     */
    public $isValid;

    /**
     * Date of last check (timestamp).
     *
     * @var int
     */
    public $lastChecked;

    /**
     * Modified date (timestamp).
     *
     * @var int
     */
    public $modified;
}
