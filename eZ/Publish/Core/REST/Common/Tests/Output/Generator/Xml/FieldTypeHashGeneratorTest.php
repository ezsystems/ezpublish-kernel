<?php

/**
 * File containing the  class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output\Generator\Xml;

use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Common\Tests\Output\Generator\FieldTypeHashGeneratorBaseTest;

class FieldTypeHashGeneratorTest extends FieldTypeHashGeneratorBaseTest
{
    /**
     * Initializes the field type hash generator.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Xml\FieldTypeHashGenerator
     */
    protected function initializeFieldTypeHashGenerator()
    {
        return new Common\Output\Generator\Xml\FieldTypeHashGenerator();
    }

    /**
     * Initializes the generator.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator
     */
    protected function initializeGenerator()
    {
        $generator = new Common\Output\Generator\Xml(
            $this->getFieldTypeHashGenerator()
        );
        $generator->setFormatOutput(true);

        return $generator;
    }
}
