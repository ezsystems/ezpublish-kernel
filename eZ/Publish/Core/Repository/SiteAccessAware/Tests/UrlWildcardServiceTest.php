<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\URLWildcardService as APIService;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\Core\Repository\SiteAccessAware\URLWildcardService;

class UrlWildcardServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return URLWildcardService::class;
    }

    public function providerForPassTroughMethods()
    {
        $urlWildcard = new URLWildcard();

        // string $method, array $arguments, bool $return = true
        return [
            ['create', ['/home', '/and/away', true]],
            ['remove', [$urlWildcard]],
            ['load', [64]],
            ['loadAll', [50, 50]],
            ['translate', ['/and/away']],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
