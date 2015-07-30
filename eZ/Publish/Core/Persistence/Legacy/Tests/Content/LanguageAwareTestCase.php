<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;

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
}
