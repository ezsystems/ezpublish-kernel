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

    public const CONTENT = 'c-%s';
    public const CONTENT_FIELDS_TYPE = 'cft-%s';
    public const CONTENT_INFO = 'ci-%s';
    public const CONTENT_INFO_BY_REMOTE_ID = 'cibri-%s';
    public const CONTENT_LOCATIONS = 'cl-%s';
    public const CONTENT_VERSION_INFO = 'cvi-%s';
    public const CONTENT_VERSION_LIST = 'c-%s-vl';
    public const CONTENT_VERSION = self::CONTENT . '-' . self::VERSION;

    public const CONTENT_TYPE = 'ct-%s';
    public const CONTENT_TYPE_FIELD_MAP = 'ctfm';
    public const CONTENT_TYPE_GROUP = 'ctg-%s';
    public const CONTENT_TYPE_GROUP_WITH_ID_SUFFIX = self::CONTENT_TYPE_GROUP . self::BY_IDENTIFIER_SUFFIX;
    public const CONTENT_TYPE_GROUP_WITH_BY_REMOTE_SUFFIX = self::CONTENT_TYPE_GROUP . self::BY_REMOTE_SUFFIX;
    public const CONTENT_TYPE_GROUP_LIST = 'ctgl-%s';
    public const CONTENT_TYPE_LIST_BY_GROUP = 'ctlbg-%s';

    public const IMAGE_VARIATION = 'ig';
    public const IMAGE_VARIATION_NAME = 'ign-%s';
    public const IMAGE_VARIATION_SITEACCESS = 'igs-%s';
    public const IMAGE_VARIATION_CONTENT = 'igc-%s';
    public const IMAGE_VARIATION_FIELD = 'igf-%s';

    public const LANGUAGE = 'la-%s';
    public const LANGUAGE_CODE = 'lac-%s';
    public const LANGUAGE_LIST = 'lal';

    public const LOCATION = 'l-%s';
    public const LOCATION_PATH = 'lp-%s';
    public const LOCATION_REMOTE_ID = 'lri';
    public const LOCATION_SUBTREE = 'ls';
    public const CONTENT_LOCATIONS_WITH_PARENT_FOR_DRAFT_SUFFIX = self::CONTENT_LOCATIONS . self::PARENT_FOR_DRAFT_SUFFIX;

    public const NOTIFICATION = 'n-%s';
    public const NOTIFICATION_COUNT = 'nc-%s';
    public const NOTIFICATION_PENDING_COUNT = 'npc-%s';

    public const POLICY = 'p-%s';
    public const ROLE = 'r-%s';
    public const ROLE_ASSIGNMENT = 'ra-%s';
    public const ROLE_ASSIGNMENT_GROUP_LIST = 'ragl-%s';
    public const ROLE_ASSIGNMENT_ROLE_LIST = 'rarl-%s';
    public const ROLE_WITH_BY_ID_SUFFIX = self::ROLE . self::BY_IDENTIFIER_SUFFIX;
    public const ROLE_ASSIGNMENT_WITH_BY_ROLE_SUFFIX = self::ROLE_ASSIGNMENT . self::BY_ROLE_SUFFIX;
    public const ROLE_ASSIGNMENT_WITH_BY_GROUP_INHERITED_SUFFIX = self::ROLE_ASSIGNMENT . self::BY_GROUP_INHERITED_SUFFIX;
    public const ROLE_ASSIGNMENT_WITH_BY_GROUP_SUFFIX = self::ROLE_ASSIGNMENT . self::BY_GROUP_SUFFIX;

    public const SECTION = 'se-%s';
    public const SECTION_WITH_BY_ID = self::SECTION . self::BY_IDENTIFIER_SUFFIX;

    public const STATE = 's-%s';
    public const STATE_BY_GROUP = 'sbg-%s';
    public const STATE_GROUP = 'sg-%s';
    public const STATE_GROUP_WITH_ID_SUFFIX = self::STATE_GROUP . self::BY_IDENTIFIER_SUFFIX;
    public const STATE_GROUP_ALL = 'sga';
    public const STATE_IDENTIFIER = 'si-%s';
    public const STATE_IDENTIFIER_WITH_BY_GROUP_SUFFIX = self::STATE_IDENTIFIER . self::BY_GROUP_SUFFIX . '-%s';
    public const STATE_LIST_BY_GROUP = 'slbg-%s';
    public const STATE_BY_GROUP_ON_CONTENT = self::STATE_BY_GROUP . self::ON_CONTENT_SUFFIX . '-%s';

    public const USER = 'u-%s';
    public const USER_WITH_BY_LOGIN_SUFFIX = self::USER . self::BY_LOGIN_SUFFIX;
    public const USER_WITH_BY_EMAIL_SUFFIX = self::USER . self::BY_EMAIL_SUFFIX;
    public const USER_WITH_ACCOUNT_KEY_SUFFIX = self::USER . self::ACCOUNT_KEY_SUFFIX;
    public const USER_WITH_BY_ACCOUNT_KEY_SUFFIX = self::USER . self::BY_ACCOUNT_KEY_SUFFIX;

    public const URL = 'url-%s';
    public const URL_ALIAS = 'urla-%s';
    public const URL_ALIAS_WITH_HASH = self::URL_ALIAS . '-%s';
    public const URL_ALIAS_CUSTOM = 'urlac-%s';
    public const URL_ALIAS_LOCATION = 'urlal-%s';
    public const URL_ALIAS_LOCATION_LIST = 'urlall-%s';
    public const URL_ALIAS_LOCATION_LIST_CUSTOM = self::URL_ALIAS_LOCATION_LIST . self::CUSTOM_SUFFIX;
    public const URL_ALIAS_LOCATION_PATH = 'urlalp-%s';
    public const URL_ALIAS_NOT_FOUND = 'urlanf';
    public const URL_ALIAS_URL = 'urlau-%s';

    public const URL_WILDCARD = 'urlw-%s';
    public const URL_WILDCARD_NOT_FOUND = 'urlwnf';
    public const URL_WILDCARD_SOURCE = 'urlws-%s';

    public const USER_PREFERENCE = 'up';
    public const USER_PREFERENCE_WITH_SUFFIX = 'up-%s-%s';

    public const TYPE = 't-%s';
    public const TYPE_WITHOUT_VALUE = 't';
    public const TYPE_GROUP = 'tg-%s';
    public const TYPE_MAP = 'tm';

    public const VERSION = 'v-%s';
}
