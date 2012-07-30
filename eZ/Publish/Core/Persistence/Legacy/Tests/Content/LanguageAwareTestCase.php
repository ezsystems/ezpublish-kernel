<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;

/**
 * Test case for Language aware classes
 */
abstract class LanguageAwareTestCase extends TestCase
{
    /**
     * Language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingLanguageHandler
     */
    protected $languageHandler;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $this->languageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $this->languageHandler->expects( $this->any() )
                ->method( 'load' )
                ->will(
                    $this->returnValue(
                        new Language(
                            array(
                                'id' => 2,
                                'languageCode' => 'eng-GB',
                                'name' => 'British english'
                            )
                        )
                    )
                );
            $this->languageHandler->expects( $this->any() )
                ->method( 'loadByLanguageCode' )
                ->will(
                    $this->returnValue(
                        new Language(
                            array(
                                'id' => 2,
                                'languageCode' => 'eng-GB',
                                'name' => 'British english'
                            )
                        )
                    )
                );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a language mask generator
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGenerator()
    {
        if ( !isset( $this->languageMaskGenerator ) )
        {
            $this->languageMaskGenerator = new LanguageMaskGenerator(
                $this->getLanguageHandler()
            );
        }
        return $this->languageMaskGenerator;
    }
}
