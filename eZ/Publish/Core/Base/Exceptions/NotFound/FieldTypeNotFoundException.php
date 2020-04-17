<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions\NotFound;

use eZ\Publish\Core\Base\Exceptions\Httpable;
use Exception;
use eZ\Publish\Core\Base\TranslatableBase;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\FieldType\Null\Type as NullType;
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
            "Field Type '%fieldType%' not found. It must be implemented or configured to use %nullType%"
        );
        $this->setParameters(
            [
                '%fieldType%' => $fieldType,
                '%nullType%' => NullType::class,
            ]
        );

        parent::__construct($this->getBaseTranslation(), self::INTERNAL_ERROR, $previous);
    }
}
