<?php
/**
 * File containing the PersistenceCachePurger class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Cache;

use Tedivm\StashBundle\Service\CacheService;

class PersistenceCachePurger
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * Avoid clearing sub elements if all cache is already cleared, avoids redundant calls to Stash.
     *
     * @var bool
     */
    protected $allCleared = false;

    /**
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     */
    public function __construct( CacheService $cache )
    {
        $this->cache = $cache;
    }

    /**
     * @return void
     */
    public function all()
    {
        $this->cache->clear();
        $this->allCleared = true;
    }

    /**
     * @param int|null $id Purges all content cache if null
     * @return void
     */
    public function content( $id )
    {
        if ( $this->allCleared === true )
            return;

        if ( $id === null )
        {
            $this->cache->clear( 'content' );
        }
        else
        {
            $this->cache->clear( 'content', $id );
            $this->cache->clear( 'content', 'info', $id );
        }

        // clear content related cache as well
        $this->cache->clear( 'urlAlias' );
        $this->cache->clear( 'location' );
    }

    /**
     * @param int|null $id Purges all contentType cache if null
     * @return void
     */
    public function contentType( $id )
    {
        if ( $this->allCleared === true )
            return;

        if ( $id === null )
        {
            $this->cache->clear( 'contentType' );
        }
        else
        {
            $this->cache->clear( 'contentType', $id );
        }
    }

    /**
     * @param int|null $id Purges all contentTypeGroup cache if null
     * @return void
     */
    public function contentTypeGroup( $id )
    {
        if ( $this->allCleared === true )
            return;

        if ( $id === null )
        {
            $this->cache->clear( 'contentTypeGroup' );
        }
        else
        {
            $this->cache->clear( 'contentTypeGroup', $id );
        }

        // clear content type in case of changes as it contains the relation to groups
        $this->cache->clear( 'contentType' );
    }

    /**
     * @param int|null $id Purges all section cache if null
     * @return void
     */
    public function section( $id )
    {
        if ( $this->allCleared === true )
            return;

        if ( $id === null )
        {
            $this->cache->clear( 'section' );
            return;
        }

        $this->cache->clear( 'section', $id );
    }


    /**
     * @param array $ids
     * @return void
     */
    public function languages( array $ids )
    {
        if ( $this->allCleared === true )
            return;

        foreach ( $ids as $id )
            $this->cache->clear( 'language', $id );
    }

    /**
     * @return void
     */
    public function user()
    {
        if ( $this->allCleared === true )
            return;

        $this->cache->clear( 'user' );
    }
}
