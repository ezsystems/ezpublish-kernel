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
     * @param \eZ\Publish\API\Repository\Values\Asset\AssetCreateStruct $assetCreateStruct
     * @return \eZ\Publish\API\Repository\Values\Asset\Asset
     *
     * @throws TODO: Define.
     */
    public function createAsset( AssetCreateStruct $assetCreateStruct );

    /**
     * Loads the asset with the given $assetId.
     *
     * @param string $assetId
     * @return \eZ\Publish\API\Repository\Values\Asset\Asset
     *
     * @throws TODO: Define.
     */
    public function loadAsset( $assetId );

    /**
     * Updates $asset using $assetUpdateStruct
     *
     * @param \eZ\Publish\API\Repository\Values\Asset\Asset $asset
     * @param \eZ\Publish\API\Repository\Values\Asset\AssetUpdateStruct $assetUpdateStruct
     *
     * @throws TODO: Define.
     */
    public function updateAsset( Asset $asset, AssetUpdateStruct $assetUpdateStruct );

    /**
     * Returns the given $asset in the variant with $variantIdentifier
     *
     * @param \eZ\Publish\API\Repository\Values\Asset\Asset $asset
     * @param string $variantIdentifier
     * @return \eZ\Publish\API\Repository\Values\Asset\Variant
     */
    public function getVariant( Asset $asset, $variantIdentifier );

    /**
     * @return \eZ\Publish\API\Repository\Asset\AssetCreateStruct
     */
    public function newAssetCreateStruct();

    /**
     * @return \eZ\Publish\API\Repository\Asset\AssetCreateStruct
     */
    public function newAssetUpdateStruct();
}
