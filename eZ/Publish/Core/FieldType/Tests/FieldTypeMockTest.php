<?php

/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeMockTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\FieldType\FieldType;

class FieldTypeMockTest extends TestCase
{
    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testApplyDefaultSettingsThrowsInvalidArgumentException()
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            FieldType::class,
            [],
            '',
            false
        );

        $fieldSettings = new \DateTime();

        $stub->applyDefaultSettings($fieldSettings);
    }

    /**
     * @dataProvider providerForTestApplyDefaultSettings
     *
     * @covers \eZ\Publish\Core\FieldType\FieldType::applyDefaultSettings
     */
    public function testApplyDefaultSettings($initialSettings, $expectedSettings)
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            FieldType::class,
            [],
            '',
            false,
            true,
            true,
            ['getSettingsSchema']
        );

        $stub
            ->expects($this->any())
            ->method('getSettingsSchema')
            ->will(
                $this->returnValue(
                    [
                        'true' => [
                            'default' => true,
                        ],
                        'false' => [
                            'default' => false,
                        ],
                        'null' => [
                            'default' => null,
                        ],
                        'zero' => [
                            'default' => 0,
                        ],
                        'int' => [
                            'default' => 42,
                        ],
                        'float' => [
                            'default' => 42.42,
                        ],
                        'string' => [
                            'default' => 'string',
                        ],
                        'emptystring' => [
                            'default' => '',
                        ],
                        'emptyarray' => [
                            'default' => [],
                        ],
                        'nodefault' => [],
                    ]
                )
            );

        $fieldSettings = $initialSettings;
        $stub->applyDefaultSettings($fieldSettings);
        $this->assertSame(
            $expectedSettings,
            $fieldSettings
        );
    }

    public function providerForTestApplyDefaultSettings()
    {
        return [
            [
                [],
                [
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'zero' => 0,
                    'int' => 42,
                    'float' => 42.42,
                    'string' => 'string',
                    'emptystring' => '',
                    'emptyarray' => [],
                ],
            ],
            [
                [
                    'true' => 'foo',
                ],
                [
                    'true' => 'foo',
                    'false' => false,
                    'null' => null,
                    'zero' => 0,
                    'int' => 42,
                    'float' => 42.42,
                    'string' => 'string',
                    'emptystring' => '',
                    'emptyarray' => [],
                ],
            ],
            [
                [
                    'null' => 'foo',
                ],
                [
                    'null' => 'foo',
                    'true' => true,
                    'false' => false,
                    'zero' => 0,
                    'int' => 42,
                    'float' => 42.42,
                    'string' => 'string',
                    'emptystring' => '',
                    'emptyarray' => [],
                ],
            ],
            [
                $array = [
                    'false' => true,
                    'emptystring' => ['foo'],
                    'null' => 'notNull',
                    'additionalEntry' => 'baz',
                    'zero' => 10,
                    'int' => 'this is not an int',
                    'string' => null,
                    'emptyarray' => [[]],
                    'true' => false,
                    'float' => true,
                ],
                $array,
            ],
        ];
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testApplyDefaultValidatorConfigurationEmptyThrowsInvalidArgumentException()
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            FieldType::class,
            [],
            '',
            false
        );

        $validatorConfiguration = new \DateTime();

        $stub->applyDefaultValidatorConfiguration($validatorConfiguration);
    }

    public function testApplyDefaultValidatorConfigurationEmpty()
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            FieldType::class,
            [],
            '',
            false,
            true,
            true,
            ['getValidatorConfigurationSchema']
        );

        $stub
            ->expects($this->any())
            ->method('getValidatorConfigurationSchema')
            ->will(
                $this->returnValue([])
            );

        $validatorConfiguration = null;
        $stub->applyDefaultValidatorConfiguration($validatorConfiguration);
        $this->assertNull(
            $validatorConfiguration
        );
    }

    /**
     * @dataProvider providerForTestApplyDefaultValidatorConfiguration
     */
    public function testApplyDefaultValidatorConfiguration($initialConfiguration, $expectedConfiguration)
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            FieldType::class,
            [],
            '',
            false,
            true,
            true,
            ['getValidatorConfigurationSchema']
        );

        $stub
            ->expects($this->any())
            ->method('getValidatorConfigurationSchema')
            ->will(
                $this->returnValue(
                    [
                        'TestValidator' => [
                            'valueClick' => [
                                'default' => 1,
                            ],
                            'valueClack' => [
                                'default' => 0,
                            ],
                        ],
                    ]
                )
            );

        $validatorConfiguration = $initialConfiguration;
        $stub->applyDefaultValidatorConfiguration($validatorConfiguration);
        $this->assertSame(
            $expectedConfiguration,
            $validatorConfiguration
        );
    }

    public function providerForTestApplyDefaultValidatorConfiguration()
    {
        $defaultConfiguration = [
            'TestValidator' => [
                'valueClick' => 1,
                'valueClack' => 0,
            ],
        ];

        return [
            [
                null,
                $defaultConfiguration,
            ],
            [
                [],
                $defaultConfiguration,
            ],
            [
                [
                    'TestValidator' => [
                        'valueClick' => 100,
                    ],
                ],
                [
                    'TestValidator' => [
                        'valueClick' => 100,
                        'valueClack' => 0,
                    ],
                ],
            ],
        ];
    }
}
