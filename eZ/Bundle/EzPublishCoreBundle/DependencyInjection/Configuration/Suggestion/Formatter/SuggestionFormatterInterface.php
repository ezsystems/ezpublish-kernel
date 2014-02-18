<?php
/**
 * File containing the SuggestionFormatterInterface class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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
    public function format( ConfigSuggestion $configSuggestion );
}
