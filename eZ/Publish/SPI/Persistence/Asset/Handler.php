<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Asset\Handler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Asset;

/**
 * the asset handler manages assets and variant storage
 *
 * @package eZ\Publish\SPI\Persistence\Asset
 */
interface Handler
{
    /**
     * creates an asset for the given content id
     *
     * @param $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Asset
     */
    public function createAsset( $contentId );

    /**
     * adds a variant to the asset
     *
     * @param int|string $assetId
     * @param \eZ\Publish\SPI\Persistence\Asset\Variant\CreateStruct $variantCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Asset\Variant
     */
    public function addVariant( $assetId, $variantCreateStruct );

    /**
     * updates a variant of an asset
     *
     * @param int|string $variantId
     * @param \eZ\Publish\SPI\Persistence\Asset\Variant\UpdateStruct $variantUpdateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Asset\Variant
     */
    public function updateVariant( $variantId, $variantUpdateStruct );

    /**
     * removes the variant from the asset
     *
     * @param $variantId
     */
    public function removeVariant( $variantId );

    /**
     * loads an asset for the given id
     *
     * @param $assetId
     *
     * @return \eZ\Publish\SPI\Persistence\Asset
     */
    public function loadAsset( $assetId );

    /**
     * deletes the asset
     *
     * @param $assetId
     */
    public function deleteAsset( $assetId );
}
