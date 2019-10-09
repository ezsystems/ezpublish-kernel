<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\LanguageService as APIService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\LanguageService;

class LanguageServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return LanguageService::class;
    }

    public function providerForPassTroughMethods()
    {
        $languageCreateStruct = new LanguageCreateStruct();
        $language = new Language();

        // string $method, array $arguments, bool $return = true
        return [
            ['createLanguage', [$languageCreateStruct], $language],

            ['updateLanguageName', [$language, 'Afrikaans'], $language],

            ['enableLanguage', [$language], $language],

            ['disableLanguage', [$language], $language],

            ['loadLanguage', ['eng-GB'], $language],
            ['loadLanguageListByCode', [['eng-GB']], []],

            ['loadLanguages', [], []],

            ['loadLanguageById', [4], $language],
            ['loadLanguageListById', [[4]], []],

            ['deleteLanguage', [$language], null],

            ['getDefaultLanguageCode', [], ''],

            ['newLanguageCreateStruct', [], $languageCreateStruct],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
