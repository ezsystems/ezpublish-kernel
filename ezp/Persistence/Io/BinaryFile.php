<?php
/**
 * File containing the BinaryFile class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_io
 */

namespace ezp\Persistence\Io;

/**
 * @package ezp
 * @subpackage persistence_io
 */
class BinaryFile
{
    /**
     * Name of the file
     *
     * @note This might just be a hash
     * @var string
     */
    private $fileName;

    /**
     * Orginal name of the file during the upload
     *
     * @var string
     */
    private $originalFilename;

    /**
     * Mime type of the file
     *
     * @var string
     */
    private $contentType;

    /**
     * Version for the content object the file is associated with
     *
     * @var int
     */
    private $version;
}
?>
