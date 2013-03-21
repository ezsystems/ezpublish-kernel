<?php
/**
 * File containing the ParameterProviderTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\Page;

use eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider;

class ParameterProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\FieldType\Page\ParameterProvider::getViewParameters
     */
    public function testGetViewParameters()
    {
        $pageService = $this->getMock( 'eZ\\Publish\\Core\\FieldType\\Page\\PageService' );
        $parameterProvider = new ParameterProvider( $pageService );
        $this->assertSame( array( 'pageService' => $pageService ), $parameterProvider->getViewParameters() );
    }
}
