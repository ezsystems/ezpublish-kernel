<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * Configures a View object.
 *
 * Example: set the template, add extra parameters.
 */
interface Configurator
{
    public function configure(View $view);
}
