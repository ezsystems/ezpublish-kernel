<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\URIElement class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher;

use eZ\Publish\MVC\SiteAccess\Matcher;

class URIElement implements Matcher
{
    /**
     * Path of the URI as returned by parse_url().
     *
     * @var string
     */
    private $path;

    /**
     * Number of elements to take into account.
     *
     * @var int
     */
    private $elementNumber;

    /**
     * Constructor.
     *
     * @param array $URIElements Elements of the URI as parsed by parse_url().
     * @param int $elementNumber Number of elements to take into account.
     */
    public function __construct( array $URIElements, $elementNumber )
    {
        $this->path = isset( $URIElements["path"] ) ? $URIElements["path"] : "";
        $this->elementNumber = (int)$elementNumber;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        $elements = array_slice(
            explode( "/", $this->path ),
            1,
            $this->elementNumber
        );

        // If one of the elements is empty, we do not match.
        foreach ( $elements as $element )
        {
            if ( $element === "" )
                return false;
        }

        if ( count( $elements ) !== $this->elementNumber )
            return false;

        return implode( "_", $elements );
    }

    public function getName()
    {
        return 'uri:element';
    }
}
