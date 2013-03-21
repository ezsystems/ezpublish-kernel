<?php
/**
 * File containing the DefinitionBasedAdapterTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Tests\Adapter;

use eZ\Publish\API\Repository\Values\ValueObject;

class DefinitionBasedAdapterTest extends ValueObjectAdapterTest
{
    protected function getAdapter( ValueObject $valueObject, array $map )
    {
        $adapter = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\MVC\\Legacy\\Templating\\Adapter\\DefinitionBasedAdapter' )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $adapter
            ->expects( $this->once() )
            ->method( 'definition' )
            ->will( $this->returnValue( $map ) );
        $adapter->__construct( $valueObject );

        return $adapter;
    }
}
