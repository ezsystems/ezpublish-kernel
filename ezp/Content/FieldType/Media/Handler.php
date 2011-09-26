<?php
/**
 * File containing the Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Media;
use ezp\Content\FieldType\Value as ValueInterface,
    ezp\Content\FieldType\BinaryFile\Handler as BinaryFileHandler,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Io\BinaryFile,
    ezp\Io\ContentType,
    ezp\Io\SysInfo,
    ezp\Io\FileInfo,
    ezp\Base\BinaryRepository;

/**
 * Media file handler
 */
class Handler extends BinaryFileHandler
{
}
