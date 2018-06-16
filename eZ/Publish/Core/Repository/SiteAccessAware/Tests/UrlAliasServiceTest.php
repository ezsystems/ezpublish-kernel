<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\URLAliasService as APIService;
use eZ\Publish\Core\Repository\SiteAccessAware\URLAliasService;
use eZ\Publish\Core\Repository\Values\Content\Location;

class UrlAliasServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return URLAliasService::class;
    }

    public function providerForPassTroughMethods()
    {
        $location = new Location();

        // string $method, array $arguments, bool $return = true
        return [
            ['createUrlAlias', [$location, '/Tomb/Raider', 'eng-AU', true, true]],
            ['createGlobalUrlAlias', ['root:bla', '/Tomb/Raider', 'eng-AU', true, true]],
            ['listGlobalAliases', ['eng-AU', 50, 50]],
            ['removeAliases', [[555]]],
            ['lookup', [['/James', 'eng-GB']]],
            ['load', [[555]]],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $location = new Location();

        $callback = function ($languageLookup) {
            $this->languageResolverMock
                ->expects($this->once())
                ->method('getShowAllTranslations')
                ->with($languageLookup ? null : true)
                ->willReturn(true);
        };

        // string $method, array $arguments, bool $return, int $languageArgumentIndex, callable $callback
        return [
            ['listLocationAliases', [$location, false, 'eng-AU', null, self::LANG_ARG], true, 4, $callback],
            ['reverseLookup', [$location, 'eng-AU', null, self::LANG_ARG], true, 3, $callback],
        ];
    }

    protected function setLanguagesLookupExpectedArguments(array $arguments, $languageArgumentIndex, array $languages)
    {
        $arguments[$languageArgumentIndex] = $languages;
        $arguments[$languageArgumentIndex - 1] = true;

        return $arguments;
    }

    protected function setLanguagesPassTroughArguments(array $arguments, $languageArgumentIndex, array $languages)
    {
        return $this->setLanguagesLookupExpectedArguments($arguments, $languageArgumentIndex, $languages);
    }
}
