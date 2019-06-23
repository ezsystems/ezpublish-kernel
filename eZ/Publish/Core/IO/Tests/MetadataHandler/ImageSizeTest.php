<?php

/**
 * File containing the ImageSize class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\MetadataHandler;

use eZ\Publish\Core\IO\MetadataHandler\ImageSize as ImageSizeMetadataHandler;
use PHPUnit\Framework\TestCase;

/**
 * @group fieldType
 * @group ezimage
 */
class ImageSizeTest extends TestCase
{
    public function testExtract()
    {
        $metadataHandler = new ImageSizeMetadataHandler();
        $file = 'eZ/Publish/Core/Repository/Tests/Service/Integration/ezplogo.png';
        self::assertEquals(
            ['width' => 189, 'height' => 200, 'mime' => 'image/png'],
            $metadataHandler->extract($file)
        );
    }
}
