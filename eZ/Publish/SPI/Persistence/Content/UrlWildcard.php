<?php

/**
 * File containing the UrlWildcard class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * UrlWildCard models one url alias path with wild cards.
 */
class UrlWildcard extends ValueObject
{
    /**
     * The unique id.
     *
     * @var mixed
     */
    public $id;

    /**
     * The source url including "*".
     *
     * @var string
     */
    public $sourceUrl;

    /**
     * The destination url containing placeholders e.g. /destination/{1}.
     *
     * @var string
     */
    public $destinationUrl;

    /**
     * Indicates if the url is redirected or not.
     *
     * @var bool
     */
    public $forward;
}
