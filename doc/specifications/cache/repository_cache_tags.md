## Tags

As the system is extensible, important part of this is to document the used tags to avoid wrong use, or conflicts.
If you add own tag types, please prefix with 1-3 letters abbreviating your *(company/full)* name.

The tags needs to be made in a way so that cache can be cleared using nothing but what info is available on signal,
and the signals will need to be expanded to contain the relevant info depending on operation they correspond to.

#### Content Tags


Tags applied to Content View and Content REST Object:

- `content-<content-id>`

    *Tagging*: Used for tagging content responses with id.
    
    *Clearing*: When a operation is specifically affecting just given content.

- `content-type-<content-type-id>`

    *Tagging*: Used for tagging content responses with type id.
    
    *Clearing*: When a operation is specifically affecting content type, typically soft purge all affected content.

#### Location and Content Location Tags


- `location-<location-id>`

    If content has locations we need additional tags on the content response to be able to clear on operations affecting a
    given location or a tree.
    
    *Tagging*: Used for tagging content responses with all it's locations.
    
    *Clearing*: When a operation is specifically affecting one or several locations, on tree operations `path` is more relevant.


- `parent-<parent-location-id>`

    *Tagging*: Used for tagging content responses with all it's direct parents.
    
    *Clearing*: When a operation is specifically affecting parent location(s), on tree operations `path` is more relevant.

- `path-<path-location-id>`

##### Content Relations



- `relation-<relation-content-id>`

*Tagging*: Used for tagging content responses with all it's relations, where content id is the id of the other side of relation.
*Clearing*: When operations also affect reverse relations we can clear them using content id of self.

_Note: These tags are mainly relevant for field (field, embed, link) relations as change like deletion of realtion has an
effect on the output of the given content (it should not render links to the given relation anymore)._



#### Other eZ Repository domains

Other domains in the Repository also have tags which follows it's name, e.g.:
- `content-type-<id>`
- `content-type-group-<id>`
- `section-<id>`
- `object-state-<id>`
- `object-state-group-<id>`
- `role-<id>`
- _(..)_
