<?php
namespace eZ\Publish\SPI\FieldType;
/**
 */
class FieldValue extends ValueObject
{
    /**
     * data which is stored in the field of the content version
     * 
     * Its up to the storage engine how to encode the hash to serialized form (JSon, Xml etc.)
     * 
     * @var array
     */
    public $data;

    /**
     * data which is stored external by the field type. 
     * 
     * Here its up to the field type
     * to define the format of this value as the field type is responsible for storing 
     * or retieving data externally if the method storeFieldData or getFieldData is called
     * @var mixed
     */
    public $externalData;

    /**
     * Mixed sort key. 
     * 
     * This value is used by the storage engine to sort objects if requested by a sort clause
     *
     * @var mixed
     */
    public $sortKey;
}
