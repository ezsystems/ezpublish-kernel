<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\MultiLanguageUpdateStructBase;

/**
 * This class is used to provide data for updating a section. At least one property has to set.
 *
 * @property-write string $name - BC: if only $identifier (from base class) and this $name is specified, then the attribute
 *                 $names['eng-GB'] will be set in the base class instead.
 */
class SectionUpdateStruct extends MultiLanguageUpdateStructBase
{

}
