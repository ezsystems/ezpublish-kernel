<?php

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Tests\Collector;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollector;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use PHPUnit_Framework_TestCase;

class SuggestionCollectorTest extends PHPUnit_Framework_TestCase
{
    public function testAddHasGetSuggestions()
    {
        $collector = new SuggestionCollector();
        $suggestions = array( new ConfigSuggestion(), new ConfigSuggestion(), new ConfigSuggestion() );
        foreach ( $suggestions as $suggestion )
        {
            $collector->addSuggestion( $suggestion );
        }

        $this->assertTrue( $collector->hasSuggestions() );
        $this->assertSame( $suggestions, $collector->getSuggestions() );
    }
}
