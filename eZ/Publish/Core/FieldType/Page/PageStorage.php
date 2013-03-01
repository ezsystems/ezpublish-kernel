<?php
/**
 * File containing the PageStorage class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

class PageStorage extends GatewayBasedStorage
{
    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService
     */
    protected $pageService;

    public function __construct( array $gateways = array(), PageService $pageService )
    {
        parent::__construct( $gateways );
        $this->pageService = $pageService;
    }

    /**
     * {@inheritDoc}
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        // TODO: Implement storeFieldData() method.
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        if ( !$this->pageService->hasStorageGateway() )
            $this->pageService->setStorageGateway( $this->getGateway( $context ) );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        // TODO: Implement deleteFieldData() method.
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        // TODO: Implement getIndexData() method.
    }
}
