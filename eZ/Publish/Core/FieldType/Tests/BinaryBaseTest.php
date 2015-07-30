<?php

/**
 * File containing the BinaryBaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Tests;

/**
 * Base class for binary field types.
 *
 * @group fieldType
 */
abstract class BinaryBaseTest extends FieldTypeTest
{
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array(
            'FileSizeValidator' => array(
                'maxFileSize' => array(
                    'type' => 'int',
                    'default' => null,
                ),
            ),
        );
    }

    protected function getSettingsSchemaExpectation()
    {
        return array();
    }

    public function provideInvalidInputForAcceptValue()
    {
        return array(
            array(
                $this->getMockForAbstractClass('eZ\Publish\Core\FieldType\Value'),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
            array(
                array('id' => '/foo/bar'),
                'eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException',
            ),
        );
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
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 2342,
                    ),
                ),
            ),
            array(
                array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => null,
                    ),
                ),
            ),
        );
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
        return array(
            array(
                array(
                    'NonExistingValidator' => array(),
                ),
            ),
            array(
                // maxFileSize must be int or bool
                array(
                    'FileSizeValidator' => array(
                        'maxFileSize' => 'foo',
                    ),
                ),
            ),
            array(
                // maxFileSize is required for this validator
                array(
                    'FileSizeValidator' => array(),
                ),
            ),
        );
    }
}
