<?php

/**
 * File containing the UrlWildcard Handler.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as BaseUrlWildcardHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * The UrlWildcard Handler provides nice urls with wildcards management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
class Handler implements BaseUrlWildcardHandler
{
    private const PLACEHOLDERS_REGEXP = '(\{(\d+)\})';

    /**
     * UrlWildcard Gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway
     */
    protected $gateway;

    /**
     * UrlWildcard Mapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper
     */
    protected $mapper;

    /**
     * Creates a new UrlWildcard Handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * Creates a new url wildcard.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     * @param bool $forward
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function create($sourceUrl, $destinationUrl, $forward = false)
    {
        $urlWildcard = $this->mapper->createUrlWildcard(
            $sourceUrl,
            $destinationUrl,
            $forward
        );

        $urlWildcard->id = $this->gateway->insertUrlWildcard($urlWildcard);

        return $urlWildcard;
    }

    /**
     * removes an url wildcard.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     */
    public function remove($id)
    {
        $this->gateway->deleteUrlWildcard($id);
    }

    /**
     * Loads a url wild card.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function load($id)
    {
        $row = $this->gateway->loadUrlWildcardData($id);

        if (empty($row)) {
            throw new NotFoundException('UrlWildcard', $id);
        }

        return $this->mapper->extractUrlWildcardFromRow($row);
    }

    /**
     * Loads all url wild card (paged).
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard[]
     */
    public function loadAll($offset = 0, $limit = -1)
    {
        return $this->mapper->extractUrlWildcardsFromRows(
            $this->gateway->loadUrlWildcardsData($offset, $limit)
        );
    }

    /**
     * Performs lookup for given URL.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the url wild card was not found
     *
     * @param string $sourceUrl
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard
     */
    public function translate(string $sourceUrl): UrlWildcard
    {
        $row = $this->gateway->loadUrlWildcardBySourceUrl($sourceUrl);

        if (!empty($row)) {
            return $this->mapper->extractUrlWildcardFromRow($row);
        }

        // can't find UrlWildcard by simple lookup, continue and try to translate

        $rows = $this->gateway->loadUrlWildcardsData();
        uasort($rows,
            static function ($row1, $row2) {
                return strlen($row2['source_url']) - strlen($row1['source_url']);
            }
        );

        foreach ($rows as $row) {
            if ($uri = $this->match($sourceUrl, $row)) {
                $row['destination_url'] = $uri;

                return $this->mapper->extractUrlWildcardFromRow($row);
            }
        }

        throw new NotFoundException('URLWildcard', $sourceUrl);
    }

    /**
     * Checks whether UrlWildcard with given source url exits.
     *
     * @param string $sourceUrl
     *
     * @return bool
     */
    public function exactSourceUrlExists(string $sourceUrl): bool
    {
        $row = $this->gateway->loadUrlWildcardBySourceUrl($sourceUrl);

        return !empty($row);
    }

    /**
     * Tests if the given url matches against the given url wildcard.
     *
     * if the wildcard matches on the given url this method will return a ready
     * to use destination url, otherwise this method will return <b>NULL</b>.
     *
     * @param string $url
     * @param array $wildcard
     *
     * @return string|null
     */
    private function match(string $url, array $wildcard): ?string
    {
        if (preg_match($this->compile($wildcard['source_url']), $url, $match)) {
            return $this->substitute($wildcard['destination_url'], $match);
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
    private function compile(string $sourceUrl): string
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
    private function substitute(string $destinationUrl, array $values): string
    {
        preg_match_all(self::PLACEHOLDERS_REGEXP, $destinationUrl, $matches);

        foreach ($matches[1] as $match) {
            $destinationUrl = str_replace("{{$match}}", $values[$match], $destinationUrl);
        }

        return $destinationUrl;
    }
}
