<?php

/**
 * File containing the BinaryBaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\FieldType\Value;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Base class for binary field types.
 *
 * @group fieldType
 */
abstract class BinaryBaseTest extends FieldTypeTest
{
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return [
            'FileSizeValidator' => [
                'maxFileSize' => [
                    'type' => 'int',
                    'default' => null,
                ],
            ],
        ];
    }

    protected function getSettingsSchemaExpectation()
    {
        return [];
    }

    public function provideInvalidInputForAcceptValue()
    {
        return [
            [
                $this->getMockForAbstractClass(Value::class),
                InvalidArgumentException::class,
            ],
            [
                ['id' => '/foo/bar'],
                InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * Provide data sets with validator configurations which are considered
     * valid by the {@link validateValidatorConfiguration()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(),
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  'minIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideValidValidatorConfiguration()
    {
        return [
            [
                [],
            ],
            [
                [
                    'FileSizeValidator' => [
                        'maxFileSize' => 2342,
                    ],
                ],
            ],
            [
                [
                    'FileSizeValidator' => [
                        'maxFileSize' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide data sets with validator configurations which are considered
     * invalid by the {@link validateValidatorConfiguration()} method. The
     * method must return a non-empty array of validation errors when receiving
     * one of the provided values.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of validator configurations.
     *
     * For example:
     *
     * <code>
     *  return array(
     *      array(
     *          array(
     *              'NonExistentValidator' => array(),
     *          ),
     *      ),
     *      array(
     *          array(
     *              // Typos
     *              'InTEgervALUeVALIdator' => array(
     *                  'minIntegerValue' => 0,
     *                  'maxIntegerValue' => 23,
     *              )
     *          )
     *      ),
     *      array(
     *          array(
     *              'IntegerValueValidator' => array(
     *                  // Incorrect value types
     *                  'minIntegerValue' => true,
     *                  'maxIntegerValue' => false,
     *              )
     *          )
     *      ),
     *      // ...
     *  );
     * </code>
     *
     * @return array
     */
    public function provideInvalidValidatorConfiguration()
    {
        return [
            [
                [
                    'NonExistingValidator' => [],
                ],
            ],
            [
                // maxFileSize must be int or bool
                [
                    'FileSizeValidator' => [
                        'maxFileSize' => 'foo',
                    ],
                ],
            ],
            [
                // maxFileSize is required for this validator
                [
                    'FileSizeValidator' => [],
                ],
            ],
        ];
    }
}
