<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Values;

use eZ\Publish\SPI\Repository\Values\MultiLanguageDescription;
use eZ\Publish\SPI\Repository\Values\MultiLanguageName;
use ReflectionClass;

/**
 * Test internal functionality defined by MultiLanguage* Traits.
 *
 * Note: this test trait assumes object defines names and descriptions in eng-US and pol-PL.
 */
trait MultiLanguageTestTrait
{
    /**
     * @depends testNewClassWithMultiLanguageProperties
     *
     * @param \eZ\Publish\SPI\Repository\Values\MultiLanguageName $object tested ValueObject
     */
    public function testGetMultiLanguagePrioritizedName($object)
    {
        if (!$object instanceof MultiLanguageName) {
            self::markTestSkipped(
                get_class($object) . ' does not implement ' . MultiLanguageName::class
            );
        }

        $names = $object->getNames();
        self::assertSame($names['pol-PL'], $object->getName());
        self::assertSame($names['eng-US'], $object->getName('eng-US'));
        self::assertSame($names['pol-PL'], $object->getName('pol-PL'));
    }

    /**
     * @depends testNewClassWithMultiLanguageProperties
     *
     * @param \eZ\Publish\SPI\Repository\Values\MultiLanguageName $object tested ValueObject
     */
    public function testGetMultiLanguageDefaultName($object)
    {
        if (!$object instanceof MultiLanguageName) {
            self::markTestSkipped(
                get_class($object) . ' does not implement ' . MultiLanguageName::class
            );
        }

        $reflection = new ReflectionClass($object);
        $prioritizedLanguagesProperty = $reflection->getProperty('prioritizedLanguages');
        $defaultLanguageProperty = $reflection->getProperty('mainLanguageCode');

        // set not defined language to force default one
        $prioritizedLanguagesProperty->setAccessible(true);
        $prioritizedLanguagesProperty->setValue($object, ['ger-DE']);

        $names = $object->getNames();
        self::assertSame($names['eng-US'], $object->getName());

        // set other defined language as default
        $defaultLanguageProperty->setAccessible(true);
        $defaultLanguageProperty->setValue($object, 'pol-PL');
        self::assertSame($names['pol-PL'], $object->getName());
    }

    /**
     * @depends testNewClassWithMultiLanguageProperties
     *
     * @param \eZ\Publish\SPI\Repository\Values\MultiLanguageDescription $object tested ValueObject
     */
    public function testGetMultiLanguagePrioritizedDescription($object)
    {
        if (!$object instanceof MultiLanguageDescription) {
            self::markTestSkipped(
                get_class($object) . ' does not implement ' . MultiLanguageDescription::class
            );
        }

        $names = $object->getDescriptions();
        self::assertSame($names['pol-PL'], $object->getDescription());
        self::assertSame($names['eng-US'], $object->getDescription('eng-US'));
        self::assertSame($names['pol-PL'], $object->getDescription('pol-PL'));
    }

    /**
     * @depends testNewClassWithMultiLanguageProperties
     *
     * @param \eZ\Publish\SPI\Repository\Values\MultiLanguageDescription $object tested ValueObject
     */
    public function testGetMultiLanguageDefaultDescription($object)
    {
        if (!$object instanceof MultiLanguageDescription) {
            self::markTestSkipped(
                get_class($object) . ' does not implement ' . MultiLanguageDescription::class
            );
        }

        $reflection = new ReflectionClass($object);
        $prioritizedLanguagesProperty = $reflection->getProperty('prioritizedLanguages');

        $defaultLanguageProperty = $reflection->getProperty('mainLanguageCode');
        $defaultLanguageProperty->setAccessible(true);

        // set not defined language to force default one
        $prioritizedLanguagesProperty->setAccessible(true);
        $prioritizedLanguagesProperty->setValue($object, ['ger-DE']);

        $descriptions = $object->getDescriptions();
        foreach ($descriptions as $languageCode => $description) {
            // set $languageCode as default
            $defaultLanguageProperty->setValue($object, $languageCode);
            self::assertSame($description, $object->getDescription());
        }
    }
}
