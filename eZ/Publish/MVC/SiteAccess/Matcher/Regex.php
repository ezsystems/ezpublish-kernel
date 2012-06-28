<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\Regex class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher;

use eZ\Publish\MVC\SiteAccess\Matcher;

abstract class Regex implements Matcher
{
    /**
     * Element that will be matched against the regex.
     *
     * @var string
     */
    private $element;

    /**
     * Regular expression used for matching.
     *
     * @var string
     */
    private $regex;

    /**
     * Item number to pick in regex.
     *
     * @var string
     */
    private $itemNumber;

    /**
     * Constructor.
     *
     * @param string $element Element on which to perform the matching.
     * @param string $regex Regular Expression to use.
     * @param int $itemNumber Item number to pick in regex.
     */
    public function __construct( $element, $regex, $itemNumber )
    {
        $this->element = $element;
        $this->regex = $regex;
        $this->itemNumber = $itemNumber;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        preg_match(
            "@{$this->regex}@",
            $this->element,
            $match
        );

        return isset( $match[$this->itemNumber] ) ? $match[$this->itemNumber] : false;
    }
}
