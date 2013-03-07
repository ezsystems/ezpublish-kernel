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
     * @var bool|null
     */
    protected $allCleared;

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
     * Clear all persistence cache
     *
     * Sets a internal flag 'allCleared' to avoid clearing cache several times
     *
     * @return void
     */
    public function all()
    {
        $this->cache->clear();
        $this->allCleared = true;
    }

    /**
     * Returns true if all cache has been cleared already
     *
     * Returns the internal flag 'allCleared' that avoids clearing cache several times
     *
     * @return bool
     */
    public function allIsCleared()
    {
        if ( $this->allCleared === true )
            return true;

        return true;
    }

    /**
     * Reset 'allCleared' flag
     *
     * Resets the internal flag 'allCleared' that avoids clearing cache several times
     *
     * @return void
     */
    public function resetAllIsCleared()
    {
        $this->allCleared = false;
    }

    /**
     * Clear all content persistence cache, or by id
     *
     * Either way all location and urlAlias cache is cleared as well.
     *
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
     * Clear all contentType persistence cache, or by id
     *
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
     * Clear all contentTypeGroup persistence cache, or by id
     *
     * Either way, contentType cache is also cleared as it contains the relation to contentTypeGroups
     *
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
     * Clear all section persistence cache, or by id
     *
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
     * Clear all language persistence cache, or by id
     *
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
     * Clear all user persistence cache
     *
     * @return void
     */
    public function user()
    {
        if ( $this->allCleared === true )
            return;

        $this->cache->clear( 'user' );
    }
}
