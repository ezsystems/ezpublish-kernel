<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests\NamedReferences;

use eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesProvider;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

final class NamedReferencesProviderTest extends TestCase
{
    private const TREE_ROOT_REFERENCE = 2;

    private const CONFIGURED_REFERENCES = [
        'images' => 'remote_id("IMAGES")',
        'videos' => 'remote_id("VIDEOS")',
        'other' => 'remote_id("OTHER")',
    ];

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesProvider */
    private $namedReferenceProvider;

    protected function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->namedReferenceProvider = new NamedReferencesProvider($this->configResolver);
    }

    public function testGetNamedReferences(): void
    {
        $this->configResolver
            ->method('getParameter')
            ->willReturnMap([
                ['location_references', null, null, self::CONFIGURED_REFERENCES],
                ['content.tree_root.location_id', null, null, self::TREE_ROOT_REFERENCE],
            ]);

        $references = $this->namedReferenceProvider->getNamedReferences();

        $this->assertCount(count(self::CONFIGURED_REFERENCES) + 1, $references);
        foreach ($references as $name => $reference) {
            $this->assertTrue($name === '__root' || isset(self::CONFIGURED_REFERENCES[$name]));
        }
    }
}
