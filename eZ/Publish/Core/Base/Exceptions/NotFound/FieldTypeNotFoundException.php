<?php

/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions\NotFound;

use eZ\Publish\Core\Base\Exceptions\Httpable;
use Exception;
use eZ\Publish\Core\Base\TranslatableBase;
use eZ\Publish\Core\Base\Translatable;
use RuntimeException;

/**
 * FieldType Not Found Exception.
 */
class FieldTypeNotFoundException extends RuntimeException implements Httpable, Translatable
{
    use TranslatableBase;

    /**
     * Creates a FieldType Not Found exception with info on how to fix.
     *
     * @param string $fieldType
     * @param \Exception|null $previous
     */
    public function __construct($fieldType, Exception $previous = null)
    {
        $this->setMessageTemplate(
            "FieldType '%fieldType%' not found, needs to be implemented or configured to use FieldType\\Null\\Type (%ezpublish.fieldType.eznull.class%)"
        );
        $this->setParameters(['%fieldType%' => $fieldType]);

        parent::__construct($this->getBaseTranslation(), self::INTERNAL_ERROR, $previous);
    }
}
