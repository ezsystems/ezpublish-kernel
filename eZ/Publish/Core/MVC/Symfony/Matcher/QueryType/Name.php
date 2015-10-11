<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\QueryType;

use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\QueryType\MultipleValued;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class Name extends MultipleValued implements MatcherInterface
{
    public function match(View $view)
    {
        if (!$view instanceof QueryTypeView) {
            return false;
        }

        return isset($this->values[$view->getQueryTypeName()]);
    }
}
