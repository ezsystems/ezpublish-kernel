<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\FieldTypeService as APIService;
use eZ\Publish\Core\Repository\SiteAccessAware\FieldTypeService;

class FieldTypeServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return FieldTypeService::class;
    }

    public function providerForPassTroughMethods()
    {
        // string $method, array $arguments, bool $return = true
        return [
            ['getFieldTypes', []],

            ['getFieldType', ['ezrichtext']],

            ['hasFieldType', ['ezrichtext']],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
