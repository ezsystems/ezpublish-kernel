<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\SetupFactory;

use eZ\Publish\Core\Persistence\Solr,
    eZ\Publish\Core\FieldType;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class LegacySolr extends Legacy
{
    protected static $indexed = false;

    /**
     * Returns a configured repository for testing.
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository( $initializeFromScratch = true )
    {
        $repository = parent::getRepository( $initializeFromScratch );

        // @HACK: This is a hack to inject a different search handler -- is
        // there a well supported way to do this? I don't think so.
        $persistenceProperty = new \ReflectionProperty( $repository, 'persistenceHandler' );
        $persistenceProperty->setAccessible( true );
        $persistenceHandler = $persistenceProperty->getValue( $repository );

        $searchProperty = new \ReflectionProperty( $persistenceHandler, 'searchHandler' );
        $searchProperty->setAccessible( true );
        $searchProperty->setValue(
            $persistenceHandler,
            $this->getSearchHandler( $persistenceHandler )
        );

        return $repository;
    }

    protected function getSearchHandler( $persistenceHandler )
    {
        $nameGenerator = new Solr\Content\Search\FieldNameGenerator();
        $fieldRegistry = new Solr\Content\Search\FieldRegistry( array(
            'ezstring'              => new FieldType\TextLine\SearchField(),
            'ezprice'               => new FieldType\Price\SearchField(),
            // @TODO: These two need proper custom search field definitions
            'eztext'                => new FieldType\TextLine\SearchField(),
            'ezxmltext'             => new FieldType\TextLine\SearchField(),
            // @TODO: Define proper types for these:
            'ezuser'                => new FieldType\Unindexed(),
            'ezimage'               => new FieldType\Unindexed(),
            'ezboolean'             => new FieldType\Unindexed(),
            'ezkeyword'             => new FieldType\Unindexed(),
            'ezdatetime'            => new FieldType\Unindexed(),
            'ezinisetting'          => new FieldType\Unindexed(),
            'ezpackage'             => new FieldType\Unindexed(),
            'ezurl'                 => new FieldType\Unindexed(),
            'ezobjectrelation'      => new FieldType\Unindexed(),
            'ezmultioption'         => new FieldType\Unindexed(),
            'ezauthor'              => new FieldType\Unindexed(),
            'ezsrrating'            => new FieldType\Unindexed(),
            'ezselection'           => new FieldType\Unindexed(),
            'ezsubtreesubscription' => new FieldType\Unindexed(),
            'ezobjectrelationlist'  => new FieldType\Unindexed(),
            'ezemail'               => new FieldType\Unindexed(),
            'ezoption'              => new FieldType\Unindexed(),
            'ezgmaplocation'        => new FieldType\Unindexed(),
            'ezbinaryfile'          => new FieldType\Unindexed(),
            'ezmedia'               => new FieldType\Unindexed(),
            'ezpage'                => new FieldType\Unindexed(),
            'ezcomcomments'         => new FieldType\Unindexed(),
        ) );

        $searchHandler = new Solr\Content\Search\Handler(
            new Solr\Content\Search\Gateway\Native(
                new Solr\Content\Search\Gateway\HttpClient\Stream( getenv( "solrServer" ) ),
                new Solr\Content\Search\CriterionVisitor\Aggregate( array(
                    new Solr\Content\Search\CriterionVisitor\ContentIdIn(),
                    new Solr\Content\Search\CriterionVisitor\LogicalAnd(),
                    new Solr\Content\Search\CriterionVisitor\LogicalOr(),
                    new Solr\Content\Search\CriterionVisitor\LogicalNot(),
                    new Solr\Content\Search\CriterionVisitor\SubtreeIn(),
                    new Solr\Content\Search\CriterionVisitor\ContentTypeIdIn(),
                    new Solr\Content\Search\CriterionVisitor\ContentTypeGroupIdIn(),
                    new Solr\Content\Search\CriterionVisitor\LocationIdIn(),
                    new Solr\Content\Search\CriterionVisitor\ParentLocationIdIn(),
                    new Solr\Content\Search\CriterionVisitor\SectionIn(),
                    new Solr\Content\Search\CriterionVisitor\RemoteIdIn(),
                    new Solr\Content\Search\CriterionVisitor\LanguageCodeIn(),
                    new Solr\Content\Search\CriterionVisitor\ObjectStateIdIn(),
                    new Solr\Content\Search\CriterionVisitor\LocationRemoteIdIn(),
                    new Solr\Content\Search\CriterionVisitor\DateMetadata\ModifiedIn(),
                    new Solr\Content\Search\CriterionVisitor\DateMetadata\PublishedIn(),
                    new Solr\Content\Search\CriterionVisitor\DateMetadata\ModifiedBetween(),
                    new Solr\Content\Search\CriterionVisitor\DateMetadata\PublishedBetween(),
                    new Solr\Content\Search\CriterionVisitor\StatusIn(),
                    new Solr\Content\Search\CriterionVisitor\FullText(),
                    new Solr\Content\Search\CriterionVisitor\Field\FieldIn(
                        $fieldRegistry,
                        $persistenceHandler->contentTypeHandler(),
                        $nameGenerator
                    ),
                    new Solr\Content\Search\CriterionVisitor\Field\FieldRange(
                        $fieldRegistry,
                        $persistenceHandler->contentTypeHandler(),
                        $nameGenerator
                    ),
                ) ),
                new Solr\Content\Search\SortClauseVisitor\Aggregate( array(
                    new Solr\Content\Search\SortClauseVisitor\ContentId(),
                    new Solr\Content\Search\SortClauseVisitor\LocationPathString(),
                    new Solr\Content\Search\SortClauseVisitor\LocationDepth(),
                ) ),
                new Solr\Content\Search\FacetBuilderVisitor\Aggregate( array(
                    new Solr\Content\Search\FacetBuilderVisitor\ContentType(),
                    new Solr\Content\Search\FacetBuilderVisitor\Section(),
                    new Solr\Content\Search\FacetBuilderVisitor\User(),
                ) ),
                new Solr\Content\Search\FieldValueMapper\Aggregate( array(
                    new Solr\Content\Search\FieldValueMapper\IdentifierMapper(),
                    new Solr\Content\Search\FieldValueMapper\StringMapper(),
                    new Solr\Content\Search\FieldValueMapper\IntegerMapper(),
                    new Solr\Content\Search\FieldValueMapper\DateMapper(),
                    new Solr\Content\Search\FieldValueMapper\PriceMapper(),
                ) ),
                $persistenceHandler->contentHandler(),
                $nameGenerator
            ),
            $fieldRegistry,
            $persistenceHandler->contentTypeHandler(),
            $persistenceHandler->objectStateHandler()
        );

        $this->indexAll( $persistenceHandler, $searchHandler );

        return $searchHandler;
    }

    protected function indexAll( $persistenceHandler, $searchHandler )
    {
        if ( self::$indexed )
        {
            return;
        }

        // @TODO: Is there a nicer way to get access to all content objects? We
        // require this to run a full index here.
        $dbHandlerProperty = new \ReflectionProperty( $persistenceHandler, 'dbHandler' );
        $dbHandlerProperty->setAccessible( true );
        $db = $dbHandlerProperty->getValue( $persistenceHandler );

        $query = $db->createSelectQuery()
            ->select( 'id', 'current_version' )
            ->from( 'ezcontentobject' );

        $stmt = $query->prepare();
        $stmt->execute();

        $searchHandler->purgeIndex();
        while ( $row = $stmt->fetch( \PDO::FETCH_ASSOC ) )
        {
            $searchHandler->indexContent(
                $persistenceHandler->contentHandler()->load( $row['id'], $row['current_version'] )
            );
        }

        self::$indexed = true;
    }
}
