<?php
/**
 * File containing the TypeTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\BlockViewProvider\Matcher;

use eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher\Type as BlockTypeMatcher;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\BlockViewProvider\Configured\Matcher
     */
    private $matcher;

    protected function setUp()
    {
        parent::setUp();
        $this->matcher = new BlockTypeMatcher;
    }

    /**
     * @dataProvider matchBlockProvider
     *
     * @param $matchingConfig
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param $expectedResult
     */
    public function testMatchBlock( $matchingConfig, Block $block, $expectedResult )
    {
        $this->matcher->setMatchingConfig( $matchingConfig );
        $this->assertSame( $expectedResult, $this->matcher->matchBlock( $block ) );
    }

    public function matchBlockProvider()
    {
        $data = array();

        $data[] = array(
            'foo',
            $this->generateBlockForType( 'foo' ),
            true
        );

        $data[] = array(
            'foo',
            $this->generateBlockForType( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateBlockForType( 'bar' ),
            false
        );

        $data[] = array(
            array( 'foo', 'baz' ),
            $this->generateBlockForType( 'baz' ),
            true
        );

        return $data;
    }

    /**
     * @param $type
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    private function generateBlockForType( $type )
    {
        return new Block(
            array( 'type' => $type )
        );
    }
}
