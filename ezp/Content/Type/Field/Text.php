<?php
/**
 * Image Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\Type,
    ezp\Content\Type\FieldDefinition;

/**
 * Image Field value object class
 */
class Text extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'eztext';

    /**
     * @var string
     */
    public $default = '';

    /**
     * @var int
     */
    public $columns = 10;

    /**
     * @return void
     */
    public function __construct( Type $contentType )
    {
        $this->readWriteProperties['default'] = true;
        $this->readWriteProperties['columns'] = true;
        TypeField::__construct( $contentType );
    }
}
