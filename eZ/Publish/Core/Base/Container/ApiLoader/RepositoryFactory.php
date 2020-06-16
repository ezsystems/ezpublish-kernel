<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader;

use eZ\Publish\API\Repository\LanguageResolver;
use eZ\Publish\API\Repository\PermissionService;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\Repository\Permission\LimitationService;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperFactoryInterface;
use eZ\Publish\Core\Repository\User\PasswordHashServiceInterface;
use eZ\Publish\Core\Repository\Helper\RelationProcessor;
use eZ\Publish\Core\Repository\Mapper;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;
use eZ\Publish\SPI\Repository\Validator\ContentValidator;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class RepositoryFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var string */
    private $repositoryClass;

    /**
     * Policies map.
     *
     * @var array
     */
    protected $policyMap = [];

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    private $languageResolver;

    public function __construct(
        $repositoryClass,
        array $policyMap,
        LanguageResolver $languageResolver
    ) {
        $this->repositoryClass = $repositoryClass;
        $this->policyMap = $policyMap;
        $this->languageResolver = $languageResolver;
    }

    /**
     * Builds the main repository, heart of eZ Publish API.
     *
     * This always returns the true inner Repository, please depend on ezpublish.api.repository and not this method
     * directly to make sure you get an instance wrapped inside Event / Cache / * functionality.
     *
     * @param string[] $languages
     */
    public function buildRepository(
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor,
        FieldTypeRegistry $fieldTypeRegistry,
        PasswordHashServiceInterface $passwordHashService,
        ThumbnailStrategy $thumbnailStrategy,
        ProxyDomainMapperFactoryInterface $proxyDomainMapperFactory,
        Mapper\ContentDomainMapper $contentDomainMapper,
        Mapper\ContentTypeDomainMapper $contentTypeDomainMapper,
        Mapper\RoleDomainMapper $roleDomainMapper,
        Mapper\ContentMapper $contentMapper,
        ContentValidator $contentValidator,
        LimitationService $limitationService,
        PermissionService $permissionService,
        array $languages
    ): Repository {
        return new $this->repositoryClass(
            $persistenceHandler,
            $searchHandler,
            $backgroundIndexer,
            $relationProcessor,
            $fieldTypeRegistry,
            $passwordHashService,
            $thumbnailStrategy,
            $proxyDomainMapperFactory,
            $contentDomainMapper,
            $contentTypeDomainMapper,
            $roleDomainMapper,
            $contentMapper,
            $contentValidator,
            $limitationService,
            $this->languageResolver,
            $permissionService,
            [
                'role' => [
                    'policyMap' => $this->policyMap,
                ],
                'languages' => $languages,
            ],
        );
    }

    /**
     * Returns a service based on a name string (content => contentService, etc).
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param string $serviceName
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return mixed
     */
    public function buildService(Repository $repository, $serviceName)
    {
        $methodName = 'get' . $serviceName . 'Service';
        if (!method_exists($repository, $methodName)) {
            throw new InvalidArgumentException($serviceName, 'No such service');
        }

        return $repository->$methodName();
    }
}
