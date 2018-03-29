<?php

namespace eZ\Publish\Core\MVC\Symfony\FieldType\Tests\Relation;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\RelationList\Value;
use eZ\Publish\Core\MVC\Symfony\FieldType\RelationList\ParameterProvider;
use PHPUnit\Framework\TestCase;

class ParameterProviderTest extends TestCase
{
    public function providerForTestGetViewParameters()
    {
        return [
            [[123, 456, 789], ['available' => [123 => true, 456 => true, 789 => false]]],
            [[123, 456], ['available' => [123 => true, 456 => true]]],
            [[789], ['available' => [789 => false]]],
            [[], ['available' => []]],
        ];
    }

    /**
     * @dataProvider providerForTestGetViewParameters
     */
    public function testGetViewParameters(array $desinationContentIds, array $expected)
    {
        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadContentInfo')
            ->will(TestCase::returnValueMap([
                [123, new ContentInfo(['status' => ContentInfo::STATUS_DRAFT])],
                [456, new ContentInfo(['status' => ContentInfo::STATUS_PUBLISHED])],
                [789, new ContentInfo(['status' => ContentInfo::STATUS_TRASHED])],
            ]));

        $parameterProvider = new ParameterProvider($contentServiceMock);
        $parameters = $parameterProvider->getViewParameters(new Field([
            'value' => new Value($desinationContentIds),
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
            'value' => new Value([$contentId]),
        ]));

        TestCase::assertSame($parameters, ['available' => [$contentId => false]]);
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
            'value' => new Value([$contentId]),
        ]));

        TestCase::assertSame($parameters, ['available' => [$contentId => false]]);
    }
}
