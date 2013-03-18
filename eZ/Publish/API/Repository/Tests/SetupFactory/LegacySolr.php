<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\SetupFactory;

use eZ\Publish\Core\Persistence\Solr;
use eZ\Publish\Core\Persistence\Solr\Content\Search;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FacetBuilderVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;
use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\Core\FieldType;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class LegacySolr extends Legacy
{
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
            $searchHandler = $this->getSearchHandler( $persistenceHandler )
        );

        if ( $initializeFromScratch )
        {
            $this->indexAll( $persistenceHandler, $searchHandler );
        }

        return $repository;
    }

    protected function getSearchHandler( $persistenceHandler )
    {
        $nameGenerator = new FieldNameGenerator();
        $fieldRegistry = new FieldRegistry(
            array(
                'ezstring'              => new FieldType\TextLine\SearchField(),
                'ezprice'               => new FieldType\Price\SearchField(),
                // @todo: These two need proper custom search field definitions
                'eztext'                => new FieldType\TextLine\SearchField(),
                'ezxmltext'             => new FieldType\TextLine\SearchField(),
                // @todo: Define proper types for these:
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
            )
        );

        return new Search\Handler(
            new Search\Gateway\Native(
                new Search\Gateway\HttpClient\Stream( getenv( "solrServer" ) ),
                new CriterionVisitor\Aggregate(
                    array(
                        new CriterionVisitor\ContentIdIn(),
                        new CriterionVisitor\LogicalAnd(),
                        new CriterionVisitor\LogicalOr(),
                        new CriterionVisitor\LogicalNot(),
                        new CriterionVisitor\SubtreeIn(),
                        new CriterionVisitor\ContentTypeIdIn(),
                        new CriterionVisitor\ContentTypeGroupIdIn(),
                        new CriterionVisitor\LocationIdIn(),
                        new CriterionVisitor\ParentLocationIdIn(),
                        new CriterionVisitor\SectionIn(),
                        new CriterionVisitor\RemoteIdIn(),
                        new CriterionVisitor\LanguageCodeIn(),
                        new CriterionVisitor\ObjectStateIdIn(),
                        new CriterionVisitor\LocationRemoteIdIn(),
                        new CriterionVisitor\DateMetadata\ModifiedIn(),
                        new CriterionVisitor\DateMetadata\PublishedIn(),
                        new CriterionVisitor\DateMetadata\ModifiedBetween(),
                        new CriterionVisitor\DateMetadata\PublishedBetween(),
                        new CriterionVisitor\StatusIn(),
                        new CriterionVisitor\FullText(),
                        new CriterionVisitor\Field\FieldIn(
                            $fieldRegistry,
                            $persistenceHandler->contentTypeHandler(),
                            $nameGenerator
                        ),
                        new CriterionVisitor\Field\FieldRange(
                            $fieldRegistry,
                            $persistenceHandler->contentTypeHandler(),
                            $nameGenerator
                        ),
                    )
                ),
                new SortClauseVisitor\Aggregate(
                    array(
                        new SortClauseVisitor\ContentId(),
                        new SortClauseVisitor\LocationPathString(),
                        new SortClauseVisitor\LocationDepth(),
                        new SortClauseVisitor\LocationPriority(),
                    )
                ),
                new FacetBuilderVisitor\Aggregate(
                    array(
                        new FacetBuilderVisitor\ContentType(),
                        new FacetBuilderVisitor\Section(),
                        new FacetBuilderVisitor\User(),
                    )
                ),
                new FieldValueMapper\Aggregate(
                    array(
                        new FieldValueMapper\IdentifierMapper(),
                        new FieldValueMapper\MultipleIdentifierMapper(),
                        new FieldValueMapper\StringMapper(),
                        new FieldValueMapper\IntegerMapper(),
                        new FieldValueMapper\DateMapper(),
                        new FieldValueMapper\PriceMapper(),
                    )
                ),
                $persistenceHandler->contentHandler(),
                $nameGenerator
            ),
            $fieldRegistry,
            $persistenceHandler->locationHandler(),
            $persistenceHandler->contentTypeHandler(),
            $persistenceHandler->objectStateHandler()
        );
    }

    protected function indexAll( $persistenceHandler, $searchHandler )
    {
        // @todo: Is there a nicer way to get access to all content objects? We
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
    }
}
