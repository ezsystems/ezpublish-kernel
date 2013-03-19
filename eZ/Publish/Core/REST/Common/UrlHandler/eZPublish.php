<?php
/**
 * File containing the eZPublish UrlHandler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\UrlHandler;

/**
 * Pattern based URL Handler pre-configured for eZ Publish
 */
class eZPublish extends Pattern
{
    /**
     * Map of URL types to their URL patterns
     *
     * @var array
     * @todo: Add sensible missing names
     */
    protected $map = array(
        'root'                      => '/',
        'locations'                 => '/content/locations',
        'locationByRemote'          => '/content/locations?remoteId={location}',
        'locationById'              => '/content/locations?id={location}',
        'locationChildren'          => '/content/locations{&location}/children',
        'locationUrlAliases'        => '/content/locations{&location}/urlaliases',
        'location'                  => '/content/locations{&location}',
        'objects'                   => '/content/objects',
        'objectByRemote'            => '/content/objects?remoteId={object}',
        'object'                    => '/content/objects/{object}',
        'objectByLangCode'          => '/content/objects/{object}/{lang_code}',
        'objectLocations'           => '/content/objects/{object}/locations',
        'objectObjectStates'        => '/content/objects/{object}/objectstates',
        'objectVersions'            => '/content/objects/{object}/versions',
        'objectVersion'             => '/content/objects/{object}/versions/{version}',
        'objectVersionRelations'    => '/content/objects/{object}/versions/{version}/relations',
        'objectVersionRelation'     => '/content/objects/{object}/versions/{version}/relations/{relation}',
        'objectCurrentVersion'      => '/content/objects/{object}/currentversion',
        'objectrelations'           => '/content/objects/{object}/relations',
        'objectrelation'            => '/content/objects/{object}/relations/{relation}',
        'objectstategroups'         => '/content/objectstategroups',
        'objectstategroup'          => '/content/objectstategroups/{objectstategroup}',
        'objectstates'              => '/content/objectstategroups/{objectstategroup}/objectstates',
        'objectstate'               => '/content/objectstategroups/{objectstategroup}/objectstates/{objectstate}',
        'sections'                  => '/content/sections',
        'section'                   => '/content/sections/{section}',
        'sectionByIdentifier'       => '/content/sections?identifier={section}',
        'trashItems'                => '/content/trash',
        'trash'                     => '/content/trash/{trash}',
        'typegroups'                => '/content/typegroups',
        'typegroupByIdentifier'     => '/content/typegroups?identifier={&typegroup}',
        'typegroup'                 => '/content/typegroups/{typegroup}',
        'grouptypes'                => '/content/typegroups/{typegroup}/types',
        'types'                     => '/content/types',
        'typeByIdentifier'          => '/content/types?identifier={type}',
        'typeByRemoteId'            => '/content/types?remoteId={type}',
        'type'                      => '/content/types/{type}',
        'typeFieldDefinitions'      => '/content/types/{type}/fieldDefinitions',
        'typeFieldDefinition'       => '/content/types/{type}/fieldDefinitions/{fieldDefinition}',
        'typeDraft'                 => '/content/types/{type}/draft',
        'typeFieldDefinitionsDraft' => '/content/types/{type}/draft/fieldDefinitions',
        'typeFieldDefinitionDraft'  => '/content/types/{type}/draft/fieldDefinitions/{fieldDefinition}',
        'groupsOfType'              => '/content/types/{type}/groups',
        'typeGroupAssign'           => '/content/types/{type}/groups?group={&group}',
        'groupOfType'               => '/content/types/{type}/groups/{group}',
        'urlWildcards'              => '/content/urlwildcards',
        'urlWildcard'               => '/content/urlwildcards/{urlwildcard}',
        'urlAliases'                => '/content/urlaliases',
        'urlAlias'                  => '/content/urlaliases/{urlalias}',
        'views'                     => '/content/views',
        'view'                      => '/content/views/{view}',
        'viewResults'               => '/content/views/{view}/results',
        'groups'                    => '/user/groups',
        'group'                     => '/user/groups{&group}',
        'groupRoleAssignments'      => '/user/groups{&group}/roles',
        'groupRoleAssignment'       => '/user/groups{&group}/roles/{role}',
        'groupSubgroups'            => '/user/groups{&group}/subgroups',
        'groupUsers'                => '/user/groups{&group}/users',
        'rootUserGroup'             => '/user/groups/root',
        'rootUserGroupSubGroups'    => '/user/groups/subgroups',
        'roles'                     => '/user/roles',
        'role'                      => '/user/roles/{role}',
        'roleByIdentifier'          => '/user/roles?identifier={role}',
        'policies'                  => '/user/roles/{role}/policies',
        'policy'                    => '/user/roles/{role}/policies/{policy}',
        'users'                     => '/user/users',
        'user'                      => '/user/users/{user}',
        'userDrafts'                => '/user/users/{user}/drafts',
        'userGroups'                => '/user/users/{user}/groups',
        'userGroupAssign'           => '/user/users/{user}/groups?group={&group}',
        'userGroup'                 => '/user/users/{user}/groups{&group}',
        'userRoleAssignments'       => '/user/users/{user}/roles',
        'userRoleAssignment'        => '/user/users/{user}/roles/{role}',
        'userPolicies'              => '/user/policies?userId={user}',
        'userSession'               => '/user/sessions/{sessionId}',
    );
}
