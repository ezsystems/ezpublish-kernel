<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Search\Legacy\Content\Mapper\FullTextMapper;

/**
 * Test case for Language aware classes.
 */
abstract class LanguageAwareTestCase extends TestCase
{
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

    /**
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldNameGeneratorMock;

    /**
     * @return \eZ\Publish\Core\Search\Common\FieldNameGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFieldNameGeneratorMock()
    {
        if (!isset($this->fieldNameGeneratorMock)) {
            $this->fieldNameGeneratorMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Search\\Common\\FieldNameGenerator')
                ->disableOriginalConstructor()
                ->getMock();
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
            $this->getMock('\\eZ\\Publish\\Core\\Search\\Common\\FieldRegistry'),
            $contentTypeHandler
        );
    }
}
