<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Renderer;

use eZ\Publish\Core\MVC\Symfony\View\Renderer;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\Templating\EngineInterface as TemplateEngine;

class TemplateRenderer implements Renderer
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $view
     *
     * @return string
     */
    public function render(View $view)
    {
        return $this->templateEngine->render(
            $view->getTemplateIdentifier(),
            $view->getParameters()
        );
    }
}
