<?php
/**
 * File containing the DoctrineDatabase Content search Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway;

use eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as SPITypeHandler;

/**
 * WordIndexer gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * SPI Content Type Handler.
     *
     * Need this for being able to pick fields that are searchable.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $typeHandler;

    /**
     * Construct from handler handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $typeHandler
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        SPITypeHandler $typeHandler
    ) {
        $this->dbHandler = $dbHandler;
        $this->typeHandler = $typeHandler;
    }

    /**
     * Add a version of a Content to index.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function index(Content $content)
    {
        throw new \RuntimeException('Indexing are not supported by legacy search engine, see eZSearchEngine::addObject');
    }

    /**
     * Remove whole content or a specific version from index.
     *
     * @param mixed $contentId
     * @param mixed|null $versionId
     */
    public function remove($contentId, $versionId = null)
    {
        throw new \RuntimeException('Indexing removal not supported by legacy search engine, see eZSearchEngine::removeObject');
    }
}
