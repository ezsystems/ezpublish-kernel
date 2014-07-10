<?php
/**
 * File containing the PreContentViewListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

// add the new use statement at the top of your file
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use ezxFormToken;

class KernelResponseListener
{

    private $fieldName;

    /**
     * CSRF token
     *
     * @var string
     */
    private $csrfToken = null;

    /**
     * @param CsrfProviderInterface $csrfProvider
     */
    public function __construct( $fieldName, CsrfProviderInterface $csrfProvider = null )
    {
        $this->fieldName = $fieldName;
        if ( $csrfProvider )
        {
            $this->csrfToken = $csrfProvider->generateCsrfToken( 'legacy' );
        }
    }

    public function onKernelResponse( FilterResponseEvent $event )
    {
        if ( !class_exists( 'ezxFormToken' ) || !ezxFormToken::isEnabled() )
            return;

        $response = $event->getResponse();
        foreach ( $response->headers as $header )
        {
            // Search for a content-type header that is NOT HTML
            if ( is_string( $header ) &&
                stripos( $header, 'Content-Type:' ) === 0 &&
                strpos( $header, 'text/html' ) === false &&
                strpos( $header, 'application/xhtml+xml' ) === false )
            {
               return;
            }
        }

        $content = $response->getContent();

        // If document has head tag, insert in a html5 valid and semi standard way
        if ( strpos( $content, '<head>' ) !== false )
        {
            $content = str_replace(
                '<head>',
                "<head>\n"
                . "<meta name=\"csrf-param\" content=\"{$this->fieldName}\" />\n"
                . "<meta name=\"csrf-token\" id=\"{$this->fieldName}_js\" title=\"{$this->csrfToken}\" content=\"{$this->csrfToken}\" />\n",
                $content
            );
        }

        $response->setContent( $content );
    }
}
