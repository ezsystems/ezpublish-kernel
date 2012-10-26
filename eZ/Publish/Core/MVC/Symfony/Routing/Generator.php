<?php
/**
 * File containing the Generator class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing;

use Symfony\Component\Routing\RequestContext;

/**
 * Base class for eZ Publish Url generation.
 */
abstract class Generator
{
    /**
     * @var \Symfony\Component\Routing\RequestContext $requestContext
     */
    protected $requestContext;

    /**
     * @param \Symfony\Component\Routing\RequestContext $requestContext
     */
    public function setRequestContext( RequestContext $requestContext )
    {
        $this->requestContext = $requestContext;
    }

    /**
     * Triggers URL generation for $urlResource and $parameters.
     *
     * @param mixed $urlResource Type can be anything, depending on the context. It's up to the router to pass the appropriate value to the implemento.r
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    public function generate( $urlResource, array $parameters, $absolute = false )
    {
        $url = $this->requestContext->getBaseUrl() . $this->doGenerate( $urlResource, $parameters );
        if ( $absolute )
        {
            $url = $this->generateAbsoluteUrl( $url );
        }
        
        return $url;
    }

    /**
     * Generates the URL from $urlResource and $parameters.
     *
     * @abstract
     * @param mixed $urlResource
     * @param array $parameters
     * @return string
     */
    abstract public function doGenerate( $urlResource, array $parameters );

    /**
     * Generates an absolute URL from $uri and the request context
     * 
     * @param $uri
     * @return string
     */
    protected function generateAbsoluteUrl( $uri )
    {
        $scheme = $this->requestContext->getScheme();
        $port = '';
        if ( $scheme === 'http' && $this->requestContext->getHttpPort() != 80 )
        {
            $port = ':' . $this->requestContext->getHttpPort();
        }
        else if ( $scheme === 'https' && $this->requestContext->getHttpsPort() != 443 )
        {
            $port = ':' . $this->requestContext->getHttpsPort();
        }

        return $scheme . '://' . $this->requestContext->getHost() . $port . $uri;
    }
}
