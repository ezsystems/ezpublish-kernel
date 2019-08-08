<?php

/**
 * File containing the eZ\Publish\Core\Repository\URLWildcardService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard as SPIUrlWildcard;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use Exception;

/**
 * URLAlias service.
 *
 * @example Examples/urlalias.php
 */
class URLWildcardService implements URLWildcardServiceInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler */
    protected $urlWildcardHandler;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var array */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler $urlWildcardHandler
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $urlWildcardHandler,
        PermissionResolver $permissionResolver,
        array $settings = []
    ) {
        $this->repository = $repository;
        $this->urlWildcardHandler = $urlWildcardHandler;
        $this->permissionResolver = $permissionResolver;
        $this->settings = $settings;
    }

    /**
     * Creates a new url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the $sourceUrl pattern already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create url wildcards
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if the number of "*" patterns in $sourceUrl and
     *         the numbers in {\d} placeholders in $destinationUrl does not match.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param bool $forward
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function create($sourceUrl, $destinationUrl, $forward = false): URLWildcard
    {
        if ($this->permissionResolver->hasAccess('content', 'urltranslator') === false) {
            throw new UnauthorizedException('content', 'urltranslator');
        }

        $sourceUrl = $this->cleanUrl($sourceUrl);
        $destinationUrl = $this->cleanUrl($destinationUrl);

        if ($this->urlWildcardHandler->exactSourceUrlExists($this->cleanPath($sourceUrl))) {
            throw new InvalidArgumentException(
                '$sourceUrl',
                'Pattern already exists'
            );
        }

        preg_match_all('(\\*)', $sourceUrl, $patterns);
        preg_match_all('(\{(\d+)\})', $destinationUrl, $placeholders);

        $patterns = array_map('intval', $patterns[0]);
        $placeholders = array_map('intval', $placeholders[1]);

        if (!empty($placeholders) && max($placeholders) > count($patterns)) {
            throw new ContentValidationException('Placeholders are not matching with wildcards.');
        }

        $this->repository->beginTransaction();
        try {
            $spiUrlWildcard = $this->urlWildcardHandler->create(
                $sourceUrl,
                $destinationUrl,
                $forward
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildUrlWildcardDomainObject($spiUrlWildcard);
    }

    /**
     * Removes an url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard the url wildcard to remove
     */
    public function remove(URLWildcard $urlWildcard): void
    {
        if (!$this->permissionResolver->canUser('content', 'urltranslator', $urlWildcard)) {
            throw new UnauthorizedException('content', 'urltranslator');
        }

        $this->repository->beginTransaction();
        try {
            $this->urlWildcardHandler->remove(
                $urlWildcard->id
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Loads a url wild card.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     */
    public function load($id): URLWildcard
    {
        return $this->buildUrlWildcardDomainObject(
            $this->urlWildcardHandler->load($id)
        );
    }

    /**
     * Loads all url wild card (paged).
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard[]
     */
    public function loadAll($offset = 0, $limit = -1): array
    {
        $spiUrlWildcards = $this->urlWildcardHandler->loadAll(
            $offset,
            $limit
        );

        $urlWildcards = [];
        foreach ($spiUrlWildcards as $spiUrlWildcard) {
            $urlWildcards[] = $this->buildUrlWildcardDomainObject($spiUrlWildcard);
        }

        return $urlWildcards;
    }

    /**
     * Translates an url to an existing uri resource based on the
     * source/destination patterns of the url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url could not be translated
     *
     * @param mixed $url
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult
     */
    public function translate($url): URLWildcardTranslationResult
    {
        $spiWildcard = $this->urlWildcardHandler->translate($this->cleanPath($url));

        return new URLWildcardTranslationResult(
            [
                'uri' => $spiWildcard->destinationUrl,
                'forward' => $spiWildcard->forward,
            ]
        );
    }

    /**
     * Builds API UrlWildcard object from given SPI UrlWildcard object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $wildcard
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     */
    private function buildUrlWildcardDomainObject(SPIUrlWildcard $wildcard): URLWildcard
    {
        return new URLWildcard(
            [
                'id' => $wildcard->id,
                'destinationUrl' => $wildcard->destinationUrl,
                'sourceUrl' => $wildcard->sourceUrl,
                'forward' => $wildcard->forward,
            ]
        );
    }

    /**
     * Removes leading and trailing slashes and spaces.
     *
     * @param string $url
     *
     * @return string
     */
    private function cleanUrl(string $url): string
    {
        return '/' . trim($url, '/ ');
    }

    /**
     * Removes leading slash from given path.
     *
     * @param string $path
     *
     * @return string
     */
    private function cleanPath(string $path): string
    {
        return trim($path, '/ ');
    }
}
