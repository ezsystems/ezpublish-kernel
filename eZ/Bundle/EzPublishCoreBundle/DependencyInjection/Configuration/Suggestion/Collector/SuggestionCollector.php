<?php

/**
 * File containing the ConfigSuggestionCollector class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;

class SuggestionCollector implements SuggestionCollectorInterface
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion[] */
    private $suggestions = [];

    /**
     * Adds a config suggestion to the list.
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion $suggestion
     */
    public function addSuggestion(ConfigSuggestion $suggestion)
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
     * @return bool
     */
    public function hasSuggestions()
    {
        return !empty($this->suggestions);
    }
}
