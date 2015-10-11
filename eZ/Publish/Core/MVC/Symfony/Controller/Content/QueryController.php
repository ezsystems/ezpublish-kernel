<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;

class QueryController
{
    public function content(QueryTypeView $view)
    {
        return $view;
    }

    public function location(QueryTypeView $view)
    {
        return $view;
    }

    public function contentInfo(QueryTypeView $view)
    {
        return $view;
    }
}
