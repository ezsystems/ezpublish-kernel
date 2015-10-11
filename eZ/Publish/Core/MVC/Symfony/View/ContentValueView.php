<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * A view that contains a Content.
 */
interface ContentValueView
{
    /**
     * Returns the Content.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function getContent();
}
