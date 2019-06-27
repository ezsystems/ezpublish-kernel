<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Converter;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\ContentService;
use eZ\Bundle\EzPublishCoreBundle\Converter\ContentParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ContentParamConverterTest extends AbstractParamConverterTest
{
    const PROPERTY_NAME = 'contentId';

    const CONTENT_CLASS = Content::class;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Converter\ContentParamConverter */
    private $converter;

    private $contentServiceMock;

    public function setUp()
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->converter = new ContentParamConverter($this->contentServiceMock);
    }

    public function testSupports()
    {
        $config = $this->createConfiguration(self::CONTENT_CLASS);
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApplyContent()
    {
        $id = 42;
        $valueObject = $this->createMock(Content::class);

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContent')
            ->with($id)
            ->will($this->returnValue($valueObject));

        $request = new Request([], [], [self::PROPERTY_NAME => $id]);
        $config = $this->createConfiguration(self::CONTENT_CLASS, 'content');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf(self::CONTENT_CLASS, $request->attributes->get('content'));
    }

    public function testApplyContentOptionalWithEmptyAttribute()
    {
        $request = new Request([], [], [self::PROPERTY_NAME => null]);
        $config = $this->createConfiguration(self::CONTENT_CLASS, 'content');

        $config->expects($this->once())
            ->method('isOptional')
            ->will($this->returnValue(true));

        $this->assertFalse($this->converter->apply($request, $config));
        $this->assertNull($request->attributes->get('content'));
    }
}
