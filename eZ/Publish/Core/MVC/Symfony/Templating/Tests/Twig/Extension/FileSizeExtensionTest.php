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

/**
 * Class FileSizeExtensionTest
 *
 * @package eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension
 */
class FileSizeExtensionTest extends Twig_Test_IntegrationTestCase
{
    /**
     * @param string $locale
     */
    protected $locale;

    /**
     * @param array $suffixes
     */
    protected $suffixes = array( 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB' );

    /**
     * @param TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translatorMock;

    /**
     * @param string $locale
     */
    protected function setDefaultLocale( $locale )
    {
        locale_set_default( $locale );
        $this->locale = $locale;
    }

    /**
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return array(
            new FileSizeExtension( $this->getTranslatorInterfaceMock(), $this->suffixes )
        );
    }

    /**
     * @return string
     */
    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/';
    }

    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTranslatorInterfaceMock()
    {
        $that = $this;
        $this->translatorMock = $this->getMock( 'Symfony\Component\Translation\TranslatorInterface' );
        $this->translatorMock
            ->expects( $this->any() )->method( 'trans' )->will(
                $this->returnCallback(
                    function ( $suffixes ) use ( $that )
                    {
                        if ( $that->getLocale() == 'fr-FR' )
                        {
                            return $suffixes . ' French version';
                        }
                        else if ( $that->getLocale() == 'en-GB' )
                        {
                            return $suffixes . ' English version';
                        }
                        else
                        {
                            return $suffixes;
                        }
                    }
                )
            );
        return $this->translatorMock;
    }
}
