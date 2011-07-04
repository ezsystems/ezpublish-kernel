<?php
/**
 * File containing the ezp\content\Translation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Content Translation
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class Translation extends \ezp\base\AbstractModel
{

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'versions' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'localeCode' => false,
        'contentId' => false,
        'fields' => false,
        'last' => false,
    );


    /**
     * Content
     *
     * @var Content
     */
    public $content;

    /**
     * Locale object
     *
     * @var \ezp\base\Locale
     */
    public $locale;

    /**
     * Existing Version in the Translation
     *
     * @var Version[]
     */
    protected $versions;

    public function __construct( \ezp\base\Locale $locale, Content $content )
    {
        $this->locale = $locale;
        $this->versions = new \ezp\base\TypeCollection( '\ezp\content\Version' );
        $this->content = $content;
    }

    /**
     * Returns the content id
     *
     * @return int
     */
    protected function getContentId()
    {
        return $this->content->id;
    }

    /**
     * Returns the locale code (eg eng-GB, fre-FR, ...)
     *
     * @return string
     */
    protected function getLocaleCode()
    {
        return $this->locale->code;
    }

    /**
     * Returns the last version added to the translation
     *
     * @return Version
     */
    protected function getLast()
    {
        return $this->versions[count( $this->versions ) - 1];
    }

    /**
     * Returns the field collection in the last version added to the
     * translation
     *
     * @return FieldCollection
     */
    protected function getFields()
    {
        return $this->getLast()->fields;
    }
}
?>
