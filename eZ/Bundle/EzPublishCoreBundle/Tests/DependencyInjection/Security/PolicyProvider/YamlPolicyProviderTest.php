<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\StubYamlPolicyProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Config\Resource\FileResource;

class YamlPolicyProviderTest extends PHPUnit_Framework_TestCase
{
    public function testSingleYaml()
    {
        $files = [__DIR__ . '/../../Fixtures/policies1.yml'];
        $provider = new StubYamlPolicyProvider($files);
        $expectedConfig = [
            'custom_module' => [
                'custom_function_1' => null,
                'custom_function_2' => ['CustomLimitation'],
            ],
            'helloworld' => [
                'foo' => ['bar'],
                'baz' => null,
            ],
        ];

        $configBuilder = $this->getMock('\eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigBuilderInterface');
        foreach ($files as $file) {
            $configBuilder
                ->expects($this->once())
                ->method('addResource')
                ->with($this->equalTo(new FileResource($file)));
        }
        $configBuilder
            ->expects($this->once())
            ->method('addConfig')
            ->with($expectedConfig);

        $provider->addPolicies($configBuilder);
    }

    public function testMultipleYaml()
    {
        $file1 = __DIR__ . '/../../Fixtures/policies1.yml';
        $file2 = __DIR__ . '/../../Fixtures/policies2.yml';
        $files = [$file1, $file2];
        $provider = new StubYamlPolicyProvider($files);
        $expectedConfig = [
            'custom_module' => [
                'custom_function_1' => null,
                'custom_function_2' => ['CustomLimitation'],
            ],
            'helloworld' => [
                'foo' => ['bar'],
                'baz' => null,
                'some' => ['thingy', 'thing', 'but', 'wait'],
            ],
            'custom_module2' => [
                'custom_function_3' => null,
                'custom_function_4' => ['CustomLimitation2', 'CustomLimitation3'],
            ],
        ];

        $configBuilder = $this->getMock('\eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigBuilderInterface');
        $configBuilder
            ->expects($this->exactly(count($files)))
            ->method('addResource')
            ->willReturnMap([
                [$this->equalTo(new FileResource($file1)), null],
                [$this->equalTo(new FileResource($file2)), null],
            ]);
        $configBuilder
            ->expects($this->once())
            ->method('addConfig')
            ->with($expectedConfig);

        $provider->addPolicies($configBuilder);
    }
}
