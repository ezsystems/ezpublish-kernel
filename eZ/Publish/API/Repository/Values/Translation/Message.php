<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Message class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository\Values
 */

namespace eZ\Publish\API\Repository\Values\Translation;

use eZ\Publish\API\Repository\Values\Translation;

/**
 * Class for translatable messages, which only occur in singular form.
 *
 * The message might include replacements, in the form %[A-Za-z]%. Those are
 * replaced by the values provided. A raw % can be escaped like %%.
 *
 * @package eZ\Publish\API\Repository\Values
 */
class Message extends Translation
{
    /**
     * Message string. Might use replacements like %foo%, which are replaced by
     * the values specified in the values array.
     *
     * @var string
     */
    protected $message;

    /**
     * Translation value objects. May not contain any numbers, which might
     * result in requiring plural forms. Use Plural for that.
     *
     * @var array
     */
    protected $values;

    /**
     * Construct singular only message from string and optional value array
     *
     * @param string $message
     * @param array $values
     *
     * @return void
     */
    public function __construct( $message, array $values = array() )
    {
        $this->message = $message;
        $this->values  = $values;
    }
}

