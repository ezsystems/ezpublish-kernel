<?php
/**
 * String Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\Type\Type,
    ezp\Content\Type\Field as TypeField;

/**
 * String Field value object class
 */
class String extends TypeField
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezstring';

    /**
     * @public
     * @var string
     */
    public $default = '';

    /**
     * @public
     * @var int
     */
    public $maxLength = 255;

    /**
     * @return void
     */
    public function __construct( Type $contentType )
    {
        $this->readableProperties['default'] = true;
        $this->readableProperties['maxLength'] = true;
        TypeField::__construct( $contentType );
    }
}
