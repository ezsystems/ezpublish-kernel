<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\NamedReferences;

final class InMemoryNamedReferenceProvider implements NamedReferencesProviderInterface
{
    /** @var string[] */
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
