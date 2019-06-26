<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Helper\ContentInfoLocationLoader;
use eZ\Publish\Core\MVC\Exception\HiddenLocationException;
use eZ\Publish\Core\MVC\Symfony\Controller\Content\PreviewController;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\EmbedView;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Builds ContentView objects.
 */
class ContentViewBuilder implements ViewBuilder
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector */
    private $viewParametersInjector;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /**
     * Default templates, indexed per viewType (full, line, ...).
     * @var array
     */
    private $defaultTemplates;

    /** @var \eZ\Publish\Core\Helper\ContentInfoLocationLoader */
    private $locationLoader;

    public function __construct(
        Repository $repository,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        RequestStack $requestStack,
        ContentInfoLocationLoader $locationLoader = null
    ) {
        $this->repository = $repository;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->locationLoader = $locationLoader;
        $this->permissionResolver = $this->repository->getPermissionResolver();
        $this->requestStack = $requestStack;
    }

    public function matches($argument)
    {
        return strpos($argument, 'ez_content:') !== false;
    }

    /**
     * @param array $parameters
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|\eZ\Publish\Core\MVC\Symfony\View\View
     *         If both contentId and locationId parameters are missing
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *         If both contentId and locationId parameters are missing
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function buildView(array $parameters)
    {
        $view = new ContentView(null, [], $parameters['viewType']);
        $view->setIsEmbed($this->isEmbed($parameters));

        if ($view->isEmbed() && $parameters['viewType'] === null) {
            $view->setViewType(EmbedView::DEFAULT_VIEW_TYPE);
        }

        if (isset($parameters['location']) && $parameters['location'] instanceof Location) {
            $location = $parameters['location'];
        } elseif (isset($parameters['locationId'])) {
            $location = $this->loadLocation($parameters['locationId']);
        } else {
            $location = null;
        }

        if (isset($parameters['content'])) {
            $content = $parameters['content'];
        } elseif ($location instanceof Location) {
            // if we already have location load content true it so we avoid dual loading in case user does that in view
            $content = $location->getContent();
            if (!$this->canRead($content, $location, $view->isEmbed())) {
                $missingPermission = 'read' . ($view->isEmbed() ? '|view_embed' : '');
                throw new UnauthorizedException(
                    'content',
                    $missingPermission,
                    [
                        'contentId' => $content->id,
                        'locationId' => $location->id,
                    ]
                );
            }
        } else {
            if (isset($parameters['contentId'])) {
                $contentId = $parameters['contentId'];
            } elseif (isset($location)) {
                $contentId = $location->contentId;
            } else {
                throw new InvalidArgumentException('Content', 'No content could be loaded from parameters');
            }

            $content = $view->isEmbed() ? $this->loadEmbeddedContent($contentId, $location) : $this->loadContent($contentId);
        }

        $view->setContent($content);

        if (isset($location)) {
            if ($location->contentId !== $content->id) {
                throw new InvalidArgumentException('Location', 'Provided location does not belong to selected content');
            }

            if (isset($parameters['contentId']) && $location->contentId !== (int)$parameters['contentId']) {
                throw new InvalidArgumentException(
                    'Location',
                    'Provided location does not belong to selected content as requested via contentId parameter'
                );
            }
        } elseif (isset($this->locationLoader)) {
            try {
                $location = $this->locationLoader->loadLocation($content->contentInfo);
            } catch (NotFoundException $e) {
                // nothing else to do
            }
        }

        if (isset($location)) {
            $view->setLocation($location);
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);
        $this->viewConfigurator->configure($view);

        // deprecated controller actions are replaced with their new equivalent, viewAction and embedAction
        if (!$view->getControllerReference() instanceof ControllerReference) {
            if (\in_array($parameters['_controller'], ['ez_content:viewLocation', 'ez_content:viewContent'])) {
                $view->setControllerReference(new ControllerReference('ez_content:viewAction'));
            } elseif (\in_array($parameters['_controller'], ['ez_content:embedLocation', 'ez_content:embedContent'])) {
                $view->setControllerReference(new ControllerReference('ez_content:embedAction'));
            }
        }

        return $view;
    }

    /**
     * Loads Content with id $contentId.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    private function loadContent($contentId)
    {
        return $this->repository->getContentService()->loadContent($contentId);
    }

    /**
     * Loads the embedded content with id $contentId.
     * Will load the content with sudo(), and check if the user can view_embed this content, for the given location
     * if provided.
     *
     * @param mixed $contentId
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    private function loadEmbeddedContent($contentId, Location $location = null)
    {
        $content = $this->repository->sudo(
            function (Repository $repository) use ($contentId) {
                return $repository->getContentService()->loadContent($contentId);
            }
        );

        if (!$this->canRead($content, $location)) {
            throw new UnauthorizedException(
                'content', 'read|view_embed',
                ['contentId' => $contentId, 'locationId' => $location !== null ? $location->id : 'n/a']
            );
        }

        // Check that Content is published, since sudo allows loading unpublished content.
        if (
            $content->getVersionInfo()->status !== VersionInfo::STATUS_PUBLISHED
            && !$this->permissionResolver->canUser('content', 'versionread', $content)
        ) {
            throw new UnauthorizedException('content', 'versionread', ['contentId' => $contentId]);
        }

        return $content;
    }

    /**
     * Loads a visible Location.
     * @todo Do we need to handle permissions here ?
     *
     * @param $locationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    private function loadLocation($locationId)
    {
        $location = $this->repository->sudo(
            function (Repository $repository) use ($locationId) {
                return $repository->getLocationService()->loadLocation($locationId);
            }
        );

        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$request->attributes->get(PreviewController::PREVIEW_PARAMETER_NAME, false)) {
            if ($location->invisible) {
                throw new HiddenLocationException($location, 'Location cannot be displayed as it is flagged as invisible.');
            }
        }

        return $location;
    }

    /**
     * Checks if a user can read a content, or view it as an embed.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param bool $isEmbed
     *
     * @return bool
     */
    private function canRead(Content $content, Location $location = null, bool $isEmbed = true): bool
    {
        $targets = isset($location) ? [$location] : [];

        return
            $this->permissionResolver->canUser('content', 'read', $content->contentInfo, $targets) ||
            $this->permissionResolver->canUser('content', 'view_embed', $content->contentInfo, $targets);
    }

    /**
     * Checks if the view is an embed one.
     * Uses either the controller action (embedAction), or the viewType (embed/embed-inline).
     *
     * @param array $parameters The ViewBuilder parameters array.
     *
     * @return bool
     */
    private function isEmbed($parameters)
    {
        if ($parameters['_controller'] === 'ez_content:embedAction') {
            return true;
        }
        if (\in_array($parameters['viewType'], ['embed', 'embed-inline'])) {
            return true;
        }

        return false;
    }
}
