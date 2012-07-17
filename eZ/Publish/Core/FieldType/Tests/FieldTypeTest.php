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
     */
    protected $validatorService;

    /**
     * @var \eZ\Publish\API\Repository\FieldTypeTools
     */
    protected $fieldTypeTools;

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
}
