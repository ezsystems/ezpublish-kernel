<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\SearchService as APIService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Repository\SiteAccessAware\SearchService;

class SearchServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return SearchService::class;
    }

    public function providerForPassTroughMethods()
    {
        // string $method, array $arguments, bool $return = true
        return [
            ['suggest', ['prefix', [], 11]],
            ['supports', [SearchService::CAPABILITY_ADVANCED_FULLTEXT]],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $query = new Query();
        $locationQuery = new LocationQuery();
        $criterion = new Query\Criterion\ContentId(44);

        $callback = function ($languageLookup) {
            $this->languageResolverMock
                ->expects($this->once())
                ->method('getUseAlwaysAvailable')
                ->with($languageLookup ? null : true)
                ->willReturn(true);
        };

        // string $method, array $arguments, bool $return, int $languageArgumentIndex, callable $callback
        return [
            ['findContent', [$query, self::LANG_ARG, false], true, 1, $callback],
            ['findContentInfo', [$query, self::LANG_ARG, false], true, 1, $callback],
            ['findSingle', [$criterion, self::LANG_ARG, false], true, 1, $callback],
            ['findLocations', [$locationQuery, self::LANG_ARG, false], true, 1, $callback],
        ];
    }

    protected function setLanguagesLookupArguments(array $arguments, $languageArgumentIndex)
    {
        $arguments[$languageArgumentIndex] = [
            'languages' => [],
            'useAlwaysAvailable' => null,
        ];

        return $arguments;
    }

    protected function setLanguagesLookupExpectedArguments(array $arguments, $languageArgumentIndex, array $languages)
    {
        $arguments[$languageArgumentIndex] = [
            'languages' => $languages,
            'useAlwaysAvailable' => true,
        ];

        return $arguments;
    }

    protected function setLanguagesPassTroughArguments(array $arguments, $languageArgumentIndex, array $languages)
    {
        $arguments[$languageArgumentIndex] = [
            'languages' => $languages,
            'useAlwaysAvailable' => true,
        ];

        return $arguments;
    }
}
