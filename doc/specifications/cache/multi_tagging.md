# Multi Tagging


_WIP @todo_




## Description
Being built on Symfony, one of the cornerstones of eZ Platform is that it has always extended Symfony HttpCache
to better integrate with the CMS. Starting in version v2014.11 *(eZ Publish Platform 5.4)* this was further improved
by building on top of FOSHttpCache.

But even if we moved to FOSHttpCache, we still had our own Symfony Proxy Store, and still abstract cache clearing
internally. Starting with kernel v6.5 *(eZ Platform v1.5)*, this allowed us to give HttpCache layer several changes to:
1. Be able to support multiple tagging, across Varnish and Symfony Proxy, as opposed to just single location tagging.
2. Allow usage of Varnish xkey VMOD for better performance with Varnish.

In both cases this is needed to more reliably be able to clear cache, and to improve performance. At later point these
features might appear in FOSHttpCache v2.0 in a more generic form for us to build upon.


## Tags

As the systems is extensible, important part of this is to document the used tags to avoid wrong use, or conflicts.
If you add own tag types, please prefix with 1-3 letters abbreviating your *(company/full)* name.

The tags are needs to be made in a way so that cache can be cleared using nothing but what info is available on signal,
and the signals will need to be expanded to contain the relevant info depending on operation they correspond to.


### Predefined System Tags


- `content-<content-id>`

*Tagging*: Used for tagging content responses with id.
*Clearing*: When a operation is specifically affecting just given content.


- `content-type-<content-type-id>`

*Tagging*: Used for tagging content responses with type id.
*Clearing*: When a operation is specifically affecting content type, typically soft purge all affected content.


##### Locations

If content has locations we need additional tags on the content response to be able to clear on operations affecting a
given location or a tree.

- `location-<location-id>`

*Tagging*: Used for tagging content responses with all it's locations.
*Clearing*: When a operation is specifically affecting one or several locations, on tree operations `path` is more relevant.


- `parent-<parent-location-id>`

*Tagging*: Used for tagging content responses with all it's direct parents.
*Clearing*: When a operation is specifically affecting parent location(s), on tree operations `path` is more relevant.

- `path-<path-location-id>`


##### Relations

If content has relations we need additional tags on the content response to be able to clear on operations affecting the
other side of the relation.

- `relation-<relation-content-id>`

*Tagging*: Used for tagging content responses with all it's relations, where content id is the id of the other side of relation.
*Clearing*: When operations also affect reverse relations we can clear then using content id of self.


## Changes to existing systems

_@todo: This is just notes for PR_


**Signals**:

- deleteLocation need to be changed to send different signals depending on if content is deleted or not.
- deleteContent signal need to contain info on directly affected locations (locations of deleted content)
    - using this all locations in all trees affected can be cleared using `path-<location-id>`



**Cache clearing system**:

- Change smart cache system into the two parts:
    - Event for tag generation for responses based on Content.
    - Event for tag generation on operations only using signal info *(as content might be deleted)*.
- Change purging to take tags only and soft purge based on them if possible.


**VCL**
Change to use xkey header, and extra flavour for actually using xcache VMOD, or inline doc on how to use it.


## Architectural choice: Http Soft purge vs instant UI update

_@todo: Move soft purge concept to own doc?_

xkey gives us ability to do soft purge. Which is utterly important for setups with high traffic, and/or if content is
updated frequently causing hard purges either by means of explicitly PURGE or BAN, flooding the backend with traffic
while cache is being regenerated.

However on UI side users expects interface to always reflect latest updates, unless interface itself indicates that a
given operation is sent for processing, or sent to que for processing.

### Recommendations

Given the CMS is aiming to be able to work in distributed setups in the future, and already have search engine which is
asynchronous, and now also HTTP cache which has some staleness in it. It is best if UI makes user aware most changes
are not instant.

UI can for instance inform user in notifications on screen that operation is being executed and will be visible soon, as
opposed to that it is done.

But to avoid stale cache issues caused by cache being refreshed before search index is or similar, it is now important
that HTTP Cache ttl *(default_ttl)* is not set to high, especially for REST endpoints used by UI.

Additionally if editorial UI is setup on own domain or url, you can update VCL rules to never serve grace cache content
to editors, to avoid some "random UI staleness".
