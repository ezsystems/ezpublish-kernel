<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\ResourceProvider;
use PHPUnit\Framework\TestCase;

class ResourceProviderTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\Templating\Twig\ResourceProvider */
    protected $resourceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configResolver = $this->getConfigResolverMock();
        $this->resourceProvider = new ResourceProvider($this->configResolver);
    }

    public function testGetFieldViewResources(): void
    {
        $resources = $this->resourceProvider->getFieldViewResources();
        $templates = array_column($resources, 'template');
        $priorities = array_column($resources, 'priority');

        $this->assertEquals('templates/fields_override1.html.twig', array_shift($templates));
        $this->assertEquals('templates/fields_override2.html.twig', array_shift($templates));
        $this->assertEquals('templates/fields_default.html.twig', array_shift($templates));

        $this->assertEquals(10, array_shift($priorities));
        $this->assertEquals(20, array_shift($priorities));
        $this->assertEquals(0, array_shift($priorities));
    }

    public function testGetFieldEditResources(): void
    {
        $resources = $this->resourceProvider->getFieldEditResources();
        $templates = array_column($resources, 'template');
        $priorities = array_column($resources, 'priority');

        $this->assertEquals('templates/fields_override1.html.twig', array_shift($templates));
        $this->assertEquals('templates/fields_override2.html.twig', array_shift($templates));
        $this->assertEquals('templates/fields_default.html.twig', array_shift($templates));

        $this->assertEquals(10, array_shift($priorities));
        $this->assertEquals(20, array_shift($priorities));
        $this->assertEquals(0, array_shift($priorities));
    }

    public function testGetFieldDefinitionViewResources(): void
    {
        $resources = $this->resourceProvider->getFieldDefinitionViewResources();
        $templates = array_column($resources, 'template');
        $priorities = array_column($resources, 'priority');

        $this->assertEquals('templates/settings_override1.html.twig', array_shift($templates));
        $this->assertEquals('templates/settings_override2.html.twig', array_shift($templates));
        $this->assertEquals('templates/settings_default.html.twig', array_shift($templates));

        $this->assertEquals(10, array_shift($priorities));
        $this->assertEquals(20, array_shift($priorities));
        $this->assertEquals(0, array_shift($priorities));
    }

    public function testGetFieldDefinitionEditResources(): void
    {
        $resources = $this->resourceProvider->getFieldDefinitionEditResources();
        $templates = array_column($resources, 'template');
        $priorities = array_column($resources, 'priority');

        $this->assertEquals('templates/settings_override1.html.twig', array_shift($templates));
        $this->assertEquals('templates/settings_override2.html.twig', array_shift($templates));
        $this->assertEquals('templates/settings_default.html.twig', array_shift($templates));

        $this->assertEquals(10, array_shift($priorities));
        $this->assertEquals(20, array_shift($priorities));
        $this->assertEquals(0, array_shift($priorities));
    }

    /**
     * Returns mocked ConfigResolver.
     *
     * Make sure returned resource lists are not sorted as ResourceProvider is sorting them
     *
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConfigResolverMock(): ConfigResolverInterface
    {
        $mock = $this->createMock(ConfigResolverInterface::class);
        $mock
            ->method('getParameter')
            ->willReturnMap([
                [
                    'field_templates',
                    null,
                    null,
                    [
                        [
                            'template' => 'templates/fields_override1.html.twig',
                            'priority' => 10,
                        ],
                        [
                            'template' => 'templates/fields_override2.html.twig',
                            'priority' => 20,
                        ],
                        [
                            'template' => 'templates/fields_default.html.twig',
                            'priority' => 0,
                        ],
                    ],
                ],
                [
                    'field_edit_templates',
                    null,
                    null,
                    [
                        [
                            'template' => 'templates/fields_override1.html.twig',
                            'priority' => 10,
                        ],
                        [
                            'template' => 'templates/fields_override2.html.twig',
                            'priority' => 20,
                        ],
                        [
                            'template' => 'templates/fields_default.html.twig',
                            'priority' => 0,
                        ],
                    ],
                ],
                [
                    'fielddefinition_settings_templates',
                    null,
                    null,
                    [
                        [
                            'template' => 'templates/settings_override1.html.twig',
                            'priority' => 10,
                        ],
                        [
                            'template' => 'templates/settings_override2.html.twig',
                            'priority' => 20,
                        ],
                        [
                            'template' => 'templates/settings_default.html.twig',
                            'priority' => 0,
                        ],
                    ],
                ],
                [
                    'fielddefinition_edit_templates',
                    null,
                    null,
                    [
                        [
                            'template' => 'templates/settings_override1.html.twig',
                            'priority' => 10,
                        ],
                        [
                            'template' => 'templates/settings_override2.html.twig',
                            'priority' => 20,
                        ],
                        [
                            'template' => 'templates/settings_default.html.twig',
                            'priority' => 0,
                        ],
                    ],
                ],
            ])
        ;

        return $mock;
    }
}
