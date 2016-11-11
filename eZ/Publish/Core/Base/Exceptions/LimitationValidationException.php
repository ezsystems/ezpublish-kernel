<?php

/**
 * File containing the eZ\Publish\Core\Base\Exceptions\LimitationValidationException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\API\Repository\Exceptions\LimitationValidationException as APILimitationValidationException;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\Base\TranslatableBase;

/**
 * This Exception is thrown on create, update or assign policy or role
 * when one or more given limitations are not valid.
 */
class LimitationValidationException extends APILimitationValidationException implements Translatable
{
    use TranslatableBase;

    /**
     * Contains an array of limitation ValidationError objects.
     *
     * @var \eZ\Publish\Core\FieldType\ValidationError[]
     */
    protected $errors;

    /**
     * Generates: Limitations did not validate.
     *
     * Also sets the given $errors to the internal property, retrievable by getValidationErrors()
     *
     * @param \eZ\Publish\Core\FieldType\ValidationError[] $errors
     */
    public function __construct(array $errors)
    {
        $this->validationErrors = $errors;
        $this->setMessageTemplate('Limitations did not validate');
        parent::__construct($this->getBaseTranslation());
    }

    /**
     * Returns an array of limitation ValidationError objects.
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[]
     */
    public function getLimitationErrors()
    {
        return $this->errors;
    }
}
