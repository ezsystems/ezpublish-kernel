<?php

/**
 * File containing the LanguageServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\LanguageService;

class LanguageServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\LanguageService'
        );
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
            [
                'id' => $languageId,
                'languageCode' => $languageCode,
                'name' => $languageName,
                'enabled' => $languageEnabled,
            ]
        );

        $languageCreateStruct = new LanguageCreateStruct();

        return [
            [
                'createLanguage',
                [$languageCreateStruct],
                $language,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LanguageService\CreateLanguageSignal',
                ['languageId' => $languageId],
            ],
            [
                'updateLanguageName',
                [$language, $languageNewName],
                $language,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LanguageService\UpdateLanguageNameSignal',
                [
                    'languageId' => $languageId,
                    'newName' => $languageNewName,
                ],
            ],
            [
                'enableLanguage',
                [$language],
                $language,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LanguageService\EnableLanguageSignal',
                [
                    'languageId' => $languageId,
                ],
            ],
            [
                'disableLanguage',
                [$language],
                $language,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LanguageService\DisableLanguageSignal',
                [
                    'languageId' => $languageId,
                ],
            ],
            [
                'loadLanguage',
                [$languageCode],
                $language,
                0,
            ],
            [
                'loadLanguages',
                [],
                [$language],
                0,
            ],
            [
                'loadLanguageById',
                [$languageId],
                $language,
                0,
            ],
            [
                'deleteLanguage',
                [$language],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LanguageService\DeleteLanguageSignal',
                [
                    'languageId' => $languageId,
                ],
            ],
            [
                'getDefaultLanguageCode',
                [],
                $languageCode,
                0,
            ],
            [
                'newLanguageCreateStruct',
                [],
                $languageCreateStruct,
                0,
            ],
        ];
    }
}
