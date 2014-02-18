<?php
/**
 * File containing the YamlSuggestionFormatterTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Tests\Formatter;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter\YamlSuggestionFormatter;
use PHPUnit_Framework_TestCase;

class YamlSuggestionFormatterTest extends PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $message = <<<EOT
Database configuration has changed for eZ Content repository.
Please define:
 - An entry in ezpublish.repositories
 - A Doctrine connection (You may check configuration reference for Doctrine "config:dump-reference doctrine" console command.)
 - A reference to configured repository in ezpublish.system.foo.repository
EOT;
        $suggestion = new ConfigSuggestion( $message );
        $suggestion->setMandatory( true );
        $suggestionArray = array(
            'doctrine' => array(
                'dbal' => array(
                    'connections' => array(
                        'default' => array(
                            'driver' => 'pdo_mysql',
                            'host' => 'localhost',
                            'dbname' => 'my_database',
                            'user' => 'my_user',
                            'password' => 'some_password',
                            'charset' => 'UTF8'
                        )
                    )
                )
            ),
            'ezpublish' => array(
                'repositories' => array(
                    'my_repository' => array( 'engine' => 'legacy', 'connection' => 'default' )
                ),
                'system' => array(
                    'foo' => array(
                        'repository' => 'my_repository'
                    )
                )
            )
        );
        $suggestion->setSuggestion( $suggestionArray );

        $expectedMessage = <<<EOT
Database configuration has changed for eZ Content repository.
Please define:
 - An entry in ezpublish.repositories
 - A Doctrine connection (You may check configuration reference for Doctrine "config:dump-reference doctrine" console command.)
 - A reference to configured repository in ezpublish.system.foo.repository


Example:
========

doctrine:
    dbal:
        connections:
            default:
                driver: pdo_mysql
                host: localhost
                dbname: my_database
                user: my_user
                password: some_password
                charset: UTF8
ezpublish:
    repositories:
        my_repository:
            engine: legacy
            connection: default
    system:
        foo:
            repository: my_repository
EOT;

        $formatter = new YamlSuggestionFormatter();
        $this->assertSame( $expectedMessage, trim( $formatter->format( $suggestion ) ) );

    }
}
