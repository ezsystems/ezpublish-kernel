<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandler/TransformationProcessorDefinitionBasedParserTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandler;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Search;

/**
 * Test case for LocationHandlerTest
 */
class TransformationProcessorDefinitionBasedParserTest extends TestCase
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
        $parser = new Search\TransformationProcessor\DefinitionBased\Parser( self::getInstallationDir() );

        $fixture = include $file . '.result';
        $this->assertEquals(
            $fixture,
            $parser->parse( str_replace( self::getInstallationDir(), '', $file ) )
        );
    }
}

