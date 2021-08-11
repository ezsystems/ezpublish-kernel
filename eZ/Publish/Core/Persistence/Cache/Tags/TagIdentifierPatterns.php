<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tags;

/**
 * @internal
 */
final class TagIdentifierPatterns
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
    public const CUSTOM_SUFFIX = '-c';

    public const BY_GROUP = 'bg-%s';
    public const ON_CONTENT = 'oc-%s';

    public const BOOKMARK = 'b-%s';
    public const PREFIXED_BOOKMARK = self::PREFIX . self::BOOKMARK;

    public const CONTENT = 'c-%s';
    public const PREFIXED_CONTENT = self::PREFIX . self::CONTENT;
    public const CONTENT_FIELDS_TYPE = 'cft-%s';
    public const CONTENT_INFO = 'ci-%s';
    public const PREFIXED_CONTENT_INFO = self::PREFIX . self::CONTENT_INFO;
    public const CONTENT_INFO_BY_REMOTE_ID = 'cibri-%s';
    public const PREFIXED_CONTENT_INFO_BY_REMOTE_ID = self::PREFIX . self::CONTENT_INFO_BY_REMOTE_ID;
    public const PREFIXED_CONTENT_LOCATIONS = self::PREFIX . 'cl-%s';
    public const CONTENT_VERSION_INFO = 'cvi-%s';
    public const PREFIXED_CONTENT_VERSION_INFO = self::PREFIX . self::CONTENT_VERSION_INFO;
    public const PREFIXED_CONTENT_VERSION_LIST = self::PREFIX . 'c-%s-vl';
    public const CONTENT_VERSION = self::CONTENT . '-' . self::VERSION;

    public const CONTENT_TYPE = 'ct-%s';
    public const PREFIXED_CONTENT_TYPE = self::PREFIX . self::CONTENT_TYPE;
    public const PREFIXED_CONTENT_TYPE_FIELD_MAP = self::PREFIX . 'ctfm';
    public const CONTENT_TYPE_GROUP = 'ctg-%s';
    public const PREFIXED_CONTENT_TYPE_GROUP = self::PREFIX . self::CONTENT_TYPE_GROUP;
    public const PREFIXED_CONTENT_TYPE_GROUP_WITH_ID_SUFFIX = self::PREFIXED_CONTENT_TYPE_GROUP . self::BY_IDENTIFIER_SUFFIX;
    public const PREFIXED_CONTENT_TYPE_GROUP_WITH_BY_REMOTE_SUFFIX = self::PREFIXED_CONTENT_TYPE_GROUP . self::BY_REMOTE_SUFFIX;
    public const PREFIXED_CONTENT_TYPE_GROUP_LIST = self::PREFIX . 'ctgl-%s';
    public const PREFIXED_CONTENT_TYPE_LIST_BY_GROUP = self::PREFIX . 'ctlbg-%s';

    public const PREFIXED_IMAGE_VARIATION = self::PREFIX . 'ig';
    public const IMAGE_VARIATION_NAME = 'ign-%s';
    public const IMAGE_VARIATION_SITEACCESS = 'igs-%s';
    public const IMAGE_VARIATION_CONTENT = 'igc-%s';
    public const IMAGE_VARIATION_FIELD = 'igf-%s';

    public const LANGUAGE = 'la-%s';
    public const PREFIXED_LANGUAGE = self::PREFIX . self::LANGUAGE;
    public const LANGUAGE_CODE = 'lac-%s';
    public const PREFIXED_LANGUAGE_CODE = self::PREFIX . self::LANGUAGE_CODE;
    public const LANGUAGE_LIST = 'lal';
    public const PREFIXED_LANGUAGE_LIST = self::PREFIX . self::LANGUAGE_LIST;

    public const LOCATION = 'l-%s';
    public const PREFIXED_LOCATION = self::PREFIX . self::LOCATION;
    public const LOCATION_PATH = 'lp-%s';
    public const PREFIXED_LOCATION_REMOTE_ID = self::PREFIX . 'lri';
    public const PREFIXED_LOCATION_SUBTREE = self::PREFIX . 'ls';
    public const PREFIXED_CONTENT_LOCATIONS_WITH_PARENT_FOR_DRAFT_SUFFIX = self::PREFIXED_CONTENT_LOCATIONS . self::PARENT_FOR_DRAFT_SUFFIX;

    public const PREFIXED_NOTIFICATION = self::PREFIX . 'n-%s';
    public const PREFIXED_NOTIFICATION_COUNT = self::PREFIX . 'nc-%s';
    public const PREFIXED_NOTIFICATION_PENDING_COUNT = self::PREFIX . 'npc-%s';

    public const POLICY = 'p-%s';
    public const ROLE = 'r-%s';
    public const PREFIXED_ROLE = self::PREFIX . self::ROLE;
    public const ROLE_ASSIGNMENT = 'ra-%s';
    public const ROLE_ASSIGNMENT_GROUP_LIST = 'ragl-%s';
    public const ROLE_ASSIGNMENT_ROLE_LIST = 'rarl-%s';
    public const PREFIXED_ROLE_WITH_BY_ID_SUFFIX = self::PREFIX . self::ROLE . self::BY_IDENTIFIER_SUFFIX;
    public const PREFIXED_ROLE_ASSIGNMENT = self::PREFIX . self::ROLE_ASSIGNMENT;
    public const PREFIXED_ROLE_ASSIGNMENT_WITH_BY_ROLE_SUFFIX = self::PREFIXED_ROLE_ASSIGNMENT . self::BY_ROLE_SUFFIX;
    public const PREFIXED_ROLE_ASSIGNMENT_WITH_BY_GROUP_INHERITED_SUFFIX = self::PREFIXED_ROLE_ASSIGNMENT . self::BY_GROUP_INHERITED_SUFFIX;
    public const PREFIXED_ROLE_ASSIGNMENT_WITH_BY_GROUP_SUFFIX = self::PREFIXED_ROLE_ASSIGNMENT . self::BY_GROUP_SUFFIX;

    public const SECTION = 'se-%s';
    public const PREFIXED_SECTION = self::PREFIX . self::SECTION;
    public const PREFIXED_SECTION_WITH_BY_ID = self::PREFIXED_SECTION . self::BY_IDENTIFIER_SUFFIX;

    public const STATE = 's-%s';
    public const PREFIXED_STATE = self::PREFIX . self::STATE;
    public const STATE_BY_GROUP = 'sbg-%s';
    public const STATE_GROUP = 'sg-%s';
    public const PREFIXED_STATE_GROUP = self::PREFIX . self::STATE_GROUP;
    public const PREFIXED_STATE_GROUP_WITH_ID_SUFFIX = self::PREFIXED_STATE_GROUP . self::BY_IDENTIFIER_SUFFIX;
    public const PREFIXED_STATE_GROUP_ALL = self::PREFIX . 'sga';
    public const PREFIXED_STATE_IDENTIFIER = self::PREFIX . 'si-%s';
    public const PREFIXED_STATE_IDENTIFIER_WITH_BY_GROUP_SUFFIX = self::PREFIXED_STATE_IDENTIFIER . self::BY_GROUP_SUFFIX . '-%s';
    public const PREFIXED_STATE_LIST_BY_GROUP = self::PREFIX . 'slbg-%s';
    public const PREFIXED_STATE_BY_GROUP_ON_CONTENT = self::PREFIX . self::STATE_BY_GROUP . self::ON_CONTENT_SUFFIX . '-%s';
    public const PREFIXED_STATE_BY_GROUP = self::PREFIX . self::STATE_BY_GROUP;

    public const USER = 'u-%s';
    public const PREFIXED_USER = self::PREFIX . self::USER;
    public const PREFIXED_USER_WITH_BY_LOGIN_SUFFIX = self::PREFIXED_USER . self::BY_LOGIN_SUFFIX;
    public const PREFIXED_USER_WITH_BY_EMAIL_SUFFIX = self::PREFIXED_USER . self::BY_EMAIL_SUFFIX;
    public const USER_WITH_ACCOUNT_KEY_SUFFIX = self::USER . self::ACCOUNT_KEY_SUFFIX;
    public const PREFIXED_USER_WITH_BY_ACCOUNT_KEY_SUFFIX = self::PREFIXED_USER . self::BY_ACCOUNT_KEY_SUFFIX;

    public const URL = 'url-%s';
    public const PREFIXED_URL = self::PREFIX . self::URL;
    public const URL_ALIAS = 'urla-%s';
    public const URL_ALIAS_WITH_HASH = self::URL_ALIAS . '-%s';
    public const PREFIXED_URL_ALIAS = self::PREFIX . self::URL_ALIAS;
    public const URL_ALIAS_CUSTOM = 'urlac-%s';
    public const URL_ALIAS_LOCATION = 'urlal-%s';
    public const PREFIXED_URL_ALIAS_LOCATION_LIST = self::PREFIX . 'urlall-%s';
    public const PREFIXED_URL_ALIAS_LOCATION_LIST_CUSTOM = self::PREFIXED_URL_ALIAS_LOCATION_LIST . self::CUSTOM_SUFFIX;
    public const URL_ALIAS_LOCATION_PATH = 'urlalp-%s';
    public const URL_ALIAS_NOT_FOUND = 'urlanf';
    public const PREFIXED_URL_ALIAS_URL = self::PREFIX . 'urlau-%s';

    public const URL_WILDCARD = 'urlw-%s';
    public const PREFIXED_URL_WILDCARD = self::PREFIX . self::URL_WILDCARD;
    public const URL_WILDCARD_NOT_FOUND = 'urlwnf';
    public const PREFIXED_URL_WILDCARD_SOURCE = self::PREFIX . 'urlws-%s';

    public const PREFIXED_USER_PREFERENCE = self::PREFIX . 'up';
    public const PREFIXED_USER_PREFERENCE_WITH_SUFFIX = self::PREFIX . 'up-%s-%s';

    public const TYPE = 't-%s';
    public const TYPE_WITHOUT_VALUE = 't';
    public const TYPE_GROUP = 'tg-%s';
    public const TYPE_MAP = 'tm';

    public const VERSION = 'v-%s';
}
