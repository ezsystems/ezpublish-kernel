<?php
/**
 * XML Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\Type,
    ezp\Content\Type\Field as TypeField;

/**
 * XML Field value object class
 */
class Xml extends Text
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezxmlstring';

    /**
     * @public
     * @var string
     */
    public $tagPreset = '';

    /**
     * @var int
     */
    public $columns = 10;

    /**
     * @return void
     */
    public function __construct( Type $contentType )
    {
        $this->readableProperties['tagPreset'] = true;
        $this->readableProperties['columns'] = true;
        TypeField::__construct( $contentType );
    }
}
