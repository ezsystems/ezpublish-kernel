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
            [
                'id' => $languageId,
                'languageCode' => $languageCode,
                'name' => $languageName,
                'enabled' => $languageEnabled,
            ]
        );

        $languageCreateStruct = new LanguageCreateStruct();

        // string $method, array $parameters, mixed $return, int $emitNr, ?string $signalClass
        return [
            [
                'createLanguage',
                [$languageCreateStruct],
                $language,
                1,
                LanguageServiceSignals\CreateLanguageSignal::class,
                ['languageId' => $languageId],
            ],
            [
                'updateLanguageName',
                [$language, $languageNewName],
                $language,
                1,
                LanguageServiceSignals\UpdateLanguageNameSignal::class,
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
                LanguageServiceSignals\EnableLanguageSignal::class,
                [
                    'languageId' => $languageId,
                ],
            ],
            [
                'disableLanguage',
                [$language],
                $language,
                1,
                LanguageServiceSignals\DisableLanguageSignal::class,
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
                'loadLanguageListByCode',
                [[$languageCode]],
                [$languageCode => $language],
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
                'loadLanguageListById',
                [[$languageId]],
                [$languageId => $language],
                0,
            ],
            [
                'deleteLanguage',
                [$language],
                null,
                1,
                LanguageServiceSignals\DeleteLanguageSignal::class,
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
