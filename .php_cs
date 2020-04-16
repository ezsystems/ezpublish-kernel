<?php

return EzSystems\EzPlatformCodeStyle\PhpCsFixer\Config::create()
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([__DIR__ . '/eZ'])
            ->exclude(
                [
                    'Bundle/EzPublishCoreBundle/Tests/DependencyInjection/Fixtures',
                    'Publish/API/Repository/Tests/FieldType/_fixtures',
                    'Publish/API/Repository/Tests/_fixtures',
                    'Publish/Core/FieldType/Tests/Url/Gateway/_fixtures',
                    'Publish/Core/IO/Tests/_fixtures',
                    'Publish/Core/MVC/Symfony/Templating/Tests/Twig/Extension/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/Location/Gateway/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/Type/Gateway/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/Type/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/UrlAlias/Gateway/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/UrlAlias/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/UrlWildcard/Gateway/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/Content/_fixtures',
                    'Publish/Core/Persistence/Legacy/Tests/_fixtures',
                    'Publish/Core/Persistence/Tests/TransformationProcessor/_fixtures',
                    'Publish/Core/Repository/Tests/Service/Integration/Legacy/_fixtures',
                    'Publish/Core/Search/Legacy/Tests/_fixtures',
                    'Publish/SPI/Tests/FieldType/_fixtures',
                ]
            )
            ->files()->name('*.php')
    );
