<?php
/**
 * File containing ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content
 */
namespace ezp\Content;
class ContentType extends Base implements ContentDomainInterface
{
    /**
     * Returns an instance of ContentType by its $identifier (e.g. "folder")
     * <code>
     * use \ezp\Content\ContentType;
     * use \ezp\Content\Content;
     *
     * $contentType = ContentType::byIdentifier( "folder" );
     * $content = new Content( $contentType );
     * </code>
     * @param string $identifier The content type identifier
     * @return ezp\Content\ContentType
     */
    public static function byIdentifier( $identifier )
    {
        $contentRepo = Repository::get()->getContentService()->loadContentTypeByIdentifier( $identifier );
    }
}
?>
