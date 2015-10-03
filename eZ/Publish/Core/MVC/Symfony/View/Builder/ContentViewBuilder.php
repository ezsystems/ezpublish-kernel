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
use Symfony\Component\HttpKernel\Controller\ControllerReference;
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

    public function __construct(Repository $repository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function matches($argument)
    {
        return strpos($argument, 'ez_content:') !== false;
    }

    /**
     * @throws InvalidArgumentException If both contentId and locationId parameters are missing
     */
    public function buildView(array $parameters)
    {
        $view = new ContentView();
        if (isset($parameters['viewType'])) {
            $view->setViewType($parameters['viewType']);
        }

        if (isset($parameters['locationId'])) {
            $parameters['location'] = $this->loadLocation($parameters['locationId']);
        }

        if (isset($parameters['contentId']) || isset($parameters['location'])) {
            $location = isset($parameters['location']) ? $parameters['location'] : null;

            if (isset($parameters['contentId'])) {
                $contentId = $parameters['contentId'];
            } elseif (isset($parameters['location'])) {
                $contentId = $parameters['location']->contentInfo->id;
            } else {
                throw new InvalidArgumentException('view', 'Missing one of locationId or contentId');
            }

            $parameters['content'] = $this->loadContent($view->getViewType(), $contentId, $location);
        }

        if (isset($parameters['content']) && $parameters['content'] instanceof Content) {
            $view->setContent($parameters['content']);
        } else {
            throw new InvalidArgumentException('Content', 'Content could not be loaded from parameters');
        }

        if (isset($parameters['location']) && $parameters['location'] instanceof Location) {
            $view->setLocation($parameters['location']);
        }

        $view->setContent($parameters['content']);
        $viewParameters = ['contentId' => $parameters['content']->id];
        if (isset($parameters['location'])) {
            $view->setLocation($parameters['location']);
            $viewParameters['locationId'] = $parameters['location']->id;
        }
        $view->addParameters($viewParameters);

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
