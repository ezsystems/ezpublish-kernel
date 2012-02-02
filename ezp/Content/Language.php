<?php
/**
 * File containing the ezp\Content\Language class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\ModelDefinition,
    eZ\Publish\SPI\Persistence\Content\Language as LanguageValue;

/**
 * This class represents a Language object
 *
 * @property-read integer $id
 *                The ID, automatically assigned by the persistence layer
 * @property string $locale
 *                Locale for this Language object
 * @property string $name
 *                Human readable name of the language
 * @property bool $isEnabled
 *                Defines if language is enabled or not.
 */
class Language extends Model implements ModelDefinition
{
    /**
     * @inherit-doc
     * @var array
     */
    protected $readWriteProperties = array(
        'id' => false,
        'locale' => true,
        'name' => true,
        'isEnabled' => true,
    );

    /**
     * Constructor, setups all internal objects.
     */
    public function __construct()
    {
        $this->properties = new LanguageValue();
    }

    /**
     * Returns definition of the language object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return array(
            'module' => 'content',
            'functions' => array(
                'translations' => array(),
            ),
        );
    }
}

?>
