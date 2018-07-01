<?php

/**
 * File containing the TranslationCollectorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Translation\GlobCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compilation pass loads every ezplatform available translations into symfony translator.
 */
class TranslationCollectorPass implements CompilerPassInterface
{
    /** @var array */
    public const LOCALES_MAP = [
        'de_DE' => 'de',
        'el_GR' => 'el',
        'es_ES' => 'es',
        'fi_FI' => 'fi',
        'fr_FR' => 'fr',
        'hi_IN' => 'hi',
        'hu_HU' => 'hu',
        'ja_JP' => 'ja',
        'nb_NO' => 'no',
        'pl_PL' => 'pl',
        'pt_PT' => 'pt',
        'ru_RU' => 'ru',
        'en_US' => 'en',
        'it_IT' => 'it',
        'hr_HR' => 'hr',
    ];

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translator.default')) {
            return;
        }

        $definition = $container->getDefinition('translator.default');
        $collector = new GlobCollector($container->getParameterBag()->get('kernel.root_dir'));

        $availableTranslations = [];
        foreach ($collector->collect() as $file) {
            /* TODO - to remove when translation files will have proper names. */
            if (isset(self::LOCALES_MAP[$file['locale']])) {
                $file['locale'] = self::LOCALES_MAP[$file['locale']];
            }
            $availableTranslations[] = $file['locale'];

            $definition->addMethodCall(
                'addResource',
                array($file['format'], $file['file'], $file['locale'], $file['domain'])
            );
        }

        $container->setParameter('available_translations', array_values(array_unique($availableTranslations)));
    }
}
