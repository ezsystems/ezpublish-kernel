<?php
/**
 * File containing the eZ\Publish\API\Repository\AssetService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

/**
 * This service provides methods to handle assets.
 *
 * @package eZ\Publish\API\Repository
 */
interface AssetService
{
    /**
     * Creates an asset using $assetCreateStruct.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an asset
     *
     * @param \eZ\Publish\API\Repository\Values\Asset\AssetCreateStruct $assetCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Asset\Asset
     *
     */
    public function createAsset( AssetCreateStruct $assetCreateStruct );

    /**
     * Loads the asset with the given $assetId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the asset with the given id does not exist
     *
     * @param int|string $assetId
     *
     * @return \eZ\Publish\API\Repository\Values\Asset\Asset
     *
     */
    public function loadAsset( $assetId );

    /**
     * Updates $asset using $assetUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an asset
     *
     * @param \eZ\Publish\API\Repository\Values\Asset\Asset $asset
     * @param \eZ\Publish\API\Repository\Values\Asset\AssetUpdateStruct $assetUpdateStruct
     *
     */
    public function updateAsset( Asset $asset, AssetUpdateStruct $assetUpdateStruct );

    /**
     * Returns the variant with $variantIdentifier for the give $asset.
     *
     * if the variant does not exist it is generated
     *
     * @param \eZ\Publish\API\Repository\Values\Asset\Asset $asset
     * @param string $variantIdentifier
     * @return \eZ\Publish\API\Repository\Values\Asset\Variant
     */
    public function getVariant( Asset $asset, $variantIdentifier );

    /**
     * returns the asset type of the given asset
     *
     * @param \eZ\Publish\API\Repository\Values\Asset\Asset $asset
     *
     * @return \eZ\Publish\API\Repository\Values\Asset\AssetType
     */
    public function getAssetType( Asset $asset );

    /**
     * @return \eZ\Publish\API\Repository\Values\Asset\AssetCreateStruct
     */
    public function newAssetCreateStruct();

    /**
     * @return \eZ\Publish\API\Repository\Values\Asset\AssetCreateStruct
     */
    public function newAssetUpdateStruct();
}
