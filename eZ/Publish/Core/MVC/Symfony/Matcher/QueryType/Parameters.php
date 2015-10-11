<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\QueryType;

use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\QueryType\MultipleValued;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class Parameters implements MatcherInterface
{
    private $parameters;


    public function match(View $view)
    {
        if (!$view instanceof QueryTypeView) {
            return false;
        }

        $queryParameters = $view->getQueryParameters();
        foreach ($this->parameters as $parameterName => $parameterValue) {
            if (!isset($queryParameters[$parameterName]) || $parameterValue != $queryParameters[$parameterName]) {
                return false;
            }
        }
        return true;
    }

    public function setMatchingConfig($matchingConfig)
    {
        $this->parameters = $matchingConfig;
    }
}
