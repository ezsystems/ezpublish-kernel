<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
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

    /** @var ParametersInjector */
    private $viewParametersInjector;

    public function __construct(
        Repository $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        ParametersInjector $viewParametersInjector
    ) {
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
        $this->viewParametersInjector = $viewParametersInjector;
    }

    public function matches($argument)
    {
        return strpos($argument, 'ez_content:') !== false;
    }

    /**
     * @throws InvalidArgumentException If both contentId and locationId parameters are missing
     * @throws NotFoundHttpException If the location is invisible
     */
    public function buildView(array $parameters)
    {
        $view = new ContentView(null, [], $parameters['viewType']);

        if (isset($parameters['locationId'])) {
            $location = $this->loadLocation($parameters['locationId']);
            if ($location->invisible) {
                throw new NotFoundHttpException('Location cannot be displayed as it is flagged as invisible.');
            }
        } elseif (isset($parameters['location'])) {
            $location = $parameters['location'];
        }

        if (isset($parameters['content'])) {
            $content = $parameters['content'];
        } else {
            if (isset($parameters['contentId'])) {
                $contentId = $parameters['contentId'];
            } elseif (isset($location)) {
                $contentId = $location->contentId;
            } else {
                throw new InvalidArgumentException('Content', 'No content could not be loaded from parameters');
            }
            $content = $this->loadContent(
                $view->getViewType(),
                $contentId,
                isset($location) ? $location : null
            );
        }

        $view->setContent($content);
        if (isset($location)) {
            $view->setLocation($location);
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        // viewLocation/embedLocation without a custom controller are mapped to their viewContent equivalent
        if ($parameters['_controller'] === 'ez_content:viewLocation') {
            $view->setControllerReference(new ControllerReference('ez_content:viewContent'));
        } elseif ($parameters['_controller'] === 'ez_content:embedLocation') {
            $view->setControllerReference(new ControllerReference('ez_content:embedContent'));
        }

        return $view;
    }

    /**
     * Loads Content with id $contentId.
     * Will cover permissions for special viewtypes (ex: embed).
     *
     * @param string $viewType
     * @param mixed $contentId
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    private function loadContent($viewType, $contentId, Location $location = null)
    {
        if ($viewType === 'embed') {
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
        } else {
            $content = $this->repository->getContentService()->loadContent($contentId);
        }

        return $content;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    private function loadLocation($locationId)
    {
        return $this->repository->sudo(
            function (Repository $repository) use ($locationId) {
                return $repository->getLocationService()->loadLocation($locationId);
            }
        );
    }

    /**
     * Checks if a user can read a content, or view it as an embed.
     *
     * @param Content $content
     * @param $location
     *
     * @return bool
     */
    private function canRead(Content $content, Location $location = null)
    {
        $limitations = ['valueObject' => $content->contentInfo];
        if (isset($location)) {
            $limitations['location'] = $location;
        }

        $readAttribute = new AuthorizationAttribute('content', 'read', $limitations);
        $viewEmbedAttribute = new AuthorizationAttribute('content', 'view_embed', $limitations);

        return
            $this->authorizationChecker->isGranted($readAttribute) ||
            $this->authorizationChecker->isGranted($viewEmbedAttribute);
    }
}
