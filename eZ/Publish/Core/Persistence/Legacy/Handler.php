<?php
/**
 * File containing the Legacy Storage Handler
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy;

use eZ\Publish\SPI\Persistence\Handler as HandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Search\Handler as LocationSearchHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler as TrashHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as ContentSearchHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandler;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as UrlWildcardHandler;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandler;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\MemoryCachingHandler as CachingContentTypeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler as CachingLanguageHandler;
use Exception;
use RuntimeException;

/**
 * The main handler for Legacy Storage Engine
 */
class Handler implements HandlerInterface
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    protected $contentSearchHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler
     */
    protected $locationSearchHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    protected $trashHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    protected $urlAliasHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    protected $urlWildcardHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\User\Handler
     */
    protected $userHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    public function __construct(
        DatabaseHandler $dbHandler,
        ContentHandler $contentHandler,
        ContentSearchHandler $contentSearchHandler,
        ContentTypeHandler $contentTypeHandler,
        LanguageHandler $languageHandler,
        LocationHandler $locationHandler,
        LocationSearchHandler $locationSearchHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler,
        TrashHandler $trashHandler,
        UrlAliasHandler $urlAliasHandler,
        UrlWildcardHandler $urlWildcardHandler,
        UserHandler $userHandler
    )
    {
        $this->dbHandler = $dbHandler;
        $this->contentHandler = $contentHandler;
        $this->contentSearchHandler = $contentSearchHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->languageHandler = $languageHandler;
        $this->locationHandler = $locationHandler;
        $this->locationSearchHandler = $locationSearchHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
        $this->trashHandler = $trashHandler;
        $this->urlAliasHandler = $urlAliasHandler;
        $this->urlWildcardHandler = $urlWildcardHandler;
        $this->userHandler = $userHandler;

    }

    public function contentHandler()
    {
        return $this->contentHandler;
    }

    public function searchHandler()
    {
        return $this->contentSearchHandler;
    }

    public function contentTypeHandler()
    {
        return $this->contentTypeHandler;
    }

    public function contentLanguageHandler()
    {
        return $this->languageHandler;
    }

    public function locationHandler()
    {
        return $this->locationHandler;
    }

    public function locationSearchHandler()
    {
        return $this->locationSearchHandler;
    }

    public function objectStateHandler()
    {
        return $this->objectStateHandler;
    }

    public function sectionHandler()
    {
        return $this->sectionHandler;
    }

    public function trashHandler()
    {
        return $this->trashHandler;
    }

    public function urlAliasHandler()
    {
        return $this->urlAliasHandler;
    }

    public function urlWildcardHandler()
    {
        return $this->urlWildcardHandler;
    }

    public function userHandler()
    {
        return $this->userHandler;
    }

    public function beginTransaction()
    {
        $this->dbHandler->beginTransaction();
    }

    public function commit()
    {
        try
        {
            $this->dbHandler->commit();
        }
        catch ( Exception $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }

    public function rollback()
    {
        try
        {
            $this->dbHandler->rollback();

            // Clear all caches after rollback
            if ( $this->contentTypeHandler instanceof CachingContentTypeHandler )
            {
                $this->contentTypeHandler->clearCache();
            }

            if ( $this->languageHandler instanceof CachingLanguageHandler )
            {
                $this->languageHandler->clearCache();
            }
        }
        catch ( Exception $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }
}
