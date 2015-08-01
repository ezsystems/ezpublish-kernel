<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Renderer;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\ViewRenderer;
use InvalidArgumentException;

/**
 * Dispatches the call to render() to a Renderer that can render the given Value, if any.
 */
class Dispatcher implements ViewRenderer
{
    /** @var array|\eZ\Publish\Core\MVC\Symfony\View\ViewRenderer[] Array of ViewRenderer, indexed by rendered type */
    private $renderers;

    /**
     * @param ViewRenderer[] $renderers
     */
    public function __construct($renderers = [])
    {
        $this->renderers = $renderers;
    }

    public function render($value, $viewType = self::VIEW_TYPE_FULL, $params = [])
    {
        foreach ($this->renderers as $type => $renderer) {
            if ($renderer->canRender($value)) {
                return $renderer->render($value, $viewType, $params);
            }
        }

        throw new InvalidArgumentException("No ViewRenderer found for " . get_class($value));
    }

    /**
     * Tests if the ViewRenderer can render $value.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $value
     *
     * @return bool true if the ViewRenderer can render $value
     */
    public function canRender(ValueObject $value)
    {
        foreach ($this->renderers as $type => $renderer) {
            if ($renderer->canRender($value)) {
                return true;
            }
        }

        return false;
    }
}
