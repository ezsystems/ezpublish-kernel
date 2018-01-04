<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\URLService as APIService;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\URLService;

class UrlServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return URLService::class;
    }

    public function providerForPassTroughMethods()
    {
        $url = new URL();
        $urlQuery = new URLQuery();
        $urlUpdateStruct = new URLUpdateStruct();

        // string $method, array $arguments, bool $return = true
        return [
            ['createUpdateStruct', []],
            ['findUrls', [$urlQuery]],
            ['findUsages', [$url]],
            ['findUsages', [$url, 10, 10]],
            ['loadById', [64]],
            ['loadByUrl', ['http://ez.no']],
            ['updateUrl', [$url, $urlUpdateStruct]],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
