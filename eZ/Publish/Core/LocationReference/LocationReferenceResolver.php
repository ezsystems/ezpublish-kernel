<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference;

use eZ\Publish\Core\LocationReference\ExpressionLanguage\ExpressionLanguage;
use eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesProviderInterface;
use eZ\Publish\API\Repository\Values\Content\Location;

final class LocationReferenceResolver implements LocationReferenceResolverInterface
{
    /** @var \eZ\Publish\Core\LocationReference\LimitedLocationService */
    private $limitedLocationService;

    /** @var \eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesProviderInterface */
    private $namedReferencesProvider;

    /** @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage */
    private $expressionLanguage;

    public function __construct(
        LimitedLocationService $limitedLocationService,
        NamedReferencesProviderInterface $namedReferencesProvider,
        ExpressionLanguage $expressionLanguage)
    {
        $this->limitedLocationService = $limitedLocationService;
        $this->namedReferencesProvider = $namedReferencesProvider;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function resolve(string $reference): Location
    {
        if ($this->isLocationId($reference)) {
            return $this->limitedLocationService->loadLocation((int)$reference);
        }

        return $this->expressionLanguage->evaluate($reference, [
            '__location_service' => $this->limitedLocationService,
            '__named_references' => $this->namedReferencesProvider->getNamedReferences(),
            '__self' => $this,
        ]);
    }

    private function isLocationId(string $reference): bool
    {
        return ctype_digit($reference);
    }
}
