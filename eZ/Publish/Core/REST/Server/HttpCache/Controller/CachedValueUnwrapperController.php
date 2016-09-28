<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\HttpCache\Controller;

use eZ\Publish\Core\REST\Server\Values\CachedValue;

/**
 * A proxy controller that unwraps value objects from CachedValue objects.
 *
 * Used to deprecate returning a CachedValue from a controller directly,
 * and enforce usage of an HttpCache proxy controller.
 */
class CachedValueUnwrapperController
{
    private $innerController;

    public function __construct($innerController)
    {
        $this->innerController = $innerController;
    }

    public function __call($method, $arguments)
    {
        $value = call_user_func_array([$this->innerController, $method], $arguments);

        if ($value instanceof CachedValue) {
            @trigger_error(
                'Returning CachedValue objects from REST controllers is deprecated',
                E_USER_DEPRECATED
            );

            return $value->value;
        } else {
            return $value;
        }
    }
}
