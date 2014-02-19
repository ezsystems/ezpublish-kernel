<?php
/**
 * File containing the ConfigSuggestionCollector class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;

class SuggestionCollector implements SuggestionCollectorInterface
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion[]
     */
    private $suggestions = array();

    /**
     * Adds a config suggestion to the list.
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion $suggestion
     */
    public function addSuggestion( ConfigSuggestion $suggestion )
    {
        $this->suggestions[] = $suggestion;
    }

    /**
     * Returns all config suggestions.
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion[]
     */
    public function getSuggestions()
    {
        return $this->suggestions;
    }

    /**
     * @return boolean
     */
    public function hasSuggestions()
    {
        return !empty( $this->suggestions );
    }
}
