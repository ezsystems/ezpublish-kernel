<?php
/**
 * Float Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\Type,
    ezp\Content\Type\Field as TypeField;

/**
 * Float Field value object class
 */
class Float extends TypeField
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezfloat';

    /**
     * @var float
     */
    public $default = 0.0;

    /**
     * @var float
     */
    public $min = 0.0;

    /**
     * @var float
     */
    public $max = 0.0;

    /**
     * @var float
     */
    public $state = 0;

    /**
     * @return void
     */
    public function __construct( Type $contentType )
    {
        $this->readWriteProperties['min'] = true;
        $this->readWriteProperties['max'] = true;
        $this->readWriteProperties['default'] = true;
        $this->readWriteProperties['state'] = true;
        TypeField::__construct( $contentType );
    }
}
