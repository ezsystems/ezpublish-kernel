<?php
/**
 * File containing the AliasCleanerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Legacy\Tests\Image;

use eZ\Publish\Core\MVC\Legacy\Image\AliasCleaner;
use PHPUnit_Framework_TestCase;

class AliasCleanerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Image\AliasCleaner
     */
    private $aliasCleaner;

    /**
     * @var \eZ\Publish\Core\FieldType\Image\AliasCleanerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $innerAliasCleaner;

    /**
     * @var \eZ\Publish\Core\IO\UrlRedecoratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRedecorator;

    protected function setUp()
    {
        parent::setUp();
        $this->innerAliasCleaner = $this->getMock( 'eZ\Publish\Core\FieldType\Image\AliasCleanerInterface' );
        $this->urlRedecorator = $this->getMock( 'eZ\Publish\Core\IO\UrlRedecoratorInterface' );
        $this->aliasCleaner = new AliasCleaner( $this->innerAliasCleaner, $this->urlRedecorator );
    }

    public function testRemoveAliases()
    {
        $originalPath = 'foo/bar/test.jpg';

        $this->urlRedecorator
            ->expects( $this->once() )
            ->method( 'redecorateFromTarget' )
            ->with( $originalPath );

        $this->innerAliasCleaner
            ->expects( $this->once() )
            ->method( 'removeAliases' );

        $this->aliasCleaner->removeAliases( $originalPath );
    }
}
