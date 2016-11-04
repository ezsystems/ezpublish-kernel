<?php

/**
 * Contains MissingClass Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use Exception;
use eZ\Publish\Core\Base\Translatable;
use eZ\Publish\Core\Base\TranslatableBase;
use LogicException;

/**
 * MissingClass Exception implementation.
 *
 * Use:
 *   throw new MissingClass( $className, 'field type' );
 *
 * @todo Add a exception type in API that uses Logic exception and change this to extend it
 */
class MissingClass extends LogicException implements Translatable
{
    use TranslatableBase;

    /**
     * Generates: Could not find[ {$classType}] class '{$className}'.
     *
     * @param string $className
     * @param string|null $classType Optional string to specify what kind of class this is
     * @param \Exception|null $previous
     */
    public function __construct($className, $classType = null, Exception $previous = null)
    {
        $this->setParameters(['%className%' => $className]);
        if ($classType === null) {
            $this->setMessageTemplate("Could not find class '%className%'");
        } else {
            $this->setMessageTemplate("Could not find %classType% class '%className%'");
            $this->addParameter('%classType%', $classType);
        }

        parent::__construct($this->getBaseTranslation(), 0, $previous);
    }
}
