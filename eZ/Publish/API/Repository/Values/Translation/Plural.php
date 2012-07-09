<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Plural class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository\Values
 */

namespace eZ\Publish\API\Repository\Values\Translation;

/**
 * Class for translatable messages, which may contain plural forms.
 *
 * The message might include replacements, in the form %[A-Za-z]%. Those are
 * replaced by the values provided. A raw % can be escaped like %%.
 *
 * You need to provide a singular and plural variant for the string. The
 * strings provided should be english and will be translated depending on the
 * environment language.
 *
 * @package eZ\Publish\API\Repository\Values
 */
class Plural extends Translation
{
    /**
     * Singular string. Might use replacements like %foo%, which are replaced by 
     * the values specfied in the values array.
     *
     * @var string
     */
    protected $singular;

    /**
     * Message string. Might use replacements like %foo%, which are replaced by 
     * the values specfied in the values array.
     *
     * @var string
     */
    protected $plural;

    /**
     * Translation value objects. May not contain any numbers, which might
     * result in requiring plural forms. Use MessagePlural for that.
     *
     * @var array
     */
    protected $values;

    /**
     * Construct singular only message from string and optional value array
     *
     * @param string $singular
     * @param string $plural
     * @param array $values
     * @return void
     */
    public function __construct( $singular, $plural, array $values = array() )
    {
        $this->singular = $singular;
        $this->plural   = $plural;
        $this->values   = $values;
    }
}

