<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Tests\Collector;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use PHPUnit\Framework\TestCase;

class SuggestionCollectorTest extends TestCase
{
    public function testAddHasGetSuggestions()
    {
        $collector = new SuggestionCollector();
        $suggestions = [new ConfigSuggestion(), new ConfigSuggestion(), new ConfigSuggestion()];
        foreach ($suggestions as $suggestion) {
            $collector->addSuggestion($suggestion);
        }

        $this->assertTrue($collector->hasSuggestions());
        $this->assertSame($suggestions, $collector->getSuggestions());
    }
}
