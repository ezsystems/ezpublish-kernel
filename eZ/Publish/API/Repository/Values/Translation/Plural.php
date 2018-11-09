<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Plural class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Translation;

use eZ\Publish\API\Repository\Values\Translation;

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
 * This interface follows the interfaces of XLiff, gettext, Symfony2
 * Translations and Zend_Translate. For singular forms you just provide a plain
 * string (with optional placeholders without effects on the plural forms). For
 * potential plural forms you always provide a singular variant and an english
 * simple plural variant. No implementation supports multiple different plural
 * forms in one single message.
 *
 * The singular / plural string could, for Symfony2, for example be converted
 * to "$singular|$plural", and you would call gettext like: ngettext(
 * $singular, $plural, $count ).
 */
class Plural extends Translation
{
    /**
     * Singular string. Might use replacements like %foo%, which are replaced by
     * the values specified in the values array.
     *
     * @var string
     */
    protected $singular;

    /**
     * Message string. Might use replacements like %foo%, which are replaced by
     * the values specified in the values array.
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
     * Construct plural message from singular, plural and value array.
     *
     * @param string $singular
     * @param string $plural
     * @param array $values
     */
    public function __construct($singular, $plural, array $values)
    {
        $this->singular = $singular;
        $this->plural = $plural;
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return strtr(current($this->values) == 1 ? $this->plural : $this->singular, $this->values);
    }
}
