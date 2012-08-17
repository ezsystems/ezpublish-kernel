<?php
/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\Core\Repository\ValidatorService,
    eZ\Publish\Core\Repository\FieldTypeTools,
    PHPUnit_Framework_TestCase;

abstract class FieldTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Repository\ValidatorService
     * @todo Remove usage from derive tests in favor of mock.
     */
    protected $validatorService;

    /**
     * @var \eZ\Publish\API\Repository\FieldTypeTools
     * @todo Remove usage from derive tests in favor of mock.
     */
    protected $fieldTypeTools;

    /**
     * Validator service mock.
     *
     * @see getValidatorServiceMock()
     * @var ValidatorService
     */
    private $validatorServiceMock;

    /**
     * Field type tools mock.
     *
     * @see getFieldTypeToolsMock()
     * @var FieldTypeTools
     */
    private $fieldTypeToolsMock;

    /**
     * File service mock.
     *
     * @see getFileServiceMock()
     * @var FileService
     */
    private $fileServiceMock;

    protected function setUp()
    {
        parent::setUp();
        $this->validatorService = new ValidatorService;
        $this->fieldTypeTools = new FieldTypeTools;
    }

    protected function tearDown()
    {
        unset( $this->validatorService, $this->fieldTypeTools );
        parent::tearDown();
    }

    /**
     * Returns a re-usable validator service mock.
     *
     * Returns a mock unique for the execution of a single test case. The same
     * instance can be received multiple times by calling this method.
     *
     * @return ValidatorService
     */
    protected function getValidatorServiceMock()
    {
        if ( !isset( $this->validatorServiceMock ) )
        {
            $this->validatorServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\Repository\\ValidatorService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->validatorServiceMock;
    }

    /**
     * Returns a re-usable field type tools mock.
     *
     * Returns a mock unique for the execution of a single test case. The same
     * instance can be received multiple times by calling this method.
     *
     * @return FieldTypeTools
     */
    protected function getFieldTypeToolsMock()
    {
        if ( !isset( $this->fieldTypeToolsMock ) )
        {
            $this->fieldTypeToolsMock = $this->getMock(
                'eZ\\Publish\\Core\\Repository\\FieldTypeTools',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->fieldTypeToolsMock;
    }

    /**
     * Returns a re-usable file service mock.
     *
     * Returns a mock unique for the execution of a single test case. The same
     * instance can be received multiple times by calling this method.
     *
     * @return FileService
     */
    protected function getFileServiceMock()
    {
        if ( !isset( $this->fileServiceMock ) )
        {
            $this->fileServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\FieldType\\FileService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->fileServiceMock;
    }
}
