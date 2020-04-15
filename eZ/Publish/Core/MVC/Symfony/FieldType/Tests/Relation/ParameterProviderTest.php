<?php

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\Relation;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\Relation\Value;
use eZ\Publish\Core\MVC\Symfony\FieldType\Relation\ParameterProvider;
use PHPUnit\Framework\TestCase;

class ParameterProviderTest extends TestCase
{
    public function providerForTestGetViewParameters()
    {
        return [
            [ContentInfo::STATUS_DRAFT, ['available' => true]],
            [ContentInfo::STATUS_PUBLISHED, ['available' => true]],
            [ContentInfo::STATUS_TRASHED, ['available' => false]],
        ];
    }

    /**
     * @dataProvider providerForTestGetViewParameters
     */
    public function testGetViewParameters($status, array $expected)
    {
        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadContentInfo')
            ->will(TestCase::returnValue(
                new ContentInfo(['status' => $status])
            ));

        $parameterProvider = new ParameterProvider($contentServiceMock);
        $parameters = $parameterProvider->getViewParameters(new Field([
            'value' => new Value(123),
        ]));

        TestCase::assertSame($parameters, $expected);
    }

    public function testNotFoundGetViewParameters()
    {
        $contentId = 123;

        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadContentInfo')
            ->will(TestCase::throwException(new NotFoundException('ContentInfo', $contentId)));

        $parameterProvider = new ParameterProvider($contentServiceMock);
        $parameters = $parameterProvider->getViewParameters(new Field([
            'value' => new Value($contentId),
        ]));

        TestCase::assertSame($parameters, ['available' => false]);
    }

    public function testUnauthorizedGetViewParameters()
    {
        $contentId = 123;

        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadContentInfo')
            ->will(TestCase::throwException(new UnauthorizedException('content', 'read')));

        $parameterProvider = new ParameterProvider($contentServiceMock);
        $parameters = $parameterProvider->getViewParameters(new Field([
            'value' => new Value($contentId),
        ]));

        TestCase::assertSame($parameters, ['available' => false]);
    }
}
