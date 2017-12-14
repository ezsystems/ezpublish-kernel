<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\URL;

use eZ\Publish\API\Repository\Values\ValueObject;

class URL extends ValueObject
{
    /**
     * The unique id of the URL.
     *
     * @var int
     */
    protected $id;

    /**
     * URL itself e.g. "http://ez.no".
     *
     * @var string
     */
    protected $url;

    /**
     * Is URL valid ?
     *
     * @var bool
     */
    protected $isValid;

    /**
     * Date of last check.
     *
     * @var \DateTimeInterface
     */
    protected $lastChecked;

    /**
     * Creation date.
     *
     * @var \DateTimeInterface
     */
    protected $created;

    /**
     * Modified date.
     *
     * @var \DateTimeInterface
     */
    protected $modified;
}
