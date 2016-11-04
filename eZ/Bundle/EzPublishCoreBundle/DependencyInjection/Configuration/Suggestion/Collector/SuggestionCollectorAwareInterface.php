<?php

/**
 * File containing the SuggestionCollectorAwareInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector;

interface SuggestionCollectorAwareInterface
{
    /**
     * Injects SuggestionCollector.
     *
     * @param SuggestionCollectorInterface $suggestionCollector
     */
    public function setSuggestionCollector(SuggestionCollectorInterface $suggestionCollector);
}
