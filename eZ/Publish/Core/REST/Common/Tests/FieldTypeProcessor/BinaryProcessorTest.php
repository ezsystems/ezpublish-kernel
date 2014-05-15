<?php
/**
 * File containing the BinaryProcessorTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\BinaryProcessor;

class BinaryProcessorTest extends BinaryInputProcessorTest
{
    const TEMPLATE_URL = 'http://ez.no/subdir/var/rest_test/storage/original/{path}';

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\BinaryProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash()
    {
        $path = 'application/815b3aa9.pdf';
        $processor = $this->getProcessor();

        $inputHash = array(
            'path' => $path,
        );

        $outputHash = $processor->postProcessValueHash( $inputHash );

        $this->assertEquals(
            array(
                'path' => $path,
                'url' => str_replace( '{path}', $path, self::TEMPLATE_URL )
            ),
            $outputHash
        );
    }

    /**
     * Returns the processor under test
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\BinaryProcessor
     */
    protected function getProcessor()
    {
        return new BinaryProcessor(
            $this->getTempDir(),
            self::TEMPLATE_URL
        );
    }
}
