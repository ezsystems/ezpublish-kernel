<?php

/**
 * FieldTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\DomainMapper;
use eZ\Publish\Core\Repository\SiteAccessAware\Helper\LanguageResolver;

/**
 * FieldTypeService class.
 */
class FieldTypeService implements FieldTypeServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $service;

    /**
     * Language resolver
     *
     * @var LanguageResolver
     */
    protected $languageResolver;

    /**
     * Rebuilds existing API domain objects to SiteAccessAware objects
     *
     * @var DomainMapper
     */
    protected $domainMapper;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service
     *
     * @param \eZ\Publish\API\Repository\FieldTypeService $service
     * @param LanguageResolver $languageResolver
     * @param DomainMapper $domainMapper
     */
    public function __construct(
        FieldTypeServiceInterface $service,
        LanguageResolver $languageResolver,
        DomainMapper $domainMapper
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
        $this->domainMapper = $domainMapper;
    }

    /**
     * Returns a list of all field types.
     *
     * @return \eZ\Publish\API\Repository\FieldType[]
     */
    public function getFieldTypes()
    {
        return $this->service->getFieldTypes();
    }

    /**
     * Returns the FieldType registered with the given identifier.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\FieldType
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if there is no FieldType registered with $identifier
     */
    public function getFieldType($identifier)
    {
        return $this->service->getFieldType($identifier);
    }

    /**
     * Returns if there is a FieldType registered under $identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasFieldType($identifier)
    {
        return $this->service->hasFieldType($identifier);
    }
}
