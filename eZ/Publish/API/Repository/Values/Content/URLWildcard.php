<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\URLWildcard class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a url alias in the repository.
 *
 * @property-read mixed $id A unique identifier for the alias
 * @property-read string $sourceUrl The source url with wildcards
 * @property-read string $destinationUrl The destination URL with placeholders
 * @property-read bool $forward indicates if the url is redirected or not
 */
class URLWildcard extends ValueObject
{
    /**
     * The unique id.
     *
     * @var mixed
     */
    protected $id;

    /**
     * The source url including "*".
     *
     * @var string
     */
    protected $sourceUrl;

    /**
     * The destination url containing placeholders e.g. /destination/{1}.
     *
     * @var string
     */
    protected $destinationUrl;

    /**
     * Indicates if the url is redirected or not.
     *
     * @var bool
     */
    protected $forward;
}
