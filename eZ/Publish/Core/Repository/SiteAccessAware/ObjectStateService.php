<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\Core\Repository\Decorator\ObjectStateServiceDecorator;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;

/**
 * SiteAccess aware implementation of ObjectStateService injecting languages where needed.
 */
class ObjectStateService extends ObjectStateServiceDecorator
{
    /** @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\ObjectStateService $service
     * @param \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver $languageResolver
     */
    public function __construct(
        ObjectStateServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        parent::__construct($service);

        $this->languageResolver = $languageResolver;
    }

    public function loadObjectStateGroup($objectStateGroupId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadObjectStateGroup($objectStateGroupId, $prioritizedLanguages);
    }

    public function loadObjectStateGroups($offset = 0, $limit = -1, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadObjectStateGroups($offset, $limit, $prioritizedLanguages);
    }

    public function loadObjectStates(ObjectStateGroup $objectStateGroup, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadObjectStates($objectStateGroup, $prioritizedLanguages);
    }

    public function loadObjectState($stateId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadObjectState($stateId, $prioritizedLanguages);
    }
}
