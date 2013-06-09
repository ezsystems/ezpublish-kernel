<?php
/**
 * File containing the controller Manager class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
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
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface
     */
    protected $locationMatcherFactory;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherFactoryInterface
     */
    protected $contentMatcherFactory;

    public function __construct( MatcherFactoryInterface $locationMatcherFactory, MatcherFactoryInterface $contentMatcherFactory, LoggerInterface $logger )
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
        if ( $valueObject instanceof Location )
        {
            $matcherProp = 'locationMatcherFactory';
        }
        else if ( $valueObject instanceof Content )
        {
            $matcherProp = 'contentMatcherFactory';
        }
        else
        {
            throw new InvalidArgumentException( 'Unsupported value object to match against' );
        }

        $configHash = $this->$matcherProp->match( $viewType, $valueObject );
        if ( !is_array( $configHash ) || !isset( $configHash['controller'] ) )
        {
            return;
        }

        return new ControllerReference( $configHash['controller'] );
    }
}
