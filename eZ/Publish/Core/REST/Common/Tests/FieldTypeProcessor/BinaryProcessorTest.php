<?php

/**
 * File containing the BinaryProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
        $uri = '/var/ezdemo_site/storage/original/application/815b3aa9.pdf';
        $processor = $this->getProcessor();

        $inputHash = [
            'uri' => '/var/ezdemo_site/storage/original/application/815b3aa9.pdf',
        ];

        $outputHash = $processor->postProcessValueHash($inputHash);

        $expectedUri = 'http://static.example.com' . $uri;
        $this->assertEquals(
            [
                'url' => $expectedUri,
                'uri' => $expectedUri,
            ],
            $outputHash
        );
    }

    /**
     * Returns the processor under test.
     *
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\BinaryProcessor
     */
    protected function getProcessor()
    {
        return new BinaryProcessor(
            $this->getTempDir(),
            'http://static.example.com'
        );
    }
}
