<?php

/**
 * File containing the YamlSuggestionFormatterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Tests\Formatter;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Formatter\YamlSuggestionFormatter;
use PHPUnit\Framework\TestCase;

class YamlSuggestionFormatterTest extends TestCase
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
        $suggestion = new ConfigSuggestion($message);
        $suggestion->setMandatory(true);
        $suggestionArray = [
            'doctrine' => [
                'dbal' => [
                    'connections' => [
                        'default' => [
                            'driver' => 'pdo_mysql',
                            'host' => 'localhost',
                            'dbname' => 'my_database',
                            'user' => 'my_user',
                            'password' => 'some_password',
                            'charset' => 'UTF8',
                        ],
                    ],
                ],
            ],
            'ezpublish' => [
                'repositories' => [
                    'my_repository' => ['engine' => 'legacy', 'connection' => 'default'],
                ],
                'system' => [
                    'foo' => [
                        'repository' => 'my_repository',
                    ],
                ],
            ],
        ];
        $suggestion->setSuggestion($suggestionArray);

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
        $this->assertSame($expectedMessage, trim($formatter->format($suggestion)));
    }

    public function testFormatNoSuggestion()
    {
        $message = 'This is a message';
        $suggestion = new ConfigSuggestion($message);
        $formatter = new YamlSuggestionFormatter();
        $this->assertSame($message, $formatter->format($suggestion));
    }
}
