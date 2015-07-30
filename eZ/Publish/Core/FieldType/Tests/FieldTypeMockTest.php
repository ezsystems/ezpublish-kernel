<?php

/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeMockTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests;

use PHPUnit_Framework_TestCase;

class FieldTypeMockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testApplyDefaultSettingsThrowsInvalidArgumentException()
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\FieldType\\FieldType',
            array(),
            '',
            false
        );

        $stub->applyDefaultSettings(new \DateTime());
    }

    /**
     * @dataProvider providerForTestApplyDefaultSettings
     *
     * @covers \eZ\Publish\Core\FieldType\FieldType::applyDefaultSettings
     */
    public function testApplyDefaultSettings($initialSettings, $expectedSettings)
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\FieldType\\FieldType',
            array(),
            '',
            false,
            true,
            true,
            array('getSettingsSchema')
        );

        $stub
            ->expects($this->any())
            ->method('getSettingsSchema')
            ->will(
                $this->returnValue(
                    array(
                        'true' => array(
                            'default' => true,
                        ),
                        'false' => array(
                            'default' => false,
                        ),
                        'null' => array(
                            'default' => null,
                        ),
                        'zero' => array(
                            'default' => 0,
                        ),
                        'int' => array(
                            'default' => 42,
                        ),
                        'float' => array(
                            'default' => 42.42,
                        ),
                        'string' => array(
                            'default' => 'string',
                        ),
                        'emptystring' => array(
                            'default' => '',
                        ),
                        'emptyarray' => array(
                            'default' => array(),
                        ),
                        'nodefault' => array(),
                    )
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
        return array(
            array(
                array(),
                array(
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'zero' => 0,
                    'int' => 42,
                    'float' => 42.42,
                    'string' => 'string',
                    'emptystring' => '',
                    'emptyarray' => array(),
                ),
            ),
            array(
                array(
                    'true' => 'foo',
                ),
                array(
                    'true' => 'foo',
                    'false' => false,
                    'null' => null,
                    'zero' => 0,
                    'int' => 42,
                    'float' => 42.42,
                    'string' => 'string',
                    'emptystring' => '',
                    'emptyarray' => array(),
                ),
            ),
            array(
                array(
                    'null' => 'foo',
                ),
                array(
                    'null' => 'foo',
                    'true' => true,
                    'false' => false,
                    'zero' => 0,
                    'int' => 42,
                    'float' => 42.42,
                    'string' => 'string',
                    'emptystring' => '',
                    'emptyarray' => array(),
                ),
            ),
            array(
                $array = array(
                    'false' => true,
                    'emptystring' => array('foo'),
                    'null' => 'notNull',
                    'additionalEntry' => 'baz',
                    'zero' => 10,
                    'int' => 'this is not an int',
                    'string' => null,
                    'emptyarray' => array(array()),
                    'true' => false,
                    'float' => true,
                ),
                $array,
            ),
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testApplyDefaultValidatorConfigurationEmptyThrowsInvalidArgumentException()
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\FieldType\\FieldType',
            array(),
            '',
            false
        );

        $stub->applyDefaultValidatorConfiguration(new \DateTime());
    }

    public function testApplyDefaultValidatorConfigurationEmpty()
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\FieldType\\FieldType',
            array(),
            '',
            false,
            true,
            true,
            array('getValidatorConfigurationSchema')
        );

        $stub
            ->expects($this->any())
            ->method('getValidatorConfigurationSchema')
            ->will(
                $this->returnValue(array())
            );

        $validatorConfiguration = null;
        $stub->applyDefaultValidatorConfiguration($validatorConfiguration);
        $this->assertSame(
            null,
            $validatorConfiguration
        );
    }

    /**
     * @dataProvider providerForTestApplyDefaultValidatorConfiguration
     */
    public function testApplyDefaultValidatorConfiguration($initialConfiguration, $expectedConfiguration)
    {
        /** @var \eZ\Publish\Core\FieldType\FieldType|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockForAbstractClass(
            '\\eZ\\Publish\\Core\\FieldType\\FieldType',
            array(),
            '',
            false,
            true,
            true,
            array('getValidatorConfigurationSchema')
        );

        $stub
            ->expects($this->any())
            ->method('getValidatorConfigurationSchema')
            ->will(
                $this->returnValue(
                    array(
                        'TestValidator' => array(
                            'valueClick' => array(
                                'default' => 1,
                            ),
                            'valueClack' => array(
                                'default' => 0,
                            ),
                        ),
                    )
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
        $defaultConfiguration = array(
            'TestValidator' => array(
                'valueClick' => 1,
                'valueClack' => 0,
            ),
        );

        return array(
            array(
                null,
                $defaultConfiguration,
            ),
            array(
                array(),
                $defaultConfiguration,
            ),
            array(
                array(
                    'TestValidator' => array(
                        'valueClick' => 100,
                    ),
                ),
                array(
                    'TestValidator' => array(
                        'valueClick' => 100,
                        'valueClack' => 0,
                    ),
                ),
            ),
        );
    }
}
