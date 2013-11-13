<?php

namespace eZ\Publish\SPI\URLAlias;

/**
 * Service for lookup of URL aliases.
 */
interface URLLookupService
{
    /**
     * looks up the URLAlias for the given url.
     *
     * @param string $url
     * @param string $languageCode
     * @param string $context
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the path does not exist or is not valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function lookup( $url, $languageCode = null, $context = null );

    /**
     * Returns the URL alias for the given location in the given language.
     *
     * If $languageCode is null the method returns the url alias in the most prioritized language.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if no url alias exist for the given language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $languageCode
     * @param string $context
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function reverseLookup( Location $location, $languageCode = null, $context = null );
}
