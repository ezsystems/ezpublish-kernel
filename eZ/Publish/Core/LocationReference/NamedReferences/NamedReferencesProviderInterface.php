<?php

namespace eZ\Publish\Core\LocationReference\NamedReferences;

interface NamedReferencesProviderInterface
{
    public function getNamedReferences(): NamedReferencesCollection;
}
