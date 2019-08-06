<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use DateTime;
use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion as ContentCriterion;
use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\API\Repository\Values\URL\UsageSearchResult;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\SPI\Persistence\URL\Handler as URLHandler;
use eZ\Publish\SPI\Persistence\URL\URL as SPIUrl;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct as SPIUrlUpdateStruct;

class URLService implements URLServiceInterface
{
    /** @var \eZ\Publish\Core\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\SPI\Persistence\URL\Handler */
    protected $urlHandler;

    /** \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\URL\Handler $urlHandler
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(
        RepositoryInterface $repository,
        URLHandler $urlHandler,
        PermissionResolver $permissionResolver
    ) {
        $this->repository = $repository;
        $this->urlHandler = $urlHandler;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function findUrls(URLQuery $query)
    {
        if ($this->repository->hasAccess('url', 'view') === false) {
            throw new UnauthorizedException('url', 'view');
        }

        if ($query->offset !== null && !is_numeric($query->offset)) {
            throw new InvalidArgumentValue('offset', $query->offset);
        }

        if ($query->limit !== null && !is_numeric($query->limit)) {
            throw new InvalidArgumentValue('limit', $query->limit);
        }

        $results = $this->urlHandler->find($query);

        $items = [];
        foreach ($results['items'] as $url) {
            $items[] = $this->buildDomainObject($url);
        }

        return new SearchResult([
            'totalCount' => $results['count'],
            'items' => $items,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUrl(URL $url, URLUpdateStruct $struct)
    {
        if (!$this->permissionResolver->canUser('url', 'update', $url)) {
            throw new UnauthorizedException('url', 'update');
        }

        if (!$this->isUnique($url->id, $struct->url)) {
            throw new InvalidArgumentException('struct', 'url already exists');
        }

        $updateStruct = $this->buildUpdateStruct($this->loadById($url->id), $struct);

        $this->repository->beginTransaction();
        try {
            $this->urlHandler->updateUrl($url->id, $updateStruct);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadById($url->id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        $url = $this->buildDomainObject(
            $this->urlHandler->loadById($id)
        );

        if (!$this->permissionResolver->canUser('url', 'view', $url)) {
            throw new UnauthorizedException('url', 'view');
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        $apiUrl = $this->buildDomainObject(
            $this->urlHandler->loadByUrl($url)
        );

        if (!$this->permissionResolver->canUser('url', 'view', $apiUrl)) {
            throw new UnauthorizedException('url', 'view');
        }

        return $apiUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function createUpdateStruct()
    {
        return new URLUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages(URL $url, $offset = 0, $limit = -1)
    {
        $contentIds = $this->urlHandler->findUsages($url->id);
        if (empty($contentIds)) {
            return new UsageSearchResult();
        }

        $query = new Query();
        $query->filter = new ContentCriterion\LogicalAnd([
            new ContentCriterion\ContentId($contentIds),
            new ContentCriterion\Visibility(ContentCriterion\Visibility::VISIBLE),
        ]);

        $query->offset = $offset;
        if ($limit > -1) {
            $query->limit = $limit;
        }

        $searchResults = $this->repository->getSearchService()->findContentInfo($query);

        $usageResults = new UsageSearchResult();
        $usageResults->totalCount = $searchResults->totalCount;
        foreach ($searchResults->searchHits as $hit) {
            $usageResults->items[] = $hit->valueObject;
        }

        return $usageResults;
    }

    /**
     * Builds domain object from ValueObject returned by Persistence API.
     *
     * @param \eZ\Publish\SPI\Persistence\URL\URL $data
     *
     * @return \eZ\Publish\API\Repository\Values\URL\URL
     */
    protected function buildDomainObject(SPIUrl $data)
    {
        return new URL([
            'id' => $data->id,
            'url' => $data->url,
            'isValid' => $data->isValid,
            'lastChecked' => $this->createDateTime($data->lastChecked),
            'created' => $this->createDateTime($data->created),
            'modified' => $this->createDateTime($data->modified),
        ]);
    }

    /**
     * Builds SPI update structure used by Persistence API.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL $url
     * @param \eZ\Publish\API\Repository\Values\URL\URLUpdateStruct $data
     *
     * @return \eZ\Publish\SPI\Persistence\URL\URLUpdateStruct
     */
    protected function buildUpdateStruct(URL $url, URLUpdateStruct $data)
    {
        $updateStruct = new SPIUrlUpdateStruct();

        if ($data->url !== null && $url->url !== $data->url) {
            $updateStruct->url = $data->url;
            // Reset URL validity
            $updateStruct->lastChecked = 0;
            $updateStruct->isValid = true;
        } else {
            $updateStruct->url = $url->url;

            if ($data->lastChecked !== null) {
                $updateStruct->lastChecked = $data->lastChecked->getTimestamp();
            } elseif ($data->lastChecked !== null) {
                $updateStruct->lastChecked = $url->lastChecked->getTimestamp();
            } else {
                $updateStruct->lastChecked = 0;
            }

            if ($data->isValid !== null) {
                $updateStruct->isValid = $data->isValid;
            } else {
                $updateStruct->isValid = $url->isValid;
            }
        }

        return $updateStruct;
    }

    /**
     * Check if URL is unique.
     *
     * @param int $id
     * @param string $url
     *
     * @return bool
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function isUnique($id, $url)
    {
        try {
            return $this->loadByUrl($url)->id === $id;
        } catch (NotFoundException $e) {
            return true;
        }
    }

    private function createDateTime($timestamp)
    {
        if ($timestamp > 0) {
            return new DateTime("@{$timestamp}");
        }

        return null;
    }
}
