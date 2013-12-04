<?php
/**
 * File containing the FileSizeExtensionTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use Twig_Test_IntegrationTestCase;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\FileSizeExtension;
use Symfony\Component\Translation\TranslatorInterface;


class FileSizeExtensionTest extends Twig_Test_IntegrationTestCase
{
    /**
     * @param string $locale
     */
    protected $locale;
    /**
     * @param TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $TransMock;


    protected function setDefaultLocale( $locale )
    {
        locale_set_default( $locale );

        $this->locale = $locale;
        // todo configure the translatorInterface mock with the locale
    }

    protected function getExtensions()
    {
        return array(
            new FileSizeExtension( $this->getTranslatorInterfaceMock() )
        );
    }
    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/';
    }
    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTranslatorInterfaceMock()
    {
        $this->TransMock = $this->getMock( 'Symfony\Component\Translation\TranslatorInterface' );
        $this->TransMock
             ->expects( $this->any() )
             ->method( 'trans' )
             ->will( $this->returnCallback(
                /**
                 * @return string $suffixes
                 */
                 function ( $suffixes )
                 {
                     if( $this->locale == 'fr-FR' )
                     {
                         return $suffixes . ' French version';
                     }
                     elseif( $this->locale == 'en-GB' )
                     {
                         return  $suffixes . ' English version';
                     }
                     else
                     {
                         return $suffixes;
                     }
                 }
            )
        );
        return $this->TransMock;
    }
}
