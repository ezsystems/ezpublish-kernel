<?php
/**
 * File containing the LogicalAnd compound siteaccess matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;

/**
 * Siteaccess matcher that allows a combination of matchers, with a logical AND
 */
class LogicalAnd extends Compound
{
    const NAME = 'logicalAnd';

    public function match()
    {
        foreach ( $this->config as $i => $rule )
        {
            foreach ( $rule['matchers'] as $subMatcherClass => $matchingConfig )
            {
                // If at least one sub matcher doesn't match, jump to the next rule set.
                if ( $this->matchersMap[$i][$subMatcherClass]->match() === false )
                {
                    continue 2;
                }
            }

            $this->subMatchers = $this->matchersMap[$i];
            return $rule['match'];
        }

        return false;
    }
}
