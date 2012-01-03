<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\SearchHandler/TransformationParserTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\SearchHandler;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Search;

/**
 * Test case for LocationHandlerTest
 */
class TransformationParserTest extends TestCase
{
    public static function getTestFiles()
    {
        return array_map(
            function ( $file )
            {
                return array( realpath( $file ) );
            },
            glob( __DIR__ . '/_fixtures/transformations/*.tr' )
        );
    }

    /**
     * @dataProvider getTestFiles
     */
    public function testParse( $file )
    {
        $parser = new Search\TransformationParser();

        $fixture = include $file . '.result';
        $this->assertEquals(
            $fixture,
            $parser->parse( $file )
        );
    }
}

