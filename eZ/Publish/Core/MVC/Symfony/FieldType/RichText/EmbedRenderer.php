<?php
/**
 * File containing the RichText field type Symfony EmbedRenderer class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\RichText;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\FieldType\RichText\EmbedRendererInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface as ControllerManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Symfony implementation of RichText field type embed renderer
 */
class EmbedRenderer implements EmbedRendererInterface
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface
     */
    protected $controllerManager;

    /**
     * @var \Symfony\Component\HttpKernel\Fragment\FragmentHandler
     */
    protected $fragmentHandler;

    /**
     * @var string
     */
    protected $renderingStrategy;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        Repository $repository,
        ControllerManagerInterface $controllerManager,
        FragmentHandler $fragmentHandler,
        $renderingStrategy,
        LoggerInterface $logger = null
    )
    {
        $this->repository = $repository;
        $this->controllerManager = $controllerManager;
        $this->fragmentHandler = $fragmentHandler;
        $this->renderingStrategy = $renderingStrategy;
        $this->logger = $logger;
    }

    /**
     * Renders Content embed view
     *
     * @param int|string $contentId
     * @param string $viewType
     * @param array $parameters
     *
     * @return string
     */
    public function renderContent( $contentId, $viewType, array $parameters )
    {
        try
        {
            /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
            $contentInfo = $this->repository->sudo(
                function ( Repository $repository ) use ( $contentId )
                {
                    return $repository->getContentService()->loadContentInfo( $contentId );
                }
            );

            $controllerReference = $this->controllerManager->getControllerReference(
                $contentInfo,
                $viewType
            );

            if ( !$controllerReference instanceof ControllerReference )
            {
                $controllerReference = $this->getDefaultContentControllerReference(
                    $contentId,
                    $viewType,
                    $parameters
                );
            }
            else
            {
                $controllerReference->attributes = array(
                    "contentId" => $contentId,
                    "viewType" => $viewType,
                    "params" => $parameters
                );
            }

            $rendered = $this->fragmentHandler->render(
                $controllerReference,
                $this->renderingStrategy,
                $parameters
            );
        }
        catch ( AccessDeniedException $e )
        {
            if ( isset( $this->logger ) )
            {
                $this->logger->error(
                    "Could not render embedded resource: access denied to embed Content #{$contentId}"
                );
            }

            $rendered = null;
        }
        catch ( Exception $e )
        {
            if ( $e instanceof NotFoundHttpException || $e instanceof NotFoundException )
            {
                if ( isset( $this->logger ) )
                {
                    $this->logger->error(
                        "Could not render embedded resource: Content #{$contentId} not found"
                    );
                }

                $rendered = null;
            }
            else
            {
                throw $e;
            }
        }

        return $rendered;
    }

    /**
     * Renders Location embed view
     *
     * @param int|string $locationId
     * @param string $viewType
     * @param array $params
     *
     * @return string
     */
    public function renderLocation( $locationId, $viewType, array $parameters )
    {
        try
        {
            /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
            $location = $this->repository->sudo(
                function ( Repository $repository ) use ( $locationId )
                {
                    return $repository->getLocationService()->loadLocation( $locationId );
                }
            );

            $controllerReference = $this->controllerManager->getControllerReference(
                $location,
                $viewType
            );

            if ( !$controllerReference instanceof ControllerReference )
            {
                $controllerReference = $this->getDefaultLocationControllerReference(
                    $locationId,
                    $viewType,
                    $parameters
                );
            }
            else
            {
                $controllerReference->attributes = array(
                    "locationId" => $locationId,
                    "viewType" => $viewType,
                    "params" => $parameters
                );
            }

            $rendered = $this->fragmentHandler->render(
                $controllerReference,
                $this->renderingStrategy,
                $parameters
            );
        }
        catch ( AccessDeniedException $e )
        {
            if ( isset( $this->logger ) )
            {
                $this->logger->error(
                    "Could not render embedded resource: access denied to embed Location #{$locationId}"
                );
            }

            $rendered = null;
        }
        catch ( Exception $e )
        {
            if ( $e instanceof NotFoundHttpException || $e instanceof NotFoundException )
            {
                if ( isset( $this->logger ) )
                {
                    $this->logger->error(
                        "Could not render embedded resource: Location #{$locationId} not found"
                    );
                }

                $rendered = null;
            }
            else
            {
                throw $e;
            }
        }

        return $rendered;
    }

    /**
     * @param string|int $contentId
     * @param string $viewType
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference
     */
    protected function getDefaultContentControllerReference( $contentId, $viewType, $parameters )
    {
        return new ControllerReference(
            "ez_content:embedContent",
            array(
                "contentId" => $contentId,
                "viewType" => $viewType,
                "params" => $parameters
            )
        );
    }

    /**
     * @param string|int $locationId
     * @param string $viewType
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference
     */
    protected function getDefaultLocationControllerReference( $locationId, $viewType, $parameters )
    {
        return new ControllerReference(
            "ez_content:embedLocation",
            array(
                "locationId" => $locationId,
                "viewType" => $viewType,
                "params" => $parameters
            )
        );
    }
}
