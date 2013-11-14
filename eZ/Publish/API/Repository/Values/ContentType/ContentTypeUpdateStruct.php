<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\MultiLanguageUpdateStructBase;

/**
 * This class is used for updating a content type
 */
class ContentTypeUpdateStruct extends MultiLanguageUpdateStructBase
{

    /**
     * If set the remote ID is changed to this value
     *
     * @var string
     */
    public $remoteId;

    /**
     * If set the URL alias schema is changed to this value
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * If set the name schema is changed to this value
     *
     * @var string
     */
    public $nameSchema;

    /**
     * If set the container fllag is set to this value
     *
     * @var boolean
     */
    public $isContainer;

    /**
     * If set the default sort field is changed to this value
     *
     * @var mixed
     */
    public $defaultSortField;

    /**
     * If set the default sort order is set to this value
     *
     * @var mixed
     */
    public $defaultSortOrder;

    /**
     * If set the default always available flag is set to this value
     *
     * @var boolean
     */
    public $defaultAlwaysAvailable;

    /**
     * If set this value overrides the current user as creator
     *
     * @var mixed
     */
    public $modifierId = null;

    /**
     * If set this value overrides the current time for creation
     *
     * @var \DateTime
     */
    public $modificationDate = null;

}
