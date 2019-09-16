<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests;

use eZ\Publish\Core\LocationReference\ExpressionLanguage\ExpressionLanguage;
use eZ\Publish\Core\LocationReference\LimitedLocationService;
use eZ\Publish\Core\LocationReference\LocationReferenceResolver;
use eZ\Publish\Core\LocationReference\Tests\Stubs\NamedReferencesProviderStub;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Location;

final class LocationReferenceResolverTest extends BaseTest
{
    private const ROOT_LOCATION_ID = 2;
    private const EXAMPLE_REMOTE_ID = 'example';

    /** @var \eZ\Publish\Core\LocationReference\LocationReferenceResolverInterface */
    private $locationReferenceResolver;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $exampleLocation;

    protected function setUp(): void
    {
        $this->exampleLocation = $this->createExampleLocation();

        $this->locationReferenceResolver = new LocationReferenceResolver(
            new LimitedLocationService($this->getRepository()->getLocationService()),
            new NamedReferencesProviderStub([
                '__root' => 'local_id(2)',
                'example' => sprintf('remote_id("%s")', self::EXAMPLE_REMOTE_ID),
            ]),
            new ExpressionLanguage()
        );
    }

    public function testResolveLocalId(): void
    {
        $this->assertLocationReference(
            sprintf('local_id(%d)', $this->exampleLocation->id),
            $this->exampleLocation->id
        );
    }

    public function testResolveRemoteId(): void
    {
        $this->assertLocationReference(
            sprintf('remote_id("%s")', $this->exampleLocation->remoteId),
            $this->exampleLocation->id
        );
    }

    public function testResolvePath(): void
    {
        $this->assertLocationReference(
            sprintf('path("%s")', $this->exampleLocation->pathString),
            $this->exampleLocation->id
        );
    }

    public function testResolveRoot(): void
    {
        $this->assertLocationReference('root()', self::ROOT_LOCATION_ID);
    }

    public function testResolveParent(): void
    {
        $this->assertLocationReference(
            sprintf('parent(local_id(%d))', $this->exampleLocation->id),
            $this->exampleLocation->parentLocationId
        );
    }

    public function testResolveNamed(): void
    {
        $this->assertLocationReference('named("example")', $this->exampleLocation->id);
    }

    private function assertLocationReference(string $reference, ?int $expectedLocationId): void
    {
        $expectedLocation = $this->getRepository()->getLocationService()->loadLocation(
            $expectedLocationId
        );

        $actualLocation = $this->locationReferenceResolver->resolve($reference);

        $this->assertEquals($expectedLocation, $actualLocation);
    }

    private function createExampleLocation(): Location
    {
        $locationService = $this->getRepository()->getLocationService();
        $contentService = $this->getRepository()->getContentService();

        $contentType = $this->getRepository()
            ->getContentTypeService()
            ->loadContentTypeByIdentifier('folder');

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField('name', 'Example folder');

        $locationCreateStruct = $locationService->newLocationCreateStruct(self::ROOT_LOCATION_ID);
        $locationCreateStruct->remoteId = self::EXAMPLE_REMOTE_ID;

        $content = $contentService->publishVersion(
            $contentService->createContent($contentCreateStruct, [$locationCreateStruct]
        )->getVersionInfo());

        return $locationService->loadLocation(
            $content->contentInfo->mainLocationId
        );
    }
}
