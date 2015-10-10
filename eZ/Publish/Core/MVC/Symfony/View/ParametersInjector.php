<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * Collects parameters that will be injected into View objects.
 */
interface ParametersInjector
{
    public function injectViewParameters(View $view, array $parameters);
}
