<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcardUpdateStruct;
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
    public function create(string $sourceUrl, string $destinationUrl, bool $forward = false): UrlWildcard
    {
        if (false === $this->permissionResolver->hasAccess('content', 'urltranslator')) {
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

        $this->validateUrls($sourceUrl, $destinationUrl);

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

    public function update(
        URLWildcard $urlWildcard,
        URLWildcardUpdateStruct $updateStruct
    ): void {
        if (false === $this->permissionResolver->canUser('content', 'urltranslator', $urlWildcard)) {
            throw new UnauthorizedException('content', 'urltranslator');
        }

        $destinationUrl = $updateStruct->destinationUrl;
        $sourceUrl = $updateStruct->sourceUrl;

        $this->validateUrls($sourceUrl, $destinationUrl);

        $this->repository->beginTransaction();

        try {
            $this->urlWildcardHandler->update(
                $urlWildcard->id,
                $sourceUrl,
                $destinationUrl,
                $updateStruct->forward
            );

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard the url wildcard to remove
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function remove(URLWildcard $urlWildcard): void
    {
        if (false === $this->permissionResolver->canUser('content', 'urltranslator', $urlWildcard)) {
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
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\UrlWildcard
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function load(int $id): UrlWildcard
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
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function loadAll(int $offset = 0, int $limit = -1): iterable
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
     * @param string $url
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult
     */
    public function translate(string $url): URLWildcardTranslationResult
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

    /**
     * @param string $sourceUrl
     * @param string $destinationUrl
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\ContentValidationException
     */
    private function validateUrls(string $sourceUrl, string $destinationUrl): void
    {
        preg_match_all('(\\*)', $sourceUrl, $patterns);
        preg_match_all('({(\d+)})', $destinationUrl, $placeholders);

        if (empty($patterns) || empty($placeholders)) {
            throw new ContentValidationException('Invalid URL wildcards provided.');
        }

        $patterns = array_map('intval', $patterns[0]);
        $placeholders = array_map('intval', $placeholders[1]);

        if (!empty($placeholders) && max($placeholders) > count($patterns)) {
            throw new ContentValidationException('Placeholders do not match the wildcards.');
        }
    }
}
