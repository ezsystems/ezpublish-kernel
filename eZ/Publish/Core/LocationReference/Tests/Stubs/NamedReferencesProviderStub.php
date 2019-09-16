<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests\Stubs;

use eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesCollection;
use eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesProviderInterface;

final class NamedReferencesProviderStub implements NamedReferencesProviderInterface
{
    private $namedReferences = [];

    public function __construct(array $references = [])
    {
        $this->namedReferences = $references;
    }

    public function getNamedReferences(): NamedReferencesCollection
    {
        return new NamedReferencesCollection($this->namedReferences);
    }
}
