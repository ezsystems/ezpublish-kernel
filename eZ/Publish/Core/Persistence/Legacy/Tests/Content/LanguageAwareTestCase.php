<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Search\Legacy\Content\Mapper\FullTextMapper;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Common\FieldRegistry;

/**
 * Test case for Language aware classes.
 */
abstract class LanguageAwareTestCase extends TestCase
{
    protected const ENG_GB = 'eng-GB';

    /**
     * Language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingLanguageHandler
     */
    protected $languageHandler;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Returns a language handler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        if (!isset($this->languageHandler)) {
            $this->languageHandler = new LanguageHandlerMock();
        }

        return $this->languageHandler;
    }

    /**
     * Returns a language mask generator.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGenerator()
    {
        if (!isset($this->languageMaskGenerator)) {
            $this->languageMaskGenerator = new LanguageMaskGenerator(
                $this->getLanguageHandler()
            );
        }

        return $this->languageMaskGenerator;
    }

    /**
     * Return definition-based transformation processor instance.
     *
     * @return Persistence\TransformationProcessor\DefinitionBased
     */
    protected function getDefinitionBasedTransformationProcessor()
    {
        return new Persistence\TransformationProcessor\DefinitionBased(
            new Persistence\TransformationProcessor\DefinitionBased\Parser(),
            new Persistence\TransformationProcessor\PcreCompiler(
                new Persistence\Utf8Converter()
            ),
            glob(__DIR__ . '/../../../../Persistence/Tests/TransformationProcessor/_fixtures/transformations/*.tr')
        );
    }

    /** @var \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit\Framework\MockObject\MockObject */
    protected $fieldNameGeneratorMock;

    /**
     * @return \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldNameGeneratorMock()
    {
        if (!isset($this->fieldNameGeneratorMock)) {
            $this->fieldNameGeneratorMock = $this->createMock(FieldNameGenerator::class);
        }

        return $this->fieldNameGeneratorMock;
    }

    /**
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler $contentTypeHandler
     * @return \eZ\Publish\Core\Search\Legacy\Content\Mapper\FullTextMapper
     */
    protected function getFullTextMapper(Persistence\Legacy\Content\Type\Handler $contentTypeHandler)
    {
        return new FullTextMapper(
            $this->createMock(FieldRegistry::class),
            $contentTypeHandler
        );
    }

    /**
     * Get FullText search configuration.
     */
    protected function getFullTextSearchConfiguration()
    {
        return [
            'stopWordThresholdFactor' => 0.66,
            'enableWildcards' => true,
            'commands' => [
                'apostrophe_normalize',
                'apostrophe_to_doublequote',
                'ascii_lowercase',
                'ascii_search_cleanup',
                'cyrillic_diacritical',
                'cyrillic_lowercase',
                'cyrillic_search_cleanup',
                'cyrillic_transliterate_ascii',
                'doublequote_normalize',
                'endline_search_normalize',
                'greek_diacritical',
                'greek_lowercase',
                'greek_normalize',
                'greek_transliterate_ascii',
                'hebrew_transliterate_ascii',
                'hyphen_normalize',
                'inverted_to_normal',
                'latin1_diacritical',
                'latin1_lowercase',
                'latin1_transliterate_ascii',
                'latin-exta_diacritical',
                'latin-exta_lowercase',
                'latin-exta_transliterate_ascii',
                'latin_lowercase',
                'latin_search_cleanup',
                'latin_search_decompose',
                'math_to_ascii',
                'punctuation_normalize',
                'space_normalize',
                'special_decompose',
                'specialwords_search_normalize',
                'tab_search_normalize',
            ],
        ];
    }
}
