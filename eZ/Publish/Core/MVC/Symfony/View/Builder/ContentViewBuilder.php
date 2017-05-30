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
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\MVC\Symfony\View\EmbedView;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Builds ContentView objects.
 */
class ContentViewBuilder implements ViewBuilder
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector */
    private $viewParametersInjector;

    /**
     * Default templates, indexed per viewType (full, line, ...).
     * @var array
     */
    private $defaultTemplates;

    /**
     * @var \eZ\Publish\Core\Helper\ContentInfoLocationLoader|null
     */
    private $locationLoader;
    /**
     * @var string
     */
    private $viewClassFullName;

    public function __construct(
        Repository $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        ContentInfoLocationLoader $locationLoader = null,
        $viewClassFullName = null
    ) {
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->locationLoader = $locationLoader;
        $this->viewClassFullName = ContentView::class;
        if($viewClassFullName)
        {
            $viewReflectCLass = new \ReflectionClass($viewClassFullName);
            $view2 = $viewReflectCLass->newInstanceWithoutConstructor();
            if(!($view2 instanceof ContentView))
            {
                throw new InvalidArgumentException('viewClassFullName', "View class does not extend: "
                    . ContentView::class);
            }
            $this->viewClassFullName = $viewClassFullName;
        }
    }

    /**
     * @return Repository|\eZ\Publish\Core\Repository\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public function getAuthorizationChecker()
    {
        return $this->authorizationChecker;
    }

    /**
     * @return Configurator
     */
    public function getViewConfigurator()
    {
        return $this->viewConfigurator;
    }

    /**
     * @return ParametersInjector
     */
    public function getViewParametersInjector()
    {
        return $this->viewParametersInjector;
    }

    /**
     * @return array
     */
    public function getDefaultTemplates()
    {
        return $this->defaultTemplates;
    }

    /**
     * @return ContentInfoLocationLoader|null
     */
    public function getLocationLoader()
    {
        return $this->locationLoader;
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
        $viewClassFullName = $this->getViewClassFullName();
        /** @var \eZ\Publish\Core\MVC\Symfony\View\ContentView $view */
        $view = new $viewClassFullName(null, [], $parameters['viewType']);
        $view->setIsEmbed($this->isEmbed($parameters));

        if ($view->isEmbed() && $parameters['viewType'] === null) {
            $view->setViewType(EmbedView::DEFAULT_VIEW_TYPE);
        }

        if (isset($parameters['locationId'])) {
            $location = $this->loadLocation($parameters['locationId']);
        } elseif (isset($parameters['location'])) {
            /** @var Location $location */
            $location = $parameters['location'];
        } else {
            $location = null;
        }

        if (isset($parameters['content'])) {
            /** @var Content $content */
            $content = $parameters['content'];
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
        } elseif ($this->getLocationLoader()) {
            try {
                $location = $this->getLocationLoader()->loadLocation($content->contentInfo);
            } catch (NotFoundException $e) {
                // nothing else to do
            }
        }

        if (isset($location)) {
            $view->setLocation($location);
        }

        $this->getViewParametersInjector()->injectViewParameters($view, $parameters);
        $this->getViewConfigurator()->configure($view);

        // deprecated controller actions are replaced with their new equivalent, viewAction and embedAction
        if (!$view->getControllerReference() instanceof ControllerReference) {
            if (in_array($parameters['_controller'], ['ez_content:viewLocation', 'ez_content:viewContent'])) {
                $view->setControllerReference(new ControllerReference('ez_content:viewAction'));
            } elseif (in_array($parameters['_controller'], ['ez_content:embedLocation', 'ez_content:embedContent'])) {
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
    protected function loadContent($contentId)
    {
        return $this->getRepository()->getContentService()->loadContent($contentId);
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
    protected function loadEmbeddedContent($contentId, Location $location = null)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $this->getRepository()->sudo(
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
            && !$this->getAuthorizationChecker()->isGranted(
                new AuthorizationAttribute('content', 'versionread', array('valueObject' => $content))
            )
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
    protected function loadLocation($locationId)
    {
        $location = $this->getRepository()->sudo(
            function (Repository $repository) use ($locationId) {
                return $repository->getLocationService()->loadLocation($locationId);
            }
        );
        if ($location->invisible) {
            throw new NotFoundHttpException('Location cannot be displayed as it is flagged as invisible.');
        }

        return $location;
    }

    /**
     * Checks if a user can read a content, or view it as an embed.
     *
     * @param Content $content
     * @param Location|null $location
     *
     * @return bool
     */
    protected function canRead(Content $content, Location $location = null)
    {
        $limitations = ['valueObject' => $content->contentInfo];
        if (isset($location)) {
            $limitations['targets'] = $location;
        }

        $readAttribute = new AuthorizationAttribute('content', 'read', $limitations);
        $viewEmbedAttribute = new AuthorizationAttribute('content', 'view_embed', $limitations);

        return
            $this->getAuthorizationChecker()->isGranted($readAttribute) ||
            $this->getAuthorizationChecker()->isGranted($viewEmbedAttribute);
    }

    /**
     * Checks if the view is an embed one.
     * Uses either the controller action (embedAction), or the viewType (embed/embed-inline).
     *
     * @param array $parameters The ViewBuilder parameters array.
     *
     * @return bool
     */
    protected function isEmbed($parameters)
    {
        if ($parameters['_controller'] === 'ez_content:embedAction') {
            return true;
        }
        if (in_array($parameters['viewType'], ['embed', 'embed-inline'])) {
            return true;
        }

        return false;
    }
    /**
     * @return string
     */
    public function getViewClassFullName()
    {
        return $this->viewClassFullName;
    }
}
