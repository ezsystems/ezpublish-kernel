<?php

/**
 * File containing the RichText field type Symfony Renderer class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\FieldType\RichText;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\FieldType\RichText\RendererInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Symfony implementation of RichText field type embed renderer.
 */
class Renderer implements RendererInterface
{
    const RESOURCE_TYPE_CONTENT = 0;
    const RESOURCE_TYPE_LOCATION = 1;

    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string
     */
    protected $tagConfigurationNamespace;

    /**
     * @var string
     */
    protected $embedConfigurationNamespace;

    /**
     * @var ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     * @param string $tagConfigurationNamespace
     * @param string $embedConfigurationNamespace
     * @param null|\Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Repository $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        ConfigResolverInterface $configResolver,
        EngineInterface $templateEngine,
        $tagConfigurationNamespace,
        $embedConfigurationNamespace,
        LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
        $this->configResolver = $configResolver;
        $this->templateEngine = $templateEngine;
        $this->tagConfigurationNamespace = $tagConfigurationNamespace;
        $this->embedConfigurationNamespace = $embedConfigurationNamespace;
        $this->logger = $logger;
    }

    public function renderTag($name, array $parameters, $isInline)
    {
        $templateName = $this->getTagTemplateName($name, $isInline);

        if ($templateName === null) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Could not render template tag '{$name}': no template configured"
                );
            }

            return null;
        }

        if (!$this->templateEngine->exists($templateName)) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Could not render template tag '{$name}': template '{$templateName}' does not exists"
                );
            }

            return null;
        }

        return $this->render($templateName, $parameters);
    }

    public function renderContentEmbed($contentId, $viewType, array $parameters, $isInline)
    {
        $isDenied = false;

        try {
            $this->checkContent($contentId);
        } catch (AccessDeniedException $e) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Could not render embedded resource: access denied to embed Content #{$contentId}"
                );
            }

            $isDenied = true;
        } catch (Exception $e) {
            if ($e instanceof NotFoundHttpException || $e instanceof NotFoundException) {
                if (isset($this->logger)) {
                    $this->logger->error(
                        "Could not render embedded resource: Content #{$contentId} not found"
                    );
                }

                return null;
            } else {
                throw $e;
            }
        }

        $templateName = $this->getEmbedTemplateName(
            static::RESOURCE_TYPE_CONTENT,
            $isInline,
            $isDenied
        );

        if ($templateName === null) {
            $this->logger->error(
                'Could not render embedded resource: no template configured'
            );

            return null;
        }

        if (!$this->templateEngine->exists($templateName)) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Could not render embedded resource: template '{$templateName}' does not exists"
                );
            }

            return null;
        }

        return $this->render($templateName, $parameters);
    }

    public function renderLocationEmbed($locationId, $viewType, array $parameters, $isInline)
    {
        $isDenied = false;

        try {
            $location = $this->checkLocation($locationId);

            if ($location->invisible) {
                if (isset($this->logger)) {
                    $this->logger->error(
                        "Could not render embedded resource: Location #{$locationId} is not visible"
                    );
                }

                return null;
            }
        } catch (AccessDeniedException $e) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Could not render embedded resource: access denied to embed Location #{$locationId}"
                );
            }

            $isDenied = true;
        } catch (Exception $e) {
            if ($e instanceof NotFoundHttpException || $e instanceof NotFoundException) {
                if (isset($this->logger)) {
                    $this->logger->error(
                        "Could not render embedded resource: Location #{$locationId} not found"
                    );
                }

                return null;
            } else {
                throw $e;
            }
        }

        $templateName = $this->getEmbedTemplateName(
            static::RESOURCE_TYPE_LOCATION,
            $isInline,
            $isDenied
        );

        if ($templateName === null) {
            $this->logger->error(
                'Could not render embedded resource: no template configured'
            );

            return null;
        }

        if (!$this->templateEngine->exists($templateName)) {
            if (isset($this->logger)) {
                $this->logger->error(
                    "Could not render embedded resource: template '{$templateName}' does not exists"
                );
            }

            return null;
        }

        return $this->render($templateName, $parameters);
    }

    /**
     * Renders template $templateReference with given $parameters.
     *
     * @param string $templateReference
     * @param array $parameters
     *
     * @return string
     */
    protected function render($templateReference, array $parameters)
    {
        return $this->templateEngine->render(
            $templateReference,
            $parameters
        );
    }

    /**
     * Returns configured template name for the given template tag identifier.
     *
     * @param string $identifier
     * @param bool $isInline
     *
     * @return null|string
     */
    protected function getTagTemplateName($identifier, $isInline)
    {
        $configurationReference = $this->tagConfigurationNamespace . '.' . $identifier;

        if ($this->configResolver->hasParameter($configurationReference)) {
            $configuration = $this->configResolver->getParameter($configurationReference);

            return $configuration['template'];
        }

        if (isset($this->logger)) {
            $this->logger->warning(
                "Template tag '{$identifier}' configuration was not found"
            );
        }

        if ($isInline) {
            $configurationReference = $this->tagConfigurationNamespace . '.default_inline';
        } else {
            $configurationReference = $this->tagConfigurationNamespace . '.default';
        }

        if ($this->configResolver->hasParameter($configurationReference)) {
            $configuration = $this->configResolver->getParameter($configurationReference);

            return $configuration['template'];
        }

        if (isset($this->logger)) {
            $this->logger->warning(
                "Template tag '{$identifier}' default configuration was not found"
            );
        }

        return null;
    }

    /**
     * Returns configured template reference for the given embed parameters.
     *
     * @param $resourceType
     * @param $isInline
     * @param $isDenied
     *
     * @return null|string
     */
    protected function getEmbedTemplateName($resourceType, $isInline, $isDenied)
    {
        $configurationReference = $this->embedConfigurationNamespace;

        if ($resourceType === static::RESOURCE_TYPE_CONTENT) {
            $configurationReference .= '.content';
        } else {
            $configurationReference .= '.location';
        }

        if ($isInline) {
            $configurationReference .= '_inline';
        }

        if ($isDenied) {
            $configurationReference .= '_denied';
        }

        if ($this->configResolver->hasParameter($configurationReference)) {
            $configuration = $this->configResolver->getParameter($configurationReference);

            return $configuration['template'];
        }

        if (isset($this->logger)) {
            $this->logger->warning(
                "Embed tag configuration '{$configurationReference}' was not found"
            );
        }

        $configurationReference = $this->embedConfigurationNamespace;

        $configurationReference .= '.default';

        if ($isInline) {
            $configurationReference .= '_inline';
        }

        if ($this->configResolver->hasParameter($configurationReference)) {
            $configuration = $this->configResolver->getParameter($configurationReference);

            return $configuration['template'];
        }

        if (isset($this->logger)) {
            $this->logger->warning(
                "Embed tag default configuration '{$configurationReference}' was not found"
            );
        }

        return null;
    }

    /**
     * Check embed permissions for the given Content $id.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param int|string $id
     */
    protected function checkContent($id)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $this->repository->sudo(
            function (Repository $repository) use ($id) {
                return $repository->getContentService()->loadContent($id);
            }
        );

        // Check both 'content/read' and 'content/view_embed'.
        if (
            !$this->authorizationChecker->isGranted(
                new AuthorizationAttribute('content', 'read', array('valueObject' => $content))
            )
            && !$this->authorizationChecker->isGranted(
                new AuthorizationAttribute('content', 'view_embed', array('valueObject' => $content))
            )
        ) {
            throw new AccessDeniedException();
        }

        // Check that Content is published, since sudo allows loading unpublished content.
        if (
            !$content->getVersionInfo()->isPublished()
            && !$this->authorizationChecker->isGranted(
                new AuthorizationAttribute('content', 'versionread', array('valueObject' => $content))
            )
        ) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Checks embed permissions for the given Location $id and returns the Location.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param int|string $id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected function checkLocation($id)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        $location = $this->repository->sudo(
            function (Repository $repository) use ($id) {
                return $repository->getLocationService()->loadLocation($id);
            }
        );

        // Check both 'content/read' and 'content/view_embed'.
        if (
            !$this->authorizationChecker->isGranted(
                new AuthorizationAttribute(
                    'content',
                    'read',
                    array('valueObject' => $location->contentInfo, 'targets' => $location)
                )
            )
            && !$this->authorizationChecker->isGranted(
                new AuthorizationAttribute(
                    'content',
                    'view_embed',
                    array('valueObject' => $location->contentInfo, 'targets' => $location)
                )
            )
        ) {
            throw new AccessDeniedException();
        }

        return $location;
    }
}
