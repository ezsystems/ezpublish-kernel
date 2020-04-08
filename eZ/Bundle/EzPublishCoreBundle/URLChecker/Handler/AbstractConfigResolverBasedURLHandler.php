<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\URLChecker\Handler;

use eZ\Publish\API\Repository\URLService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * URLHandler based on ConfigResolver configured using $parameterName, $namespace and $scope properties.
 */
abstract class AbstractConfigResolverBasedURLHandler extends AbstractURLHandler
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var string */
    private $parameterName;

    /** @var string|null */
    private $namespace;

    /** @var string|null */
    private $scope;

    public function __construct(
        URLService $urlService,
        ConfigResolverInterface $configResolver,
        string $parameterName,
        ?string $namespace = null,
        ?string $scope = null
    ) {
        parent::__construct($urlService);

        $this->configResolver = $configResolver;
        $this->parameterName = $parameterName;
        $this->namespace = $namespace;
        $this->scope = $scope;
    }

    public function getOptions(): array
    {
        $options = $this->configResolver->getParameter(
            $this->parameterName,
            $this->namespace,
            $this->scope
        );

        return $this->getOptionsResolver()->resolve($options);
    }
}
