<?php
/**
 * File containing the YamlSuggestionFormatter class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use Symfony\Component\Yaml\Yaml;

class YamlSuggestionFormatter implements SuggestionFormatterInterface
{
    public function format( ConfigSuggestion $configSuggestion )
    {
        $yamlConfig = Yaml::dump( $configSuggestion->getSuggestion(), 8 );
        if ( php_sapi_name() !== 'cli' )
        {
            $yamlConfig = "<pre>$yamlConfig</pre>";
        }

        return <<<EOT
{$configSuggestion->getMessage()}


Example:
========

$yamlConfig
EOT;
    }
}
