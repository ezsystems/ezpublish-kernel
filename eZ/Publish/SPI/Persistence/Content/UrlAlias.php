<?php
/**
 * File containing the UrlAlias class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence;

/**
 * UrlAlias models one url alias path element separated by '/' in urls.
 *
 * This class models the legacy structure used for url aliases.
 */
class UrlAlias extends ValueObject
{
    /**
     * The action of the url alias path element.
     *
     * The action consists of a encoded values as action-type:id.
     *
     * Example:
     * <code>
     * eznode:2
     * </code>
     *
     * This action would point to Location with id=2.
     *
     * Action-type can be either 'nop:', 'eznode:' or 'ezmodule:'.
     *
     * @var string
     */
    public $action;

    /**
     * Specifies the action type of the url alias path element.
     *
     * Value can be one of 'nop', 'eznode' or 'ezmodule'.
     *
     * @var string
     */
    public $action_type;

    /**
     * Whether an alias should redirect to its destination.
     *
     * Read: HTTP 302 redirection.
     *
     * @var int
     */
    public $alias_redirects;

    /**
     * Id of url alias element.
     *
     * @var int
     */
    public $id;

    /**
     * Flag signifying a custom made pointer.
     *
     * @var int
     */
    public $is_alias;

    /**
     * Flag signifying pointer to a live existing entity.
     *
     * @var int
     */
    public $is_original;

    /**
     * Lanuage mask of url alias entry.
     *
     * Contains represented languages encoded into integer bit field.
     *
     * @var int
     */
    public $lang_mask;

    /**
     * Pointer to other url alias element.
     *
     * Used to dereference history chain of url alias elements.
     *
     * @var int
     */
    public $link;

    /**
     * Specifies the parent of this url alias element.
     *
     * @var int
     */
    public $parent;

    /**
     * The actual text of the url alias element.
     *
     * @var string
     */
    public $text;

    /**
     * MD5 hash of lowercase {@link $text}.
     *
     * @var string
     */
    public $text_md5;
}
