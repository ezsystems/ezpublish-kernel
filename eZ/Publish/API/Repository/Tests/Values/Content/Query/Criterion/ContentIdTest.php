<?php
/**
 * File containing the ContentId class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Tests\Values\Content\Query\CriterionTest;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class ContentId extends CriterionTest
{
    public function providerForTestToString()
    {
        return array(
            array( new Criterion\ContentId( 1 ), 'contentId = 1' ),
            array( new Criterion\ContentId( array( 1, 2, 3 ) ), 'contentId IN (1, 2, 3)' )
        );
    }
}
