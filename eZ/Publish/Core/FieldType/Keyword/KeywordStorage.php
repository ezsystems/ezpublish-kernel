<?php
/**
 * File containing the KeywordStorage Converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Keyword;
use eZ\Publish\SPI\FieldType\FieldStorage,
    eZ\Publish\SPI\Persistence\Handler as PersistenceHandler,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    LogicException,
    PDO;

/**
 * Converter for Keyword field type external storage
 *
 * The keyword storage ships a list (array) of keywords in
 * $field->value->externalData. $field->value->data is simply empty, because no
 * internal data is store.
 */
class KeywordStorage implements FieldStorage
{
    /**
     * Gateways
     *
     * @var \eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway[]
     */
    protected $gateways;

    /**
     * SPI Persistence handler
     *
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Construct from gateways
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway[] $gateways
     */
    public function __construct( PersistenceHandler $persistenceHandler, array $gateways )
    {
        $this->persistenceHandler = $persistenceHandler;
        foreach ( $gateways as $identifier => $gateway )
        {
            $this->addGateway( $identifier, $gateway );
        }
    }

    /**
     * Add gateway
     *
     * @param string $identifier
     * @param \eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway $gateway
     * @return void
     */
    public function addGateway( $identifier, KeywordStorage\Gateway $gateway )
    {
        $this->gateways[$identifier] = $gateway;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        if ( empty( $field->value->externalData ) )
        {
            return;
        }

        $contentTypeID = $this->getContentTypeID( $versionInfo );

        $gateway = $this->getGateway( $context );
        return $gateway->storeFieldData( $field, $contentTypeID );
    }

    /**
     * Returns the content type ID for $fieldDefinitionId
     *
     * @param string $typeIdentifier
     * @return mixed
     */
    protected function getContentTypeID( VersionInfo $versionInfo )
    {
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo(
            $versionInfo->contentId
        );
        $contentType = $this->persistenceHandler->contentTypeHandler()->load(
            $contentInfo->contentTypeId
        );
        return $contentType->id;
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $gateway = $this->getGateway( $context );
        // @TODO: This should already retrieve the ContentType ID
        return $gateway->getFieldData( $field );
    }

    /**
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
        // @TODO: What about deleting keywords?
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        return null;
    }

    /**
     * Get gateway for given context
     *
     * @param array $context
     * @return UserStorage\Gateway
     */
    protected function getGateway( array $context )
    {
        if ( !isset( $this->gateways[$context['identifier']] ) )
        {
            throw new \OutOfBoundsException( "No gateway for ${context['identifier']} available." );
        }

        $gateway = $this->gateways[$context['identifier']];
        $gateway->setConnection( $context['connection'] );

        return $gateway;
    }
}
