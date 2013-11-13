<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Section class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\MultiLanguageValueBase;

/**
 * This class represents a section
 *
 * @property-read mixed $id the id of the section
 * @property-read string $name the name of the section in the main language i.e. getName( $mainLanguageCode )
 */
abstract class Section extends MultiLanguageValueBase
{
    /**
     * Id of the section
     *
     * @var mixed
     */
    protected $id;

}
