<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Event;

use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Dispatched when the parameters injected into a view are collected.
 *
 * Listeners can add, remove and modify parameters using the ParameterBag returned by getParameterBag().
 */
class FilterViewParametersEvent extends Event
{
    /**
     * Copy of the view object that is being built.
     *
     * @var \eZ\Publish\Core\MVC\Symfony\View\View
     */
    private $view;

    /**
     * Parameters that were provided to the ViewBuilder.
     *
     * @var array
     */
    private $builderParameters;

    /**
     * ParameterBag used to manipulate the view parameters. Its contents will be injected as the view parameters.
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    private $parameterBag;

    public function __construct(View $view, array $builderParameters)
    {
        $this->view = clone $view;
        $this->builderParameters = $builderParameters;
        $this->parameterBag = new ParameterBag();
    }

    /**
     * Returns the parameters that can be injected into the View.
     *
     * @return array
     */
    public function getViewParameters()
    {
        return $this->parameterBag->all();
    }

    /**
     * Returns the parameters that were passed to the builder.
     *
     * @return array
     */
    public function getBuilderParameters()
    {
        return $this->builderParameters;
    }

    /**
     * Returns the ParameterBag used to manipulate the view parameters.
     * @return ParameterBag
     */
    public function getParameterBag()
    {
        return $this->parameterBag;
    }

    /**
     * Returns the copy of the View object.
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }
}
