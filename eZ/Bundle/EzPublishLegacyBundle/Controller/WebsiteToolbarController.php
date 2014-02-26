<?php
/**
 * File containing the WebsiteToolbarController class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class WebsiteToolbarController extends Controller
{
    /** @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface */
    private $csrfProvider;

    /** @var \Symfony\Component\Templating\EngineInterface */
    private $legacyTemplateEngine;

    public function __construct( CsrfProviderInterface $csrfProvider, EngineInterface $engine )
    {
        $this->csrfProvider = $csrfProvider;
        $this->legacyTemplateEngine = $engine;
    }

    public function websiteToolbarAction( $locationId )
    {
        return new Response(
            $this->legacyTemplateEngine->render(
                'design:parts/website_toolbar.tpl',
                array(
                    'current_node_id' => $locationId,
                    'form_token' => $this->csrfProvider->generateCsrfToken( 'legacy' )
                )
            )
        );
    }
}
 