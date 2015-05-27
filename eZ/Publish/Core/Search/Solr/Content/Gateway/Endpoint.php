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
     * Holds scheme, 'http' or 'https'
     *
     * @var string
     */
    protected $scheme;

    /**
     * Holds basic HTTP authentication username
     *
     * @var string
     */
    protected $user;

    /**
     * Holds basic HTTP authentication password
     *
     * @var string
     */
    protected $pass;

    /**
     * Holds hostname
     *
     * @var string
     */
    protected $host;

    /**
     * Holds port number
     *
     * @var int
     */
    protected $port;

    /**
     * Holds path
     *
     * @var string
     */
    protected $path;

    /**
     * Holds core name
     *
     * @var string
     */
    protected $core;

    /**
     * Returns Endpoint's identifier, to be used for targeting specific logical indexes
     *
     * @return string
     */
    public function getIdentifier()
    {
        return "{$this->host}:{$this->port}{$this->path}/{$this->core}";
    }

    /**
     * Returns full HTTP URL of the Endpoint
     *
     * @return string
     */
    public function getURL()
    {
        $authorization = ( !empty( $this->username ) ? "{$this->user}:{$this->pass}" : "" );

        return "{$this->scheme}://{$authorization}" . $this->getIdentifier();
    }
}
