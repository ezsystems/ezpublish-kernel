<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

final class TagIdentifiers
{
    public const PREFIX = 'ez-';

    public const BY_IDENTIFIER_SUFFIX = '-bi';
    public const BY_REMOTE_SUFFIX = '-br';
    public const PARENT_FOR_DRAFT_SUFFIX = '-pfd';
    public const BY_LOGIN_SUFFIX = '-bl';
    public const BY_EMAIL_SUFFIX = '-be';
    public const BY_ACCOUNT_KEY_SUFFIX = '-bak';
    public const ACCOUNT_KEY_SUFFIX = '-ak';
    public const BY_ROLE_SUFFIX = '-bro';
    public const BY_GROUP_INHERITED_SUFFIX = '-bgi';
    public const BY_GROUP_SUFFIX = '-bg';
    public const ON_CONTENT_SUFFIX = '-oc';

    public const ALIAS = 'a';

    public const BOOKMARK = 'b';

    public const CONTENT = 'c';
    public const CONTENT_FIELDS_TYPE = 'cft';
    public const CONTENT_INFO = 'ci';
    public const CONTENT_INFO_BY_REMOTE_ID = 'cibri';
    public const CONTENT_LOCATIONS = 'cl';
    public const CONTENT_VERSION_INFO = 'cvi';

    public const CONTENT_TYPE = 'ct';
    public const CONTENT_TYPE_FIELD_MAP = 'ctfm';
    public const CONTENT_TYPE_GROUP = 'ctg';
    public const CONTENT_TYPE_GROUP_LIST = 'ctgl';
    public const CONTENT_TYPE_LIST_BY_GROUP = 'ctlbg';

    public const IMAGE_VARIATION = 'ig';
    public const IMAGE_VARIATION_NAME = 'ign';
    public const IMAGE_VARIATION_SITEACCESSS = 'igs';
    public const IMAGE_VARIATION_CONTENT = 'igc';
    public const IMAGE_VARIATION_FIELD = 'igf';

    public const LANGUAGE = 'la';
    public const LANGUAGE_CODE = 'lac';
    public const LANGUAGE_LIST = 'lal';

    public const LOCATION = 'l';
    public const LOCATION_PATH = 'lp';
    public const LOCATION_REMOTE_ID = 'lri';
    public const LOCATION_SUBTREE = 'ls';

    public const NOTIFICATION = 'n';
    public const NOTIFICATION_COUNT = 'nc';
    public const NOTIFICATION_PENDING_COUNT = 'npc';

    public const POLICY = 'p';
    public const ROLE = 'r';
    public const ROLE_ASSIGNMENT = 'ra';
    public const ROLE_ASSIGNMENT_GROUP_LIST = 'ragl';
    public const ROLE_ASSIGNMENT_ROLE_LIST = 'rarl';

    public const SECTION = 'se';

    public const STATE = 's';
    public const STATE_BY_GROUP = 'sbg';
    public const STATE_GROUP = 'sg';
    public const STATE_GROUP_ALL = 'sga';
    public const STATE_IDENTIFIER = 'si';
    public const STATE_LIST_BY_GROUP = 'slbg';

    public const USER = 'u';
    
    public const URL = 'url';
    public const URL_ALIAS = 'urla';
    public const URL_ALIAS_CUSTOM = 'urlac';
    public const URL_ALIAS_LOCATION = 'urlal';
    public const URL_ALIAS_LOCATION_LIST = 'urlall';
    public const URL_ALIAS_LOCATION_PATH = 'urlalp';
    public const URL_ALIAS_NOT_FOUND = 'urlanf';
    public const URL_ALIAS_URL = 'urlau';

    public const URL_WILDCARD = 'urlw';
    public const URL_WILDCARD_NOT_FOUND = 'urlwnf';
    public const URL_WILDCARD_SOURCE = 'urlws';

    public const USER_PREFERENCE = 'up';

    public const TYPE = 't';
    public const TYPE_GROUP = 'tg';
    public const TYPE_MAP = 'tm';

    public const VERSION = 'v';
    public const VERSION_LIST = 'vl';
}
