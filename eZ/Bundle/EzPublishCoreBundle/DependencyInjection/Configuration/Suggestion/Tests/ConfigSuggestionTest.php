<?php
/**
 * File containing the ConfigSuggestionTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Tests;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use PHPUnit_Framework_TestCase;

class ConfigSuggestionTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyConstructor()
    {
        $suggestion = new ConfigSuggestion();
        $this->assertNull( $suggestion->getMessage() );
        $this->assertSame( array(), $suggestion->getSuggestion() );
        $this->assertFalse( $suggestion->isMandatory() );
    }

    public function testConfigSuggestion()
    {
        $message = 'some message';
        $configArray = array( 'foo' => 'bar' );

        $suggestion = new ConfigSuggestion( $message, $configArray );
        $this->assertSame( $message, $suggestion->getMessage() );
        $this->assertSame( $configArray, $suggestion->getSuggestion() );
        $this->assertFalse( $suggestion->isMandatory() );

        $newMessage = 'foo bar';
        $suggestion->setMessage( $newMessage );
        $this->assertSame( $newMessage, $suggestion->getMessage() );

        $newConfigArray = array( 'ez' => 'publish' );
        $suggestion->setSuggestion( $newConfigArray );
        $this->assertSame( $newConfigArray, $suggestion->getSuggestion() );

        $suggestion->setMandatory( true );
        $this->assertTrue( $suggestion->isMandatory() );
    }
}
