<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\Type\Type,
    ezp\Content\Type\Field as TypeField;

/**
 * Int Field value object class
 */
class Int extends TypeField
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezinteger';

    /**
     * @var int
     */
    public $default = 0;

    /**
     * @var int
     */
    public $min = 0;

    /**
     * @var int
     */
    public $max = 0;

    /**
     * @var int
     */
    public $state = 0;

    /**
     * @return void
     */
    public function __construct( Type $contentType )
    {
        $this->readableProperties['min'] = true;
        $this->readableProperties['max'] = true;
        $this->readableProperties['default'] = true;
        $this->readableProperties['state'] = true;
        TypeField::__construct( $contentType );
    }
}
