<?php

/**
 * File containing the eZ\Publish\Core\Repository\URLWildcardService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard as SPIUrlWildcard;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
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
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    protected $urlWildcardHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler $urlWildcardHandler
     * @param array $settings
     */
    public function __construct(RepositoryInterface $repository, Handler $urlWildcardHandler, array $settings = [])
    {
        $this->repository = $repository;
        $this->urlWildcardHandler = $urlWildcardHandler;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + [
            //'defaultSetting' => array(),
        ];
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
    public function create($sourceUrl, $destinationUrl, $forward = false)
    {
        if ($this->repository->hasAccess('content', 'urltranslator') !== true) {
            throw new UnauthorizedException('content', 'urltranslator');
        }

        $sourceUrl = $this->cleanUrl($sourceUrl);
        $destinationUrl = $this->cleanUrl($destinationUrl);

        $spiUrlWildcards = $this->urlWildcardHandler->loadAll();
        foreach ($spiUrlWildcards as $wildcard) {
            if ($wildcard->sourceUrl === $sourceUrl) {
                throw new InvalidArgumentException(
                    '$sourceUrl',
                    'Pattern already exists'
                );
            }
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
     * Removes leading and trailing slashes and spaces.
     *
     * @param string $url
     *
     * @return string
     */
    protected function cleanUrl($url)
    {
        return '/' . trim($url, '/ ');
    }

    /**
     * removes an url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove url wildcards
     *
     * @param \eZ\Publish\API\Repository\Values\Content\UrlWildcard $urlWildcard the url wildcard to remove
     */
    public function remove(URLWildcard $urlWildcard)
    {
        if (!$this->repository->canUser('content', 'urltranslator', $urlWildcard)) {
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
    public function load($id)
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
    public function loadAll($offset = 0, $limit = -1)
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
    public function translate($url)
    {
        $spiUrlWildcards = $this->urlWildcardHandler->loadAll();

        // sorts wildcards by length of source URL string
        // @todo sort by specificity of the pattern?
        uasort(
            $spiUrlWildcards,
            function (SPIUrlWildcard $w1, SPIUrlWildcard $w2) {
                return strlen($w2->sourceUrl) - strlen($w1->sourceUrl);
            }
        );

        foreach ($spiUrlWildcards as $wildcard) {
            if ($uri = $this->match($url, $wildcard)) {
                return new URLWildcardTranslationResult(
                    [
                        'uri' => $uri,
                        'forward' => $wildcard->forward,
                    ]
                );
            }
        }

        throw new NotFoundException('URLWildcard', $url);
    }

    /**
     * Tests if the given url matches against the given url wildcard.
     *
     * if the wildcard matches on the given url this method will return a ready
     * to use destination url, otherwise this method will return <b>NULL</b>.
     *
     * @param string $url
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $wildcard
     *
     * @return null|string
     */
    private function match($url, SPIUrlWildcard $wildcard)
    {
        if (preg_match($this->compile($wildcard->sourceUrl), $url, $match)) {
            return $this->substitute($wildcard->destinationUrl, $match);
        }

        return null;
    }

    /**
     * Compiles the given url pattern into a regular expression.
     *
     * @param string $sourceUrl
     *
     * @return string
     */
    private function compile($sourceUrl)
    {
        return '(^' . str_replace('\\*', '(.*)', preg_quote($sourceUrl)) . '$)U';
    }

    /**
     * Substitutes all placeholders ({\d}) in the given <b>$destinationUrl</b> with
     * the values from the given <b>$values</b> array.
     *
     * @param string $destinationUrl
     * @param array $values
     *
     * @return string
     */
    private function substitute($destinationUrl, array $values)
    {
        preg_match_all('(\{(\d+)\})', $destinationUrl, $matches);

        foreach ($matches[1] as $match) {
            $destinationUrl = str_replace("{{$match}}", $values[$match], $destinationUrl);
        }

        return $destinationUrl;
    }

    /**
     * Builds API UrlWildcard object from given SPI UrlWildcard object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $wildcard
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     */
    private function buildUrlWildcardDomainObject(SPIUrlWildcard $wildcard)
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
}
