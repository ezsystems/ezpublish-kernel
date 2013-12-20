<?php
/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Helper\ContentPreviewHelper;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PreviewController
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $kernel;

    /**
     * @var \eZ\Publish\Core\Helper\ContentPreviewHelper
     */
    private $previewHelper;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(
        Repository $repository,
        HttpKernelInterface $kernel,
        ContentPreviewHelper $previewHelper
    )
    {
        $this->repository = $repository;
        $this->contentService = $this->repository->getContentService();
        $this->kernel = $kernel;
        $this->previewHelper = $previewHelper;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    public function previewContentAction( $contentId, $versionNo, $language, $siteAccessName )
    {
        try
        {
            $content = $this->contentService->loadContent( $contentId, array( $language ), $versionNo );
            $location = $this->previewHelper->getPreviewLocation( $contentId );
        }
        catch ( UnauthorizedException $e )
        {
            throw new AccessDeniedException();
        }

        if ( !$this->repository->canUser( 'content', 'versionview', $content ) )
        {
            throw new AccessDeniedException();
        }

        $newSiteAccess = $this->previewHelper->changeConfigScope( $siteAccessName );

        $response = $this->kernel->handle(
            $this->request->duplicate(
                null, null,
                array(
                    '_controller' => 'ez_content:viewLocation',
                    'location' => $location,
                    'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                    'layout' => true,
                    'params' => array( 'content' => $content, 'location' => $location, 'isPreview' => true ),
                    'siteaccess' => $newSiteAccess
                )
            ),
            HttpKernelInterface::SUB_REQUEST
        );
        $response->headers->remove( 'cache-control' );
        $response->headers->remove( 'expires' );

        $this->previewHelper->restoreConfigScope();

        return $response;
    }
}
