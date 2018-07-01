<?php

/**
 * File containing the LanguageServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\LanguageService as APILanguageService;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\LanguageService;
use eZ\Publish\Core\SignalSlot\Signal\LanguageService as LanguageServiceSignals;

class LanguageServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APILanguageService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new LanguageService($coreService, $dispatcher);
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
                'enabled' => $languageEnabled,
            )
        );

        $languageCreateStruct = new LanguageCreateStruct();

        return array(
            array(
                'createLanguage',
                array($languageCreateStruct),
                $language,
                1,
                LanguageServiceSignals\CreateLanguageSignal::class,
                array('languageId' => $languageId),
            ),
            array(
                'updateLanguageName',
                array($language, $languageNewName),
                $language,
                1,
                LanguageServiceSignals\UpdateLanguageNameSignal::class,
                array(
                    'languageId' => $languageId,
                    'newName' => $languageNewName,
                ),
            ),
            array(
                'enableLanguage',
                array($language),
                $language,
                1,
                LanguageServiceSignals\EnableLanguageSignal::class,
                array(
                    'languageId' => $languageId,
                ),
            ),
            array(
                'disableLanguage',
                array($language),
                $language,
                1,
                LanguageServiceSignals\DisableLanguageSignal::class,
                array(
                    'languageId' => $languageId,
                ),
            ),
            array(
                'loadLanguage',
                array($languageCode),
                $language,
                0,
            ),
            array(
                'loadLanguages',
                array(),
                array($language),
                0,
            ),
            array(
                'loadLanguageById',
                array($languageId),
                $language,
                0,
            ),
            array(
                'deleteLanguage',
                array($language),
                null,
                1,
                LanguageServiceSignals\DeleteLanguageSignal::class,
                array(
                    'languageId' => $languageId,
                ),
            ),
            array(
                'getDefaultLanguageCode',
                array(),
                $languageCode,
                0,
            ),
            array(
                'newLanguageCreateStruct',
                array(),
                $languageCreateStruct,
                0,
            ),
        );
    }
}
