<?php

namespace eZ\Publish\Core\FieldType\Price\PriceStorage\Gateway;

use eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZContentClassAttribute;
use eZContentObjectAttribute;
use eZPrice;

class LegacyStorage extends Gateway
{
    /**
     * The legacy kernel instance (eZ Publish 4)
     *
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    public function __construct( \Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;
        return $legacyKernelClosure();
    }

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof DatabaseHandler )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new \RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * Stores the keyword list from $field->value->externalData
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field
     * @param mixed $contentTypeId
     */
    public function storeFieldData( Field $field, $contentTypeId )
    {
    }

    /**
     * Sets the list of assigned keywords into $field->value->externalData
     *
     * @param Field $field
     *
     * @return void
     */
    public function getFieldData( Field $field )
    {
        $field->value->externalData = $this->getLegacyKernel()->runCallback(
            function () use ( $field )
            {
                $classAttribute = eZContentClassAttribute::fetch( $field->fieldDefinitionId );
                $contentObjectAttribute = eZContentObjectAttribute::fetch( $field->id, $field->versionNo );
                $storedPrice = $contentObjectAttribute->attribute( "data_float" );
                $price = new eZPrice( $classAttribute, $contentObjectAttribute, $storedPrice );

                return array(
                    'price' => $storedPrice,
                    'currency' => $price->currency(),
                    'selectedVatType' => $price->VATType(),
                    'vatType' => $price->VATType()->VATTypeList(),
                    'vatPercent' => $price->VATPercent(),
                    'isVatIncluded' => $price->VATIncluded(),
                    'incVatPrice' => $price->incVATPrice(),
                    'exVatPrice' => $price->exVATPrice(),
                    'discountPercent' => $price->discountPercent(),
                    'discountPriceIncVat' => $price->discountIncVATPrice(),
                    'discountPriceExVat' => $price->discountExVATPrice(),
                    'hasDiscount' => $price->hasDiscount(),
                    'currentUser' => \eZUser::currentUser()
                );
            }
        );
    }

    /**
     * Retrieve the ContentType ID for the given $field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return mixed
     */
    public function getContentTypeId( Field $field )
    {
    }

    /**
     * Stores the keyword list from $field->value->externalData
     *
     * @param mixed $fieldId
     */
    public function deleteFieldData( $fieldId )
    {
    }
}
