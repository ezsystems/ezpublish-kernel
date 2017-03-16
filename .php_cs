<?php

if (class_exists('Symfony\CS\Config\Config')) {
    // PHP-CS-Fixer 1.x syntax (deprecated)
    return Symfony\CS\Config\Config::create()
        ->setUsingLinter(false)
        ->setUsingCache(true)
        ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
        ->fixers([
            'concat_with_spaces',
            '-concat_without_spaces',
            '-empty_return',
            '-phpdoc_params',
            '-phpdoc_separation',
            '-phpdoc_to_comment',
            '-spaces_cast',
            '-blankline_after_open_tag',
            '-single_blank_line_before_namespace',
            '-phpdoc_annotation_without_dot',
        ])
        ->finder(
            Symfony\CS\Finder\DefaultFinder::create()
                ->in(__DIR__)
                ->exclude([
                    'bin/.travis',
                    'data',
                    'doc',
                    'var',
                    'vendor',
                    'eZ/Bundle/EzPublishCoreBundle/Tests/DependencyInjection/Fixtures',
                    'eZ/Publish/API/Repository/Tests/FieldType/_fixtures',
                    'eZ/Publish/API/Repository/Tests/_fixtures',
                    'eZ/Publish/Core/FieldType/Tests/RichText/Converter/Xslt/_fixtures',
                    'eZ/Publish/Core/FieldType/Tests/RichText/Gateway/_fixtures',
                    'eZ/Publish/Core/FieldType/Tests/Url/Gateway/_fixtures',
                    'eZ/Publish/Core/FieldType/Tests/XmlText/Converter/_fixtures',
                    'eZ/Publish/Core/IO/Tests/_fixtures',
                    'eZ/Publish/Core/MVC/Symfony/Templating/Tests/Twig/Extension/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/Location/Gateway/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/Type/Gateway/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/Type/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/UrlAlias/Gateway/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/UrlAlias/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/UrlWildcard/Gateway/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/Content/_fixtures',
                    'eZ/Publish/Core/Persistence/Legacy/Tests/_fixtures',
                    'eZ/Publish/Core/Persistence/Tests/TransformationProcessor/_fixtures',
                    'eZ/Publish/Core/REST/Common/Tests/Input/Handler/_fixtures',
                    'eZ/Publish/Core/REST/Common/Tests/Output/Generator/_fixtures',
                    'eZ/Publish/Core/REST/Client',
                    'eZ/Publish/Core/Repository/Tests/Service/Integration/Legacy/_fixtures',
                    'eZ/Publish/Core/Search/Legacy/Tests/_fixtures',
                    'eZ/Publish/SPI/Tests/FieldType/_fixtures',
                ])
                ->files()->name('*.php')
        )
    ;
}

// PHP-CS-Fixer 2.x syntax
return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => false,
        'simplified_null_return' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'cast_spaces' => false,
        'blank_line_after_opening_tag' => false,
        'single_blank_line_before_namespace' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_no_alias_tag' => false,
        'space_after_semicolon' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude([
                'bin/.travis',
                'data',
                'doc',
                'var',
                'vendor',
                'eZ/Bundle/EzPublishCoreBundle/Tests/DependencyInjection/Fixtures',
                'eZ/Publish/API/Repository/Tests/FieldType/_fixtures',
                'eZ/Publish/API/Repository/Tests/_fixtures',
                'eZ/Publish/Core/FieldType/Tests/RichText/Converter/Xslt/_fixtures',
                'eZ/Publish/Core/FieldType/Tests/RichText/Gateway/_fixtures',
                'eZ/Publish/Core/FieldType/Tests/Url/Gateway/_fixtures',
                'eZ/Publish/Core/FieldType/Tests/XmlText/Converter/_fixtures',
                'eZ/Publish/Core/IO/Tests/_fixtures',
                'eZ/Publish/Core/MVC/Symfony/Templating/Tests/Twig/Extension/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/Location/Gateway/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/Type/Gateway/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/Type/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/UrlAlias/Gateway/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/UrlAlias/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/UrlWildcard/Gateway/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/Content/_fixtures',
                'eZ/Publish/Core/Persistence/Legacy/Tests/_fixtures',
                'eZ/Publish/Core/Persistence/Tests/TransformationProcessor/_fixtures',
                'eZ/Publish/Core/REST/Common/Tests/Input/Handler/_fixtures',
                'eZ/Publish/Core/REST/Common/Tests/Output/Generator/_fixtures',
                'eZ/Publish/Core/REST/Client',
                'eZ/Publish/Core/Repository/Tests/Service/Integration/Legacy/_fixtures',
                'eZ/Publish/Core/Search/Legacy/Tests/_fixtures',
                'eZ/Publish/SPI/Tests/FieldType/_fixtures',
            ])
            ->files()->name('*.php')
    )
;
