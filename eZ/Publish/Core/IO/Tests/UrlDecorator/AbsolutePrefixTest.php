<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\UrlDecorator;

use eZ\Publish\Core\IO\UrlDecorator\AbsolutePrefix;
use PHPUnit_Framework_TestCase;

/**
 * Test case for IO Service
 */
class AbsolutePrefixTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testDecorate( $url, $prefix, $decoratedUrl )
    {
        $decorator = new AbsolutePrefix( $prefix );

        self::assertEquals(
            $decoratedUrl,
            $decorator->decorate( $url )
        );
    }

    /**
     * @dataProvider provideData
     */
    public function testUndecorate( $url, $prefix, $decoratedUrl )
    {
        $decorator = new AbsolutePrefix( $prefix );

        self::assertEquals(
            $url,
            $decorator->undecorate( $decoratedUrl )
        );
    }

    public function provideData()
    {
        return array(
            array(
                'images/file.png',
                'var/storage',
                '/var/storage/images/file.png'
            ),
            array(
                'images/file.png',
                'http://static.example.com',
                'http://static.example.com/images/file.png'
            )
        );
    }
}
