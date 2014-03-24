<?php
/**
 * Created by PhpStorm.
 * User: lolautruche
 * Date: 24/03/14
 * Time: 14:13
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\LoaderString;
use PHPUnit_Framework_TestCase;

class LoaderStringTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider existsProvider
     */
    public function testExists( $name, $expectedResult )
    {
        $loaderString = new LoaderString();
        $this->assertSame( $expectedResult, $loaderString->exists( $name ) );
    }

    public function existsProvider()
    {
        return array(
            array( 'foo.html.twig', false ),
            array( 'foo/bar/baz.txt.twig', false ),
            array( 'SOMETHING.HTML.tWiG', false ),
            array( 'foo', true ),
            array( 'Hey, I love twig', true ),
            array( 'Hey, I love Twig', true ),
        );
    }
}
