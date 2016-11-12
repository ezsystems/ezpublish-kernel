<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * A view that contains relation data.
 */
interface RelationView
{
    /**
     * Returns the Relations for the current entity being viewed.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function getRelations();
}
