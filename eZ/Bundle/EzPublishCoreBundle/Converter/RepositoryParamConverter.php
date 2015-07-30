<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Converter;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class RepositoryParamConverter implements ParamConverterInterface
{
    public function supports(ParamConverter $configuration)
    {
        return is_a($configuration->getClass(), $this->getSupportedClass(), true);
    }

    abstract protected function getSupportedClass();

    /**
     * @return string property name used in the method of the controller needing param conversion
     */
    abstract protected function getPropertyName();

    /**
     * @return string classes with its namespace
     */
    abstract protected function loadValueObject($id);

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     *
     * @throws NotFoundHttpException if value object was not found
     * @throws AccessDeniedHttpException if user is not allowed to load the value object
     *
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->attributes->has($this->getPropertyName())) {
            return false;
        }

        $valueObjectId = $request->attributes->get($this->getPropertyName());
        if (!$valueObjectId && $configuration->isOptional()) {
            return false;
        }

        try {
            $request->attributes->set($configuration->getName(), $this->loadValueObject($valueObjectId));

            return true;
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException('Requested values not found', $e);
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedHttpException('Access to values denied', $e);
        }
    }
}
