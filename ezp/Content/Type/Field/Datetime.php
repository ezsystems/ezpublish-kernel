<?php
/**
 * Relation Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\Type,
    ezp\Content\Type\Field as TypeField;

/**
 * Relation Field value object class
 */
class Datetime extends Int
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezdatetime';

    /**
     * @var int
     */
    public $default = 0;

    /**
     * @var int
     */
    public $useSeconds = 0;

    /**
     * @var string
     */
    public $adjustment = 0;

    /**
     * @return void
     */
    public function __construct( Type $contentType )
    {
        $this->readWriteProperties['default'] = true;
        $this->readWriteProperties['useSeconds'] = true;
        $this->readWriteProperties['adjustment'] = true;
        TypeField::__construct( $contentType );
    }
}
