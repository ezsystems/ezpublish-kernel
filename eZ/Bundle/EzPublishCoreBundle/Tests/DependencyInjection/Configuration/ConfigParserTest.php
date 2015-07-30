<?php

/**
 * File containing the ConfigParserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser;
use PHPUnit_Framework_TestCase;
use stdClass;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class ConfigParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function testConstructWrongInnerParser()
    {
        new ConfigParser(
            array(
                $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
                new stdClass(),
            )
        );
    }

    public function testConstruct()
    {
        $innerParsers = array(
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
        );
        $configParser = new ConfigParser($innerParsers);
        $this->assertSame($innerParsers, $configParser->getConfigParsers());
    }

    public function testGetSetInnerParsers()
    {
        $configParser = new ConfigParser();
        $this->assertSame(array(), $configParser->getConfigParsers());

        $innerParsers = array(
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
        );
        $configParser->setConfigParsers($innerParsers);
        $this->assertSame($innerParsers, $configParser->getConfigParsers());
    }

    public function testMapConfig()
    {
        $parsers = array(
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
        );
        $configParser = new ConfigParser($parsers);

        $scopeSettings = array(
            'foo' => 'bar',
            'some' => 'thing',
        );
        $currentScope = 'the_current_scope';
        $contextualizer = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface');

        foreach ($parsers as $parser) {
            /* @var \PHPUnit_Framework_MockObject_MockObject $parser */
            $parser
                ->expects($this->once())
                ->method('mapConfig')
                ->with($scopeSettings, $currentScope, $contextualizer);
        }

        $configParser->mapConfig($scopeSettings, $currentScope, $contextualizer);
    }

    public function testPrePostMap()
    {
        $parsers = array(
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
        );
        $configParser = new ConfigParser($parsers);

        $config = array(
            'foo' => 'bar',
            'some' => 'thing',
        );
        $contextualizer = $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface');

        foreach ($parsers as $parser) {
            /* @var \PHPUnit_Framework_MockObject_MockObject $parser */
            $parser
                ->expects($this->once())
                ->method('preMap')
                ->with($config, $contextualizer);
            $parser
                ->expects($this->once())
                ->method('postMap')
                ->with($config, $contextualizer);
        }

        $configParser->preMap($config, $contextualizer);
        $configParser->postMap($config, $contextualizer);
    }

    public function testAddSemanticConfig()
    {
        $parsers = array(
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface'),
        );
        $configParser = new ConfigParser($parsers);

        $nodeBuilder = new NodeBuilder();

        foreach ($parsers as $parser) {
            /* @var \PHPUnit_Framework_MockObject_MockObject $parser */
            $parser
                ->expects($this->once())
                ->method('addSemanticConfig')
                ->with($nodeBuilder);
        }

        $configParser->addSemanticConfig($nodeBuilder);
    }
}
