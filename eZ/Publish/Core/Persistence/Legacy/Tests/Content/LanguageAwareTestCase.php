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
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache,
    eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for Language aware classes
 */
abstract class LanguageAwareTestCase extends TestCase
{
    /**
     * Returns the Language Lookup mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Lookup
     */
    protected function getLanguageLookupMock()
    {
        $langLookup = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Lookup'
        );

        $cache = new Cache();

        $languageUs = new Language();
        $languageUs->id = 2;
        $languageUs->locale = 'eng-US';

        $cache->store( $languageUs );

        $languageGb = new Language();
        $languageGb->id = 4;
        $languageGb->locale = 'eng-GB';

        $cache->store( $languageGb );

        $langLookup->expects( $this->any() )
            ->method( 'getById' )
            ->will( $this->returnCallback(
                function ( $id ) use ( $cache )
                {
                    return $cache->getById( $id );
                }
        ) );
        $langLookup->expects( $this->any() )
            ->method( 'getByLocale' )
            ->will( $this->returnCallback(
                function ( $locale ) use ( $cache )
                {
                    return $cache->getByLocale( $locale );
                }
        ) );

        return $langLookup;
    }
}
