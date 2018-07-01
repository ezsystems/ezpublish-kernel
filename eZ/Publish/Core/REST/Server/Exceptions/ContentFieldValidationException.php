<?php

/**
 * File containing the ContentFieldValidationException tests.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Exceptions;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException as APIContentFieldValidationException;

/**
 * Exception thrown if one or more content fields did not validate.
 */
class ContentFieldValidationException extends BadRequestException
{
    /**
     * Contains an array of field ValidationError objects indexed with FieldDefinition id and language code.
     * @see eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException
     *
     * @var \eZ\Publish\Core\FieldType\ValidationError[]
     */
    protected $errors;

    public function __construct(APIContentFieldValidationException $e)
    {
        $this->errors = $e->getFieldErrors();

        parent::__construct($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * Returns an array of field validation error messages.
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[]
     */
    public function getFieldErrors()
    {
        return $this->errors;
    }
}
