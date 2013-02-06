<?php
/**
 * File containing the LegacyEngineTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;

class LegacyEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $engine;

    protected function setUp()
    {
        parent::setUp();
        $this->engine = new LegacyEngine(
            function ()
            {
            },
            $this->getMock( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\Converter\\MultipleObjectConverter' )
        );
    }

    /**
     * @param $tplName
     * @param $expected
     *
     * @covers \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine::supports
     *
     * @dataProvider supportTestProvider
     */
    public function testSupports( $tplName, $expected )
    {
        $this->assertSame( $expected, $this->engine->supports( $tplName ) );
    }

    public function supportTestProvider()
    {
        return array(
            array( 'design:foo/bar.tpl', true ),
            array( 'file:some/path.tpl', true ),
            array( 'unsupported.php', false ),
            array( 'unsupported.tpl', false ),
            array( 'design:unsupported.php', false ),
            array( 'design:foo/bar.php', false ),
            array( 'file:some/path.php', false )
        );
    }
}
