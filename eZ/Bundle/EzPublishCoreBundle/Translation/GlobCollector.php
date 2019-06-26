<?php

/**
 * File containing the GlobCollector class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Translation;

/**
 * Retrieves all installed ezplatform translation files ie those installed as ezsystems/ezplatform-i18n-* package.
 */
class GlobCollector implements Collector
{
    /** @var string */
    private $tranlationPattern;

    /**
     * @param string $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->tranlationPattern = $kernelRootDir . sprintf('%1$s..%1$svendor%1$sezplatform-i18n%1$sezplatform-i18n-*%1$s*%1$s*.xlf', DIRECTORY_SEPARATOR);
    }

    /**
     * @return array
     */
    public function collect()
    {
        $meta = [];
        foreach (glob($this->tranlationPattern) as $file) {
            list($domain, $locale, $format) = explode('.', basename($file), 3);
            $meta[] = [
                'file' => $file,
                'domain' => $domain,
                'locale' => $locale,
                'format' => $format,
            ];
        }

        return $meta;
    }
}
