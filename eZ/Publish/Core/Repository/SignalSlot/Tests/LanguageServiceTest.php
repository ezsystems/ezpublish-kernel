<?php
/**
 * File containing the LanguageServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;

use eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\Repository\SignalSlot\LanguageService;

class LanguageServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\LanguageService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new LanguageService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $languageId = 2;
        $languageCode = 'elv-TO';
        $languageName = 'Elvish';
        $languageEnabled = true;
        $languageNewName = 'Elfique';

        $language = new Language(
            array(
                'id' => $languageId,
                'languageCode' => $languageCode,
                'name' => $languageName,
                'enabled' => $languageEnabled
            )
        );

        $languageCreateStruct = new LanguageCreateStruct();

        return array(
            array(
                'createLanguage',
                array( $languageCreateStruct ),
                $language,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\CreateLanguageSignal',
                array( 'languageId' => $languageId )
            ),
            array(
                'updateLanguageName',
                array( $language, $languageNewName ),
                $language,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\UpdateLanguageNameSignal',
                array(
                    'languageId' => $languageId,
                    'newName' => $languageNewName
                )
            ),
            array(
                'enableLanguage',
                array( $language ),
                $language,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\EnableLanguageSignal',
                array(
                    'languageId' => $languageId,
                )
            ),
            array(
                'disableLanguage',
                array( $language ),
                $language,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\DisableLanguageSignal',
                array(
                    'languageId' => $languageId,
                )
            ),
            array(
                'loadLanguage',
                array( $languageCode ),
                $language,
                0
            ),
            array(
                'loadLanguages',
                array(),
                array( $language ),
                0
            ),
            array(
                'loadLanguageById',
                array( $languageId ),
                $language,
                0
            ),
            array(
                'deleteLanguage',
                array( $language ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\LanguageService\DeleteLanguageSignal',
                array(
                    'languageId' => $languageId,
                )
            ),
            array(
                'getDefaultLanguageCode',
                array(),
                $languageCode,
                0
            ),
            array(
                'newLanguageCreateStruct',
                array(),
                $languageCreateStruct,
                0
            ),
        );
    }
}
