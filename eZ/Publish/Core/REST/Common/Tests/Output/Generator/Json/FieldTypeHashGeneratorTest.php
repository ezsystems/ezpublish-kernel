<?php

/**
 * File containing the  class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output\Generator\Json;

use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Common\Tests\Output\Generator\FieldTypeHashGeneratorBaseTest;

class FieldTypeHashGeneratorTest extends FieldTypeHashGeneratorBaseTest
{
    /**
     * Initializes the field type hash generator.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Json\FieldTypeHashGenerator
     */
    protected function initializeFieldTypeHashGenerator()
    {
        return new Common\Output\Generator\Json\FieldTypeHashGenerator();
    }

    /**
     * Initializes the generator.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator
     */
    protected function initializeGenerator()
    {
        return new Common\Output\Generator\Json(
            $this->getFieldTypeHashGenerator()
        );
    }
}
