<?php
namespace ezp\PublicAPI\FieldTypeProviderInterface;
/**
 */
class FieldValue extends ValueObject
{
    /**
     * data which is stored in the field of the content version
     * @var array
     */
    public $data;

    /**
     * data which is stored external by the field type
     * @var mixed
     */
    public $externalData;

    /**
     * Mixed sort key
     *
     * @var mixed
     */
    public $sortKey;
}
