<?php
/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $pageService;

    protected function setUp()
    {
        parent::setUp();
        $this->pageService = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\PageService' );
    }

    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Page::init
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $zone1 = new Zone( $this->pageService, array( 'id' => 'foo' ) );
        $zone2 = new Zone( $this->pageService, array( 'id' => 'bar' ) );
        $zone3 = new Zone( $this->pageService, array( 'id' => 'baz' ) );
        $properties = array(
            'layout'    => 'my_layout',
            'zones'     => array( $zone1, $zone2, $zone3 )
        );
        $page = new Page( $this->pageService, $properties );
        $this->assertEquals(
            $properties + array(
                'zonesById'     => array(
                    'foo'   => $zone1,
                    'bar'   => $zone2,
                    'baz'   => $zone3
                ),
                'attributes'    => array()
            ),
            $page->getState()
        );
    }
}
