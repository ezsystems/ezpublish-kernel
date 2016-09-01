<?php
/**
 * This file is part of the ezplatform package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\ValueLoaders;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\RouterInterface;
use UnexpectedValueException;

/**
 * An URI value loader that uses router to run the matching controller and load the value.
 *
 * The URL is matched using the router, the controller configured with the ControllerResolver, and executed.
 * Since Rest controllers don't return a response but a Value Object, the result can be used directly.
 */
class ControllerUriValueLoader implements UriValueLoader
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ControllerResolverInterface
     */
    private $controllerResolver;

    public function __construct(RouterInterface $router, ControllerResolverInterface $controllerResolver)
    {
        $this->router = $router;
        $this->controllerResolver = $controllerResolver;
    }

    public function load($restResourceLink, $mediaType = null)
    {
        $request = Request::create($restResourceLink);
        $request->attributes->add($this->router->match($restResourceLink));

        if ($mediaType !== null) {
            $request->headers->set('Accept', $mediaType);
        }

        $controller = $this->controllerResolver->getController($request);
        $arguments = $this->controllerResolver->getArguments($request, $controller);

        $value = call_user_func_array($controller, $arguments);

        if ($value instanceof Response) {
            throw new UnexpectedValueException('Expected the controller to return a Value object, got a Response instead');
        }

        return $value;
    }
}
