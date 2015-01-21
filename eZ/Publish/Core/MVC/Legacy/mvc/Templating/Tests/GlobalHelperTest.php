<?php
/**
 * File containing the GlobalHelperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests;

use eZ\Publish\Core\MVC\Legacy\Templating\GlobalHelper;
use eZ\Publish\Core\MVC\Symfony\Templating\Tests\GlobalHelperTest as BaseGlobalHelperTest;

class GlobalHelperTest extends BaseGlobalHelperTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $legacyHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->legacyHelper = $this->getMock(
            'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\LegacyHelper',
            array(),
            array(
                function ()
                {
                }
            )
        );
        // Force to use Legacy GlobalHelper
        $this->helper = new GlobalHelper( $this->configResolver, $this->locationService, $this->router, $this->translationHelper );
        $this->helper->setLegacyHelper( $this->legacyHelper );
    }

    public function testGetLegacy()
    {
        $this->assertSame( $this->legacyHelper, $this->helper->getLegacy() );
    }
}
