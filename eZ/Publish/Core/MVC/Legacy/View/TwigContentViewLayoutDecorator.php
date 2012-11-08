<?php
/**
 * File containing the TwigLayoutDecorator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\View;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface,
    eZ\Publish\Core\MVC\Symfony\View\ContentView,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    Twig_Environment;

class TwigContentViewLayoutDecorator implements ContentViewInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    protected $contentView;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $options;

    public function __construct( Twig_Environment $twig, array $options )
    {
        $this->twig = $twig;
        $this->options = $options + array( 'contentBlockName' => 'content' );
    }

    /**
     * Injects the content view object to decorate.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $contentView
     */
    public function setContentView( ContentView $contentView )
    {
        $this->contentView = $contentView;
    }

    /**
     * Sets $templateIdentifier to the content view.
     * This decorator only supports closures.
     *
     * Must throw a \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType exception if $templateIdentifier is invalid.
     *
     * @param \Closure $templateIdentifier
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function setTemplateIdentifier( $templateIdentifier )
    {
        if ( !$templateIdentifier instanceof \Closure )
            throw new InvalidArgumentType( 'templateIdentifier', '\\Closure', $templateIdentifier );

        $this->contentView->setTemplateIdentifier( $templateIdentifier );
    }

    /**
     * Returns the registered template identifier.
     *
     * @return \Closure
     * @throws \RuntimeException
     */
    public function getTemplateIdentifier()
    {
        $options = $this->options;
        $contentView = $this->contentView;
        $twig = $this->twig;

        return function ( array $params ) use ( $options, $contentView, $twig )
        {
            $contentViewClosure = $contentView->getTemplateIdentifier();
            $layout = $options['layout'];
            if ( isset( $params['noLayout'] ) && $params['noLayout'] )
            {
                $layout = $options['viewbaseLayout'];
            }
            $twigContentTemplate = <<<EOT
{% extends "{$layout}" %}

{% block {$options['contentBlockName']} %}
{{ viewResult|raw }}
{% endblock %}
EOT;
            return $twig->render(
                $twigContentTemplate,
                array(
                     'viewResult' => $contentViewClosure( $params )
                )
            );
        };
    }

    /**
     * Sets $parameters that will later be injected to the template/closure.
     * If some parameters were already present, $parameters will replace them.
     *
     * @param array $parameters Hash of parameters
     */
    public function setParameters( array $parameters )
    {
        $this->contentView->setParameters( $parameters );
    }

    /**
     * Adds a hash of parameters to the existing parameters.
     *
     * @param array $parameters
     */
    public function addParameters( array $parameters )
    {
        $this->contentView->addParameters( $parameters );
    }

    /**
     * Returns registered parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->contentView->getParameters();
    }

    /**
     * Checks if $parameterName exists.
     *
     * @param string $parameterName
     * @return bool
     */
    public function hasParameter( $parameterName )
    {
        return $this->contentView->hasParameter( $parameterName );
    }

    /**
     * Returns parameter value by $parameterName.
     * Throws an \InvalidArgumentException if $parameterName is not set.
     *
     * @param string $parameterName
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getParameter( $parameterName )
    {
        return $this->contentView->getParameter( $parameterName );
    }
}
