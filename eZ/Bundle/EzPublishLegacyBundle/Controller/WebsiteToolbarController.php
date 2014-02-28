<?php
/**
 * File containing the WebsiteToolbarController class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;

class WebsiteToolbarController extends Controller
{
    /** @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface */
    private $csrfProvider;

    /** @var \Symfony\Component\Templating\EngineInterface */
    private $legacyTemplateEngine;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \Symfony\Component\Security\Core\SecurityContextInterface */
    private $securityContext;

    public function __construct( CsrfProviderInterface $csrfProvider, EngineInterface $engine, Repository $repository, SecurityContextInterface $securityContext )
    {
        $this->csrfProvider = $csrfProvider;
        $this->legacyTemplateEngine = $engine;
        $this->repository = $repository;
        $this->securityContext = $securityContext;
    }

    /**
     * Renders the legacy website toolbar template.
     *
     * If the logged in user doesn't have the required permission, an empty response is returned
     *
     * @param mixed $locationId
     */
    public function websiteToolbarAction( $locationId )
    {
        $response = new Response();

        $authorizationAttribute = new AuthorizationAttribute(
            'websitetoolbar',
            'use',
            array( 'valueObject' => $this->loadContentByLocationId( $locationId ) )
        );

        if ( !$this->securityContext->isGranted( $authorizationAttribute ) )
        {
            return $response;
        }

        $response->setContent(
            $this->legacyTemplateEngine->render(
                'design:parts/website_toolbar.tpl',
                array(
                    'current_node_id' => $locationId,
                    'form_token' => $this->csrfProvider->generateCsrfToken( 'legacy' )
                )
            )
        );

        return $response;
    }

    /**
     * @return Content
     */
    protected function loadContentByLocationId( $locationId )
    {
        return $this->repository->getContentService()->loadContent(
            $this->repository->getLocationService()->loadLocation( $locationId )->contentId
        );
    }
}

