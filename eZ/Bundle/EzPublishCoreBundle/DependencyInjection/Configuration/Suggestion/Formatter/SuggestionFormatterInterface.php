<?php

/**
 * File containing the SuggestionFormatterInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;

/**
 * Interface for ConfigSuggestion formatters.
 *
 * A SuggestionFormatter will convert a ConfigSuggestion value object to a human readable format
 * (e.g. YAML, XML, JSON...).
 */
interface SuggestionFormatterInterface
{
    public function format(ConfigSuggestion $configSuggestion);
}
