<?php

/**
 * File containing the eZ\Publish\SPI\FieldType\Exceptions\InvalidIndexDataException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\FieldType\Exceptions;

use Exception;

/**
 * This Exception is thrown if data given to the indexer are invalid.
 */
class InvalidIndexDataException extends Exception
{
    /**
     * @var mixed
     */
    protected $fieldId;

    /**
     * @var int|null
     */
    protected $versionNo;

    /**
     * @var string
     */
    protected $languageCode;

    public function __construct($fieldId, $versionNo, $languageCode, Exception $previous = null)
    {
        $this->fieldId = $fieldId;
        $this->versionNo = $versionNo;
        $this->languageCode = $languageCode;

        $message = sprintf('Field %d in the version %d for %s language can not be indexed', $this->fieldId, $this->versionNo, $this->languageCode);
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return mixed
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * @return int|null
     */
    public function getVersionNo()
    {
        return $this->versionNo;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }
}
