<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Provides a View, if applicable, for a ValueObject
 */
interface ViewProvider
{
    /**
     * Returns a View object corresponding to $valueObject, or null if not applicable.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     * @param string $viewType Variation of display for your value
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View|null
     */
    public function getView(ValueObject $valueObject, $viewType);
}
