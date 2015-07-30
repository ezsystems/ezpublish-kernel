<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\ValueObject;
use InvalidArgumentException;

/**
 * Renders a value object to a string representation.
 */
interface ViewRenderer
{
    const VIEW_TYPE_FULL = 'full';
    const VIEW_TYPE_LINE = 'line';

    /**
     * Renders the given $value.
     *
     * @param object $value
     * @param string $viewType
     * @param array $params
     *
     * @return string
     *
     * @throws InvalidArgumentException If $value doesn't match what is expected.
     */
    public function render($value, $viewType = self::VIEW_TYPE_FULL, $params = []);

    /**
     * Tests if the ViewRenderer can render $value.
     *
     * @return bool true if the ViewRenderer can render $value
     */
    public function canRender($value);
}
