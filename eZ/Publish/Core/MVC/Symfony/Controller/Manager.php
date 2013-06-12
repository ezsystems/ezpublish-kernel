<?php
/**
 * File containing the controller Manager class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class Manager implements ManagerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory
     */
    protected $locationMatcherFactory;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory
     */
    protected $contentMatcherFactory;

    public function __construct( ContentBasedMatcherFactory $locationMatcherFactory, ContentBasedMatcherFactory $contentMatcherFactory, LoggerInterface $logger )
    {
        $this->locationMatcherFactory = $locationMatcherFactory;
        $this->contentMatcherFactory = $contentMatcherFactory;
        $this->logger = $logger;
    }

    /**
     * Returns a ControllerReference object corresponding to $valueObject and $viewType
     *
     * @param ValueObject $valueObject
     * @param string $viewType
     *
     * @throws \InvalidArgumentException
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference|null
     */
    public function getControllerReference( ValueObject $valueObject, $viewType )
    {
        $matchedType = null;
        if ( $valueObject instanceof Location )
        {
            $matcherProp = 'locationMatcherFactory';
            $matchedType = 'Location';
        }
        else if ( $valueObject instanceof ContentInfo )
        {
            $matcherProp = 'contentMatcherFactory';
            $matchedType = 'Content';
        }
        else
        {
            throw new InvalidArgumentException( 'Unsupported value object to match against' );
        }

        $configHash = $this->$matcherProp->match( $valueObject, $viewType );
        if ( !is_array( $configHash ) || !isset( $configHash['controller'] ) )
        {
            return;
        }

        $this->logger->debug( "Matched custom controller '{$configHash['controller']}' for $matchedType #$valueObject->id" );
        return new ControllerReference( $configHash['controller'] );
    }
}
