<?php
/**
 * File containing the SearchBaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Base test for search tests implementing comparision helpers
 */
abstract class SearchBaseTest extends BaseTest
{
    /**
     * Assert that query result matches the given fixture.
     *
     * @param Query $query
     * @param string $fixture
     *
     * @return void
     */
    protected function assertQueryFixture( Query $query, $fixture, $closure = null )
    {
        $repository    = $this->getRepository();
        $searchService = $repository->getSearchService();

        try
        {
            $result = $searchService->findContent( $query );
            $this->simplifySearchResult( $result );
        }
        catch ( NotImplementedException $e )
        {
            $this->markTestSkipped(
                "This feature is not supported by the current search backend: " . $e->getMessage()
            );
        }

        if ( !is_file( $fixture ) )
        {
            if ( isset( $_ENV['ez_tests_record'] ) )
            {
                file_put_contents(
                    $record = $fixture . '.recording',
                    "<?php\n\nreturn " . var_export( $result, true ) . ";\n\n"
                );
                $this->markTestIncomplete( "No fixture available. Result recorded at $record. Result: \n" . $this->printResult( $result ) );
            }
            else
            {
                $this->markTestIncomplete( "No fixture available. Set \$_ENV['ez_tests_record'] to generate it." );
            }
        }

        if ( $closure !== null )
        {
            $closure( $result );
        }

        $this->assertEquals(
            include $fixture,
            $result,
            "Search results do not match.",
            .1 // Be quite generous regarding delay -- most important for scores
        );
    }

    /**
     * Show a simplified view of the search result for manual introspection
     *
     * @param SearchResult $result
     *
     * @return string
     */
    protected function printResult( SearchResult $result )
    {
        $printed = '';
        foreach ( $result->searchHits as $hit )
        {
            $printed .= sprintf( " - %s (%s)\n", $hit->valueObject['title'], $hit->valueObject['id'] );
        }
        return $printed;
    }

    /**
     * Simplify search result
     *
     * This leads to saner comparisons of results, since we do not get the full
     * content objects every time.
     *
     * @param SearchResult $result
     *
     * @return void
     */
    protected function simplifySearchResult( SearchResult $result )
    {
        $result->time = 1;

        foreach ( $result->searchHits as $hit )
        {
            switch ( true )
            {
                case $hit->valueObject instanceof Content:
                    $hit->valueObject = array(
                        'id'    => $hit->valueObject->contentInfo->id,
                        'title' => $hit->valueObject->contentInfo->name,
                    );
                    break;

                default:
                    throw new \RuntimeException( "Unknown search result hit type: " . get_class( $hit->valueObject ) );
            }
        }
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
