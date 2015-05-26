<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

use eZ\Publish\SPI\Persistence\ValueObject;
use eZ\Publish\SPI\Search\FieldType;

/**
 * @property-read string $scheme
 * @property-read string $user
 * @property-read string $pass
 * @property-read string $host
 * @property-read int $port
 * @property-read string $path
 * @property-read string $core
 */
class Endpoint extends ValueObject
{
    /**
     *
     *
     * @var string
     */
    protected $scheme;

    /**
     *
     *
     * @var string
     */
    protected $user;

    /**
     *
     *
     * @var string
     */
    protected $pass;

    /**
     *
     *
     * @var string
     */
    protected $host;

    /**
     *
     *
     * @var int
     */
    protected $port;

    /**
     *
     *
     * @var string
     */
    protected $path;

    /**
     *
     *
     * @var string
     */
    protected $core;

    /**
     *
     *
     * @return string
     */
    public function getIdentifier()
    {
        return "{$this->host}:{$this->port}/{$this->path}/{$this->core}";
    }

    /**
     *
     *
     * @return string
     */
    public function getURL()
    {
        $authorization = ( !empty( $this->username ) ? "{$this->user}:{$this->pass}" : "" );

        return "{$this->scheme}://{$authorization}" . $this->getIdentifier();
    }
}
