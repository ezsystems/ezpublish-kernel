<?php

/**
 * File containing the YamlSuggestionFormatter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use Symfony\Component\Yaml\Yaml;

class YamlSuggestionFormatter implements SuggestionFormatterInterface
{
    public function format(ConfigSuggestion $configSuggestion)
    {
        $message = $configSuggestion->getMessage();
        $suggestion = $configSuggestion->getSuggestion();
        if ($suggestion) {
            $yamlConfig = Yaml::dump($suggestion, 8);
            if (PHP_SAPI !== 'cli') {
                $yamlConfig = "<pre>$yamlConfig</pre>";
            }

            return <<<EOT
{$message}


Example:
========

$yamlConfig
EOT;
        }

        return $message;
    }
}
