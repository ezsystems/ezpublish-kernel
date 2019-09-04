<?php

/**
 * File contains: eZ\Publish\Core\Search\Legacy\Tests\Content\WordIndexer\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Tests\Content\WordIndexer\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Handler as ContentHandler;
use eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Repository\SearchIndex;
use eZ\Publish\Core\Search\Legacy\Tests\Content\AbstractTestCase;

/**
 * Test case for eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends AbstractTestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    private $contentHandler;

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected function getContentHandler()
    {
        $dbHandler = $this->getDatabaseHandler();
        if (!isset($this->contentHandler)) {
            $this->contentHandler = new ContentHandler(
                new ContentGateway(
                    $dbHandler,
                    $this->getDatabaseConnection(),
                    new ContentGateway\QueryBuilder($dbHandler),
                    $this->getLanguageHandler(),
                    $this->getLanguageMaskGenerator()
                )
            );
        }

        return $this->contentHandler;
    }

    /**
     * @return \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Repository\SearchIndex
     */
    protected function getSearchIndex()
    {
        return new SearchIndex($this->getDatabaseHandler());
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @return \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway\DoctrineDatabase
     */
    protected function getDatabaseGateway()
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase(
                $this->getDatabaseHandler(),
                $this->getContentTypeHandler(),
                $this->getDefinitionBasedTransformationProcessor(),
                $this->getSearchIndex(),
                [] // Means the default config should be used
            );
        }

        return $this->databaseGateway;
    }

    /**
     * @covers \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway\DoctrineDatabase::__construct
     */
    public function testCtor()
    {
        $handler = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handler,
            'dbHandler',
            $gateway
        );
    }

    /**
     * Test indexing of Turkish characters.
     * The fixture must contain the words "eei eeİ eeı eeI".
     *
     * @see https://jira.ez.no/browse/EZP-30481
     * @covers \eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Gateway\DoctrineDatabase::buildWordIDArray
     */
    public function testBuildWordIDArrayWithTurkishChars()
    {
        $gateway = $this->getDatabaseGateway();

        $content = $this->getContentHandler()->load(100);
        $fullTextMapper = $this->getFullTextMapper($this->getContentTypeHandler());
        $fullTextData = $fullTextMapper->mapContent($content);

        $gateway->index($fullTextData);

        // Assert words where saved in the index
        self::assertQueryResult(
            [
                [
                    'word' => 'eei',
                    'object_count' => '1',
                ],
                [
                    'word' => 'eeI',
                    'object_count' => '1',
                ],
            ],
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('word', 'object_count')
                ->from('ezsearch_word')
                ->where('word in (\'eei\', \'eeI\')')
        );

        // Assert the object-word-link entries where created - this does not happen correctly pre-EZP-30481
        self::assertQueryResult(
            [
                [
                    'word' => 'eei',
                    'count' => '1',
                ],
                [
                    'word' => 'eeI',
                    'count' => '3',
                ],
            ],
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('ezsearch_word.word', 'COUNT(ezsearch_word.id) as count')
                ->from('ezsearch_word, ezsearch_object_word_link')
                ->where('ezsearch_word.id = ezsearch_object_word_link.word_id AND ezsearch_word.word in (\'eei\', \'eeI\') GROUP BY ezsearch_word.id')
        );
    }
}
