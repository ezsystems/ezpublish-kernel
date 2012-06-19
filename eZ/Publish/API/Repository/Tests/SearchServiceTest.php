<?php
/**
 * File containing the SearchServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Exceptions\NotFoundException;
use \eZ\Publish\Core\Repository\SearchService;
use \eZ\Publish\API\Repository\Values\Content\Query;
use \eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Test case for operations in the SearchService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 */
class SearchServiceTest extends BaseTest
{
    /**
     * Return search service to test
     *
     * @return SearchService
     */
    protected function getSearchService()
    {
        return new SearchService();
    }

    /**
     * Test for the getSearchService() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Repository::getSearchService()
     */
    public function testGetSearchService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\SearchService',
            $repository->getSearchService()
        );
    }

    public function getSearches()
    {
        $fixtureDir = $this->getFixtureDir();
        return array(
            array(
                new Criterion\ContentId(
                    array( 1, 4, 10 )
                ),
                $fixtureDir . 'ContentId.php',
            ),
            array(
                new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentId(
                            array( 1, 4, 10 )
                        ),
                        new Criterion\ContentId(
                            array( 4, 12 )
                        ),
                    )
                ),
                $fixtureDir . 'LogicalAnd.php',
            ),
            array(
                new Criterion\LogicalOr(
                    array(
                        new Criterion\ContentId(
                            array( 1, 4, 10 )
                        ),
                        new Criterion\ContentId(
                            array( 4, 12 )
                        ),
                    )
                ),
                $fixtureDir . 'LogicalOr.php',
            ),
            array(
                new Criterion\LogicalAnd(
                    array(
                        new Criterion\ContentId(
                            array( 1, 4, 10 )
                        ),
                        new Criterion\LogicalNot(
                            new Criterion\ContentId(
                                array( 10, 12 )
                            )
                        ),
                    )
                ),
                $fixtureDir . 'LogicalNot.php',
            ),
            array(
                new Criterion\Subtree(
                    '/1/2/69/'
                ),
                $fixtureDir . 'Subtree.php',
            ),
            array(
                new Criterion\ContentTypeId(
                    4
                ),
                $fixtureDir . 'ContentTypeId.php',
            ),
            array(
                new Criterion\ContentTypeGroupId(
                    2
                ),
                $fixtureDir . 'ContentTypeGroupId.php',
            ),
            array(
                new Criterion\DateMetadata(
                    Criterion\DateMetadata::MODIFIED,
                    Criterion\Operator::GT,
                    1311154214
                ),
                $fixtureDir . 'DateMetadataGt.php',
            ),
            array(
                new Criterion\DateMetadata(
                    Criterion\DateMetadata::MODIFIED,
                    Criterion\Operator::GTE,
                    1311154214
                ),
                $fixtureDir . 'DateMetadataGte.php',
            ),
            array(
                new Criterion\DateMetadata(
                    Criterion\DateMetadata::MODIFIED,
                    Criterion\Operator::IN,
                    array( 1311154214, 1311154215 )
                ),
                $fixtureDir . 'DateMetadataIn.php',
            ),
            array(
                new Criterion\DateMetadata(
                    Criterion\DateMetadata::MODIFIED,
                    Criterion\Operator::BETWEEN,
                    array( 1311154213, 1311154215 )
                ),
                $fixtureDir . 'DateMetadataBetween.php',
            ),
            array(
                new Criterion\DateMetadata(
                    Criterion\DateMetadata::CREATED,
                    Criterion\Operator::BETWEEN,
                    array( 1299780749, 1311154215 )
                ),
                $fixtureDir . 'DateMetadataCreated.php',
            ),
            array(
                new Criterion\LocationId(
                    array( 1, 2, 5 )
                ),
                $fixtureDir . 'LocationId.php',
            ),
            array(
                new Criterion\ParentLocationId(
                    array( 1 )
                ),
                $fixtureDir . 'ParentLocationId.php',
            ),
            array(
                new Criterion\RemoteId(
                    array( 'f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca' )
                ),
                $fixtureDir . 'RemoteId.php',
            ),
            array(
                new Criterion\LocationRemoteId(
                    array( '3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983' )
                ),
                $fixtureDir . 'LocationRemoteId.php',
            ),
            array(
                new Criterion\SectionId(
                    array( 2 )
                ),
                $fixtureDir . 'SectionId.php',
            ),
            array(
                new Criterion\Status(
                    array( Criterion\Status::STATUS_PUBLISHED )
                ),
                $fixtureDir . 'Status.php',
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider getSearches
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends testGetSearchService
     */
    public function testFindContent( $criterion, $fixture )
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        $query = new Query();
        $query->criterion = $criterion;

        try {
            $result = $searchService->findContent( $query );
        } catch ( NotImplementedException $e ) {
            $this->markTestSkipped(
                "This feature is not supported by the current search backend: " . $e->getMessage()
            );
        }

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchResult',
            $result
        );

        if ( !is_file( $fixture ) )
        {
            file_put_contents(
                $record = $fixture . '.recording',
                "<?php\n\nreturn " . var_export( $result, true ) . ";\n\n"
            );
            $this->markTestIncomplete( "No fixture available. Result recorded at $record" );
        }

        $this->assertEquals(
            include $fixture,
            $result
        );
    }

    /**
     * Get fixture directory
     *
     * @return string
     */
    protected function getFixtureDir()
    {
        return __DIR__ . '/_fixtures/' . getenv( "fixtureDir" ) . '/';
    }
}
