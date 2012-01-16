==============================
eZ Publish REST API DRAFT 1.05
==============================

.. sectnum::

.. contents:: Table of Contents


Authentication
==============

Basic Authentication
--------------------

See http://tools.ietf.org/html/rfc261

OAuth
-----

See http://oauth.net/2/
TBD - setting up oauth.


Session based Authentication
----------------------------

This approach violates generally the principles of RESTful services. However,
the sessions are only created to reauthenticate the user (and perform authorization,
which has do be done anyway) and not to hold session state in the service.
So we consider this method to support AJAX based applications.

If activated the user has to login and the client has to send the session cookie in every request:

:Resource:    /user/sessions
:Method:      POST
:Description: Performs a login for the user and returns the session cookie
:Request format: application/x-www-form-urlencoded 
:Parameters:
        :login:  the login of the user
        :password:  the password
:Response: 200 Set-Cookie: SessionId : <sessionID>  A unique session id containing encryped information of client host and expiretime  
           UserInfo_
:Error codes: 
       :401: If the authorization failed


In order to logout the user calls:

:Resource: /user/sessions/<sessionID>
:Method: DELETE
:Description: The user session is removed i.e. the user is logged out.
:Parameters:
:Response: 204
:Error Codes:
    :404: If the session does not exist

SSL Client Authentication
-------------------------

The REST API provides authenticating a user by a subject in a client certificate delivered by the web server configured as SSL endpoint.


Content
=======

Concepts
--------

This paragraph describes the relationchips between content, versions, drafts, languages and translations and how to use them.

- Content is a composite of metadata and a list of versions.
- A version is a composite of version metadata and fields.
- A draft is a version with status DRAFT assigned to a user which is allowed to update the version.
- Fields can depend on a language. With languages of a content we denote all existing languages in fields of the existing versions.
- A translation is a result of a translation process and denotes the meta information for this process.
  The meta information consists of source language, destination language, source version and destination version.
  With this information it is possible to track translations (e.g. view differences) and to trigger workflows if
  e.g. the source language has changed and the destination language has to be updated. (Note that in the current kernel
  there are some restrictions - source language cannot be stored yet but this will change in the future)

To create a new content object do:

.. parsed-literal::

    POST <URI>/content/objects
    [ContentCreate_]

This method creates a new draft assigned to a user given in the body or to the authenticated user (if not given).
It returns a ContentVersion_ which contains the content metadata, version meta data and the fields. 

To update a draft call:

.. parsed-literal::

    PUT <URI>/content/objects/<ID>/versions/<version_nr>
    [ContentVersionInput_]

To publish the draft call:

.. parsed-literal::

    POST <URI>/content/objects/<ID>/versions/<version_nr>
    or
    PUBLISH <URI>/content/objects/<ID>/versions/<version_nr>

To list the drafts assigned to a user call:

.. parsed-literal::

    GET <URI>/users/<ID>/drafts

which returns a list of [VersionInfo_]

To create and update a new draft for an existing content object call:

.. parsed-literal::

    POST <URI>/content/objects/<ID>/versions
    [ContentVersionInput_]

To register a translation (not in eZ publish 4.6)

.. parsed-literal::

    POST <URI>/content/objects/<ID>/translations
    [TranslationInfo_]

This is usually done by a workflow which has updated the draft before.

To retrieve the current version of a content object in one language call:

::

    GET <URI>/content/objects/<ID>/languages/<language_code>

or:

::

     GET <URI>/content/objects/<ID>?languages=<language_code>,...

In the second it is possible to retrieve more than on language.


To update the content meta data (version independent) call:

.. parsed-literal::

    PUT <URI>/content/objects/<ID>
    [ContentUpdate_]


General considerations
----------------------

PUT vs. POST
~~~~~~~~~~~~

In this specification we consider a method as idempotent if the result or side effect 
(see `HTTP/1.1 <http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html>`_ 9.1.2) of the 
operation is the same if called twice on the same resource.
This means that we do not require that the response of the operation is identical if called twice. This leads to use less POST
requests for creating entities which cant created twice due to constraints in the eZ publish data model.


Actions and Parameters
~~~~~~~~~~~~~~~~~~~~~~

In this specification an approach is taken which provides easy intuitive resources and tries to hide complexity in parameters. There are also some
actions which are triggered via POST and parameters. It is avoided to have complex resources and responses containing action urls for
given resources. Examples:

- The publish operation is realized by making an empty POST on
  /content/objects/<ID>/versions/<nr>

- Trashing a content object or location is not realized with (an academic) POST on the trash items but on on the DELETE
  operation with a parameter indicating to delete permanently or moving to trash.

- Copying is realized with a POST and src and destination parameters.



Overview
--------

In the content module there are the root collections objects, locations, trash and sections 

===================================================== =================== ======================= ============================ ================
        :Resource:                                          POST                GET                  PUT                         DELETE
----------------------------------------------------- ------------------- ----------------------- ---------------------------- ----------------
/content/objects                                      create new content  list/find content       -                            -            
/content/objects/views                                -                   list views              create a new view and return -            
                                                                                                  the results
/content/objects/view/<ID>                            -                   get view results        replace view                 delete view
/content/objects/<ID>                                 -                   load content in current update content meta data     delete content
                                                                          version
/content/objects/<ID>/translations                    create translation  list translations       -                            -            
/content/objects/<ID>/languages                       -                   list languages of cont. -                            -              
/content/objects/<ID>/languages/<lang_code>           -                   load content in the     -                            delete language
                                                                          given language                                       from content   
/content/objects/<ID>/versions                        create a new draft  load all versions       -                            -            
                                                      from an existing    (version infos)
                                                      version 
/content/objects/<ID>/versions/<versionNo>            -                   get a specific version  update a version/draft       delete version
/user/users/<ID>/drafts                               -                   list all drafts owned   -                            delete all drafts
                                                                          by the user                                          of the user
/content/objects/<ID>/locations                       -                   load locations of cont- create a new location for    delete all locations
                                                                          ent                     content
/content/objects/<ID>/mainlocation                    -                   load main location      change mainlocation          -                    
/content/objects/<ID>/section                         -                   get section of content  assign new section           -            
/content/objects/<ID>/relations                       -                   load outgoing relations -                            -                            
                                                                          of current version
/content/objects/<ID>/versions/<no>/relations         create new relation load relations of vers. -                            -              
/content/objects/<ID>/versions/<no>/relations/<ID>    -                   load relation details   -                            delete relation
/content/locations                                    -                   list/find locations     create a new location refer- -            
                                                                                                  ing to an existing content 
                                                                                                  and a parent
/content/locations/<ID>                               -                   load a location         update location              delete a location (subtree)
/content/locations/<ID>/content                       -                   load content            update content               delete content
/content/locations/<ID>/children                      -                   load children           create a new location refer- delete all children
                                                                                                  ing to a existing content 
                                                                                                  object
/content/locations/<ID>/parent                        -                   load parent location    move location                -            
/content/sections                                     -                   list all sections       create a new                 section -            
/content/sections/<ID>                                -                   load section            update setion                delete section
/content/trash/items                                  -                   list trash items        -                            empty trash
/content/trash/items/<ID>                             -                   load trash item         untrash item                 delete from trash
===================================================== =================== ======================= ============================ ================


Specification
-------------

General Error Codes
~~~~~~~~~~~~~~~~~~~
(see also HTTP 1.1 Specification)

:500: The server encountered an unexpected condition which prevented it from fulfilling the request - e.g. database down etc.
:501: The requested method was not implemented yet
:404: Requested resource was not found
:405: The request method is -             - the methods available are returned for this resource
	
Operations using RemoteId
~~~~~~~~~~~~~~~~~~~~~~~~~

If a remoteId should be used instead of an ID:

In resources replace <ID> with remote/<remoteId>
In url parameters replace ....Id with ...remoteId

This will be explicite part of spec asap.


Managing content
~~~~~~~~~~~~~~~~

Creating Content
````````````````

:Resource:    /content/objects
:Method:      POST
:Description: Creates a new content draft assigned to the authenticated user. If a different userId is given in the input
              it is assigned to the given user but this required special rights for the authenticated user (this is useful
              for content staging where the transfer process does not have to authenticate with the user which created the
              content object in the source server).
              The user has to publish the content if it should be visible.
:Request format: application/json
:Parameters:
        :fields:            comma separated list of fields which should be returned in the response (see ContentVersion_)
        :responseGroups:    alternative: comma separated lists of predefined field groups (see REST API Spec v1)
:Inputschema:    ContentCreate_
:Response:       201 Location: /content/objects/<ID>/versions/<version_nr> 
                 Version_
:Error codes: 
       :400: If the Input does not match the input schema definition or the validation on a field fails, 
             In this case the response contains an ErrorMessage_ containing the appropriate error description
       :401: If the user is not authorized to create this object in this location
	
List/Search Content
```````````````````
:Resource: /content/views
:Method:   PUT
:Description: executes a query and returns the results (in future - stores the query as view under the given identifier in the Query_ )
              The Query_ input reflects the criteria model of the public API.
:Request format: application/json
:Parameters:
    :fields:         comma separated list of fields which should be returned in the response (see Content)
    :responseGroups: comma separated lists of predefined field groups (see REST API Spec v1)
:Inputschema: Query_
:Response: 200 array of Version_
:Error codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
	
Alternatively:

:Resource: /content/objects
:Method: GET
:Description: List/Search content objects (published version)
:Parameters:
    :q:               (required) query string in lucene format
    :fields:          comma separated list of fields which should be returned in the items of the response (see Content)
    :responseGroups:  comma separated lists of predefined field groups (see REST API Spec v1)
    :limit:           only <limit> items will be returned started by offset
    :offset:          offset of the result set
    :sortField:       the field used for sorting TBD.
    :sortOrder:       DESC or ASC
:Response: 200 array of Version_
:Error codes:
    :400: If the query string does not match the lucene query string format, In this case the response contains an ErrorMessage_
	
Load Content
````````````
:Resource: - /content/objects/<ID> 
           - /content/locations/<locationId>/content
:Method: GET
:Description: Loads the content object for the given id in its current version (i.e the current published version or if not exists the draft of the authenticated user)
:Parameters:
    :fields: comma separated list of fields which should be returned in the response (see ContentVersion_)
    :responseGroups: comma separated lists of predefined field groups (see REST API Spec v1)
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Response: 200 Version_
:Error Codes:
    :401: If the user is not authorized to read  this object. This could also happen if there is no published version yet and another user owns a draft of this content
    :404: If the ID is not found


Get a result in different format
''''''''''''''''''''''''''''''''

GET /.../<resource>.<json | html | xml >   TBD.

Update Content
``````````````
:Resource: - /content/objects/<ID> 
           - /content/locations/<locationId>/content
:Method: PUT
:Description: this method updates the content metadata which is independent from a version.
:Request Format: application/json
:Parameters: 
    :fields:          comma separated list of fields which should be returned in the items of the response (see Content)
    :responseGroups:  comma separated lists of predefined field groups (see REST API Spec v1)
:Inputschema: ContentUpdate_
:Response: 200 ContentInfo_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update this object  
    :404: If the content id or location id does not exist

Delete Content
``````````````
:Resource: /content/objects/<ID> or /content/locations/<locationId>/content
:Method: DELETE
:Description: The content is deleted or moved to trash. On delete all locations assigned the content object are deleted via delete subtree. 
:Parameters:
    :trash: if true (default) the content is moved to trash - otherwise it is deleted
:Response: 204
:Error Codes:
    :404: content object was not found
    :401: If the user is not authorized to delete this object


Copy content
````````````
:Resource:    /content/objects
:Method:      POST
:Description: Creates a new content object as copy under the given parent id. One of the parameters parentId or parentRemoteId and
              srcId or sourceRemoteId are required.
:Request format: application/json
:Parameters:
        :parentId:  the parent location id under which the new content should be created
        :parentRemoteId:  the parent location renote id under which the new content should be created
        :srcId: the src id of the content object to be copied        
        :srcRemoteId: alternatively the src remote id of the content object to be copied        
:Inputschema:
:Response: 201  ContentInfo_
:Error codes: 
       :400: If one of the parameters parentId and parentRemoteId or srcId and srcRemoteId are missing or the corresponding objects do not exist.  
       :401: If the user is not authorized to copy this object to the given location

Managing Translations
~~~~~~~~~~~~~~~~~~~~~

A translation is a result of an executed translation process. It consists of
the meta data sourceLanguage, sourceVersion, destinationLanguage, destinationVersion 
The repository stores translation info datasets which contain for each content object a
set of executed translations (not available in 4.6).

List available translations
```````````````````````````
:Resource: /content/objects/<ID>/translations
:Method: GET
:Description: Lists the latest translation infos for the given content object.
:Parameters: 
    :latest: if true (default) only the latest translation for each language is returned.
:Response: 200 array of TranslationInfo_
:Error Codes:
    :404: If the content object does not exist

Load Translation
````````````````
:Resource: /content/objects/<ID>/translations/<ID>
:Method: GET
:Description: loads a translation info
:Parameters: 
:Response: 200 TranslationInfo_
:Error Codes:
    :404: If the content object or the tranlation info was not found 
    :401: If the user is not authorized to delete this object

Create Translation
``````````````````
:Resource: /content/objects/<ID>/translations
:Method: POST
:Description: Inserts a new translation info for the given object 
:Request Format: application/json
:Parameters:
:Inputschema: TranslationInfo_
:Response: 201
:Error Codes:
    :400: If the Input does not match the input schema definition.  In this case the response contains an ErrorMessage_ containing the appropriate error description
    :401: If the user is not authorized to create the translation
    :404: If the content object does not exist

List Languages
``````````````
:Resource: /content/objects/<ID>/languages
:Method: GET
:Description: Lists all available languages of a content object
:Parameters: 
:Response: 200 array of string
:Error Codes:
    :404: If the content object or the tranlation info was not found 
    :401: If the user is not authorized to delete this object

Load Content in one Language
````````````````````````````
:Resource: /content/objects/<ID>/languages/<language_code>
:Method: GET
:Description: Loads the current version of a content object only containing the fields in the given language and the non translatable fields.
:Parameters: 
:Response: 200 Version_
:Error Codes:
    :404: If the content object or the tranlation info was not found 
    :401: If the user is not authorized to delete this object


Remove a language
`````````````````
:Resource: /content/objects<ID>/languages/<language_code>
:Method: DELETE
:Description: A language is completely removed from the content object in all versions and the translation metadata is deleted.
:Parameters:
:Response: 204
:Error Codes:
    :401: If the user is not authorized to remove the translation
    :404: If the object or the translation does not exist
	

Managing Versions
~~~~~~~~~~~~~~~~~

List Versions
`````````````
:Resource: /content/objects/<ID>/versions
:Method: GET
:Description: Returns a list of all versions of the content
:Response: 200 array of VersionInfo_
:Error Codes:
     :401: If the user has no permission to read the versions

Load Content Version
````````````````````
:Resource: /content/objects/<ID>/versions/<versionNo>
:Method: GET
:Description: Loads a specific version of a content object
:Parameters: 
    :fields: comma separated list of fields which should be returned in the response (see Content)
    :responseGroups: alternative: comma separated lists of predefined field groups (see REST API Spec v1)
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Response: 200 Version_
:Error Codes:
    :401: If the user is not authorized to read  this object
    :404: If the ID or version is not found
	
Update Version
``````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: PUT
:Description: A specific draft is updated. 
:Request Format: application/json
:Parameters: 
    :fields: comma separated list of fields which should be returned in the response (see Content)
    :responseGroups: alternative: comma separated lists of predefined field groups (see REST API Spec v1)
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Inputschema: ContentVersionInput_
:Response: 200 Version_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update this version  
    :403: If the version is not allowed to change - i.e is not a DRAFT
    :404: If the content id or location id does not exist
	

Create a Draft from an archived or published Version
````````````````````````````````````````````````````
:Resource: /content/objects/<ID>/versions
:Method: POST
:Description: The system creates a new draft version as a copy from the given version
:Request Format: 
:Parameters:
    :srcVersion: the source version from which data is copied to the new draft - if not given the current published version is used
:Inputschema:
:Response: 201 Location: /content/objects/<ID>/versions/<new-versionNo> ContentVersionInfo_
:Error Codes:
    :401: If the user is not authorized to update this object  
    :404: If the content object was not found

Delete Content Version
``````````````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: DELETE
:Description: The content  version is deleted
:Response: 204
:Error Codes:
    :404: if the content object or version nr was not found
    :401: If the user is not authorized to delete this version 
    :403: If the version is in state published

Publish a content version
`````````````````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: POST or PUBLISH
:Description: The content version is published
:Response: 204
:Error Codes:
    :404: if the content object or version nr was not found
    :401: If the user is not authorized to publish this version
    :403: If the version is not a draft

Managing Relations
~~~~~~~~~~~~~~~~~~

Load all outgoing relations
```````````````````````````
:Resource: /content/objects/<ID>/relations
:Method: GET
:Description: loads all outgoing relations  for the given content object in the current version
:Parameters: 
    :offset: the offset of the result set
    :limit: the number of relations returned
:Response: 200 array of Relation_
:Error Codes:
    :401: If the user is not authorized to read  this object
    :404: If the content object was not found

Load a relation
```````````````
:Resource: /content/objects/<ID>/relations/<ID>
:Method: GET
:Description: loads a relation for the given content object
:Parameters:
:Response: 200 Relation
:Error Codes:
    :404: If the  object with the given id or the relation does not exist
    :401: If the user is not authorized to read this object  
	
Create a new Relation
`````````````````````
:Resource: /content/objects/<ID>/versions/<versionNo>/relations
:Method: PUT
:Description: Creates a new relation of type COMMON for the given draft. 
:Request Format: application/json
:Parameters: destId (required): the destinationId for new created relation
:Inputschema:
:Response: 201 Relation_
:Error Codes:
    :401: If the user is not authorized to update this content object
    :403: If a relation to the destId already exists or the destId does not exist or the version is not a draft.
    :404: If the  object or version with the given id does not exist

Delete a relation
`````````````````
:Resource: /content/objects/<ID>/versions/<versionNo>/relations/<ID>
:Method: DELETE
:Description: Deletes a relation of the given draft.
:Parameters:
:Response: 204
:Error Codes:
    :404: content object was not found or the relation was not found in the given version
    :401: If the user is not authorized to delete this relation 
    :403: If the relation is not of type COMMON or the given version is not a draft
	
Managing Locations
~~~~~~~~~~~~~~~~~~

Create a new location for a content object
``````````````````````````````````````````
:Resource: /content/objects/<ID>/locations
:Method: PUT
:Description: Creates a new location for the given content object
:Request Format: application/json
:Parameters:
     :parentId: (required): the parentId for new created location
:Inputschema: LocationInput_
:Response: 200 Location_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this location  
    :403: If a location under the given parent id already exists
	
Alternatively:

:Resource: /content/locations
:Method: PUT
:Description: Creates a new location for the given content object and parent Id
:Request Format: application/json
:Parameters:
    :contentId: (required) the id of the content object for which this location should be created
    :parentId: (required) the parent location for the new created location
:Inputschema: LocationInput_
:Response: 200 Location_
:Error Codes:

    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this location  
    :403: If a location for the given content object already exists under the given parent location
	
Alternatively:

:Resource: /content/locations/<ID>/children
:Method: PUT
:Description: Creates a new location for the given content object
:Request Format: application/json
:Parameters:
    :contentId: (required): the contentId for which the new location is created
:Inputschema: LocationInput_
:Response: 200 Location_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this location  
    :403: If a location for the given content object already exists under the given location
	
Get locations for a content object
``````````````````````````````````
:Resource: /content/objects/<ID>/locations
:Method: GET
:Description: loads all locations for the given content object
:Parameters:
:Response: 200 array of Location_
:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object  

Load main location
``````````````````
:Resource: /content/objects/<ID>/mainlocation
:Method: GET
:Description: loads the main location for the given content
:Parameters:
:Response: 200 Location_
:Error Codes:
    :404: If the content object with the given id does not exist
    :401: If the user is not authorized to read this location  

Change main location of a content object
````````````````````````````````````````
:Resource: /content/objects/<ID>/mainlocation
:Method: PUT
:Description: changes the main location of the given content object
:Request Format: 
:Parameters:
     :locationId: (required): the id of the new main location
:Inputschema: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to update the content object  
    :403: If the location belongs not to the locations of content
	


Load location by id
```````````````````
:Resource: /content/locations/<ID>
:Method: GET
:Description: loads the location for the given id
:Parameters:
:Response: 200 Location_
:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to read this location  
	

Update location
```````````````
:Resource: /content/locations/<ID>
:Method: PUT
:Description: updates the location,  this method can also be used to hide/unhide a location via the hidden field in the LocationInput_
:Request Format: application/json
:Parameters:
:Inputschema: LocationInput_
:Response: 200 Location_
:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to update this location  

Delete Subtree
``````````````
:Resource: /content/locations/<ID>
:Method: DELETE
:Description: Deletes the complete subtree for the given root id or moves it to the trash. If the parameter trash = false every content object is deleted (see "delete content object") which does not have any other location. Otherwise the deleted location is removed from the content object. The children a recursively deleted also. If trahs = true the locations are moved to trash and the content object is left untouched.
:Parameters:
    :trash: boolean (default true). If true the locations and content objects are moved to trash
:Response: 204
:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to delete this subtree  

Get child locations 
```````````````````
:Resource: /content/locations/<ID>/children
:Method: GET
:Description: loads all child locations for the given parent location
:Parameters:
    :offset: the offset of the result set
    :limit: the number of locations returned
:Response: 200 array of Location_
:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object  

Delete all children (Subtree)
`````````````````````````````
:Resource: /content/locations/<ID>/children
:Method: DELETE
:Description: Deletes the complete subtree for the given children or moves it to the trash. If the parameter trash = false every content object is deleted (see "delete content object") which does not have any other location. Otherwise the deleted location is removed from the content object. The children a recursively deleted also. If trahs = true the locations are moved to trash and the content object is left untouched.
:Parameters:
	:trash: boolean (default true). If true the locations and content objects are moved to trash
:Response: 204
:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to delete this location  

Get Parent Location
```````````````````
:Resource: /content/locations/<ID>/parent
:Method: GET
:Description: loads the parent location
:Parameters:
:Response: 200 Location_
:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object  

Move Subtree
````````````
:Resource: /content/locations/<ID>/parent
:Method: PUT
:Description: moves the location to another parent
:Request Format: 
:Parameters:
    :destParentId: (required) - the new parent id
:Inputschema:
:Response: 200
:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to move this location  
	
Copy Subtree
````````````
:Resource: /content/locations/<parentId>/children
:Method: POST
:Description: moves the location to another parent
:Request Format: 
:Parameters:
    :srcId: (required) - the id of the tree to be copied
:Inputschema:
:Response: 200 Location_
:Error Codes:
    :404: If the location with the given id does not exist
    :401: If the user is not authorized to move this location  

Swap Location
`````````````
:Resource: /content/locations/<ID>
:Method: POST
:Description: Swaps the content of the location with the content of the given location
:Request Format: 
:Parameters:
    :srcNodeId: (required) - the id of the location to be swapped
:Inputschema:
:Response: 204
:Error Codes:
    :404: If the location with the given id does not exist
    :401: If the user is not authorized to swap this location  

Managing Sections
~~~~~~~~~~~~~~~~~

Create a new Section
````````````````````
:Resource: /content/sections
:Method: PUT
:Description: Creates a new section
:Request Format: application/json
:Parameters:
:Inputschema: SectionInput_
:Response: 200 Section_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this section  
    :403: If a section with same identifier already exists

Get Sections
````````````
:Resource: /content/sections
:Method: GET
:Description: Returns a list of all sections
:Response: 200 array of Section_
:Error Codes:
    :401: If the user has no permission to read the sections
	
Get Section of content
``````````````````````
:Resource: /content/objects/<ID>/section
:Method: GET
:Description: Returns the section assigned to the given content object
:Response: 200 Section_
:Error Codes:
    :401: If the user has no permission to read the content object
    :404: If the content object with the given id does not exist
	
Assign a Section to content
```````````````````````````
:Resource: /content/objects/<ID>/section
:Method: PUT
:Description: Assigns a new section to the given content object
:Request Format: 
:Parameters:
    :sectionId: (required)
:Inputschema:
:Response: 204
:Error Codes:
    :401: If the user is not authorized to assign this section  
    :404: If the content object does not exist

Get Section by id
`````````````````
:Resource: /content/sections/<ID>
:Method: GET
:Description: Returns the section given by id
:Response: 200 Section
    :401: If the user is not authorized to read this section  
    :404: If the section does not exist

Update a Section
````````````````
:Resource: /content/sections/<ID>
:Method: PUT
:Description: Updates a section
:Request Format: application/json
:Parameters:
:Inputschema: SectionInput_
:Response: 200 Section_
:Error Codes:
    :400; If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this section  
    :403: If a section with the given new identifier already exists

Delete Section
``````````````
:Resource: /content/sections/<ID>
:Method: DELETE
:Description: the given section is deleted
:Parameters:
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this section
    :404: If the section does not exist

Assign Section to a subtree
```````````````````````````
:Resource: /content/locations/<ID>
:Method: POST
:Description: Assigns a new section to the complete subtree given by ID
:Request Format: 
:Parameters:
    :sectionId: (required)
:Inputschema:
:Response: 204
:Error Codes:
    :401: If the user is not authorized to assign this section  
    :404: If the location does not exist

Managing Trash
~~~~~~~~~~~~~~

List TrashItems
```````````````
:Resource: /content/trash/items
:Method: GET
:Description: Returns a list of all trash items
:Response: 200 array of Location_
    :401: If the user has no permission to read the trash

Get TrashItem
`````````````
:Resource: /content/trash/items/<ID>
:Method: GET
:Description: Returns the trash item given by id
:Response: 200 Location_
:Error Codes:
    :401: If the user has no permission to read the trash item
    :404: If the trash item with the given id does not exist

Untrash Item
````````````
:Resource: /content/trash/items/<ID>
:Method: PUT
:Description: Restores a trashItem
:Request Format:
:Parameters:
	:parentLocation: if given the trash item is restored under this location otherwise under its parent location
:Inputschema:
:Response: 200 Location_
:Error Codes:
    :401: If the user is not authorized to restore this trash item  
    :403: if the given parent location does not exist
    :404: if the given trash item does not exist

Empty Trash
```````````
:Resource: /content/trash/items
:Method: DELETE
:Description: Empties the trash
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to empty all trash items

Delete TrashItem
````````````````
:Resource: /content/trash/items/<ID>
:Method: DELETE
:Description: Deletes the given trash item
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to empty the given trash item
    :404: if the given trash item does not exist

Additional Usecases
~~~~~~~~~~~~~~~~~~~

Get item counts of collections
``````````````````````````````
GET /.../<collection>?count


Upload Images/Media Files
`````````````````````````
TBD

 
Content Types
=============

Overview
--------

========================================= =================== =================== ======================= =======================
      Resource                                  POST             GET                 PUT                     DELETE
----------------------------------------- ------------------- ------------------- ----------------------- -----------------------
/content/typegroups                       create new group    load all groups     -                       -            
/content/typegroups/<ID>                  -                   load group          update group            delete group
/content/typegroups/<ID>/types            create content type -                   link content type       -                  
/content/typegroups/<ID>/types/<ID>       -                   -                   -                       unlink content type
/content/types                            copy content type   list content types  -                       -            
/content/types/<ID>                       -                   load content type   update content type     delete content type
/content/types/<ID>/fieldDefinitions      create field def.   -                   -                       -            
/content/types/<ID>/fieldDefinitions/<ID> -                   load field def.     update field definition delete field definition
/content/types/<ID>/groups                link new group      load groups         -                       -            
/content/types/<ID>/groups/<ID>           -                   load group          -                       remove from content type (if not last)
========================================= =================== =================== ======================= =======================

Specification
-------------

Managing Content Type Groups
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create Content Type Group
`````````````````````````
:Resource: /content/typegroups
:Method: POST
:Description: Creates a new content type group 
:Request Format: application/json
:Parameters: 
:Inputschema: ContentTypeGroupInput_
:Response: 200 ContentTypeGroup_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this content type group
    :403: If a content type group with same identifier already exists

Get Content Type Groups
```````````````````````
:Resource: /content/typegroups
:Method: GET
:Description: Returns a list of all content types groups
:Parameters:  :includeContentTypes: default false in this case only the ids of the content types a returned
:Response: 200 array of ContentTypeGroup_
:Error Codes:
    :401: If the user has no permission to read the content types
	
Get Content Type Group
``````````````````````
:Resource: /content/typegroups/<ID>
:Method: GET
:Description: Returns the content type given by id
:Parameters:  :includeContentTypes: default false in this case only the ids of the content types a returned
:Response: 200 ContentTypeGroup_
    :401: If the user is not authorized to read this content type  
    :404: If the content type does not exist

Update Content Type Group
`````````````````````````
:Resource: /content/typegroups/<ID>
:Method: PUT
:Description: Updates a content type group 
:Request Format: application/json
:Parameters: 
:Inputschema: ContentTypeGroupInput_
:Response: 200 ContentTypeGroup_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this content type group
    :403: If a content type group with same identifier already exists

Delete Content Type Group
`````````````````````````
:Resource: /content/typegroups/<ID>
:Method: DELETE
:Description: the given content type group is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the content type group is not empty
    :404: If the content type does not exist

Link Content Type
`````````````````
:Resource: /content/typegroups/<ID>/types
:Method: PUT
:Description: Assignes the given content type to the group
:Request Format: application/json
:Parameters: :contentTypeId: the content type which shall be assigned to the group
:Inputschema: 
:Response: 200 
:Error Codes:
    :401: If the user is not authorized to assign this content type

Unlink Content Type
````````````````````
:Resource: /content/typegroups/<ID>/types/<ID>
:Method: DELETE
:Description: removes the given content type from the given group. If the content type is in no other groups it is deleted.
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the content type is to be deleted but it is not empty
    :404: If the content type does not exist

Managing Content Types
~~~~~~~~~~~~~~~~~~~~~~

Create Content Type
```````````````````
:Resource: /content/typegroups/<ID>/types
:Method: POST
:Description: Creates a new content type draft in the given content type group
:Request Format: application/json
:Parameters: :publish: (default false) If true the content type is published after creating
:Inputschema: ContentTypeInput_
:Response: 200 ContentType_
:Error Codes:
    :400: - If the Input does not match the input schema definition,
          - If publish = true and the input is not complete e.g. no field definitions are provided 
    :401: If the user is not authorized to create this content type  
    :403: If a content type with same identifier already exists

Copy Content Type
`````````````````
:Resource: /content/types
:Method: POST
:Description: Creates a new content type
:Request Format: application/json
:Parameters: srcId - required
:Inputschema: 
:Response: 200 ContentType_
:Error Codes:
    :400: if srcId is missing
    :401: If the user is not authorized to copy this content type  
	
Get Content Types
`````````````````
:Resource: /content/types
:Method: GET
:Description: Returns a list of all content types 
:Response: 200 array of ContentType_
:Error Codes:
    :401: If the user has no permission to read the content types

Get Content Type by id
``````````````````````
:Resource: /content/types/<ID>
:Method: GET
:Description: Returns the content type given by id
:Response: 200 ContentType_
    :401: If the user is not authorized to read this content type  
    :404: If the content type does not exist

Update Content Type
```````````````````
:Resource: /content/types/<ID>
:Method: PUT
:Description: If there is no content type version with status draft a DRAFT is created as a copy. Then the 
              given attributes of the content type are updated. The field definitions should not be present in the input - they are ignored.
:Request Format: application/json
:Parameters:
:Inputschema: ContentTypeInput_ 
:Response: 200 ContentType
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the content type
    :403: - If a content type with the given new identifier already exists.  
          - If there exists a draft which is assigned to another user

Delete Content Type
```````````````````
:Resource: /content/types/<ID>
:Method: DELETE
:Description: the given content type is deleted
:Parameters: 
              :deleteObjects: (default false) If true the objects belonging to this content type a deleted.
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If deleteObjects is false and there are object instances of this content type - the response should contain an ErrorMessage_
    :404: If the content type does not exist
	
Publish content type
````````````````````
:Resource: /content/types/<ID>
:Method: POST
:Description: Publishes a content type draft
:Request Format: 
:Parameters:
:Inputschema: 
:Response: 200 
:Error Codes:
    :400: If the content type is not complete e.g. there is no field definition provided
    :401: If the user is not authorized to publish this content type
    :403: If there is no draft assigned to the authenticated user.
    :404: If the content type does not exist

Add Field definition
````````````````````
:Resource: /content/types/<ID>/fielddefinitions
:Method: POST
:Description: Creates a new field definition for the given content type
:Request Format: application/json
:Parameters:
:Inputschema: FieldDefinitionInput_
:Response: 201 FieldDefinition_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to add a field definition  
    :403: - If a field definition with same identifier already exists in the given content type 
          - If there is no draft assigned to the authenticated user.

Get Fielddefinition
```````````````````
:Resource: /content/types/<ID>/fielddefinitions/<ID>
:Method: GET
:Description: Returns the field definition given by id
:Response: 200 array of FieldDefinition_
    :401: If the user is not authorized to read this content type  
    :404: If the content type does not exist

Update Fielddefinition
``````````````````````
:Resource: /content/types/<ID>/fielddefinitions/<ID>
:Method: PUT
:Description: Updates the attributes of a field definitions
:Request Format: application/json
:Parameters:
:Inputschema: FieldDefinitionInput_
:Response: 200 FieldDefinition_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the field definition
    :403: - If a field definition with the given new identifier already exists in the given content type. 
          - If there is no draft assigned to the authenticated user.

Delete Fielddefinition
``````````````````````
:Resource: /content/types/<ID>/fielddefinitions/<ID>
:Method: DELETE
:Description: the given field definition is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: - if there is no draft of the content type assigned to the authenticated user

User Management
===============

Overview
--------

============================================= ===================== ===================== ===================== =======================
Resource                                      POST                  GET                   PUT                   DELETE
--------------------------------------------- --------------------- --------------------- --------------------- -----------------------
/user/groups                                  create user group     load all topl. groups -                     -            
/user/groups/<ID>                             -                     load user group       update user group     delete user group
/user/groups/<ID>/users                       -                     load users of group   create user           delete all users in this group
/user/groups/<ID>/parent                      -                     load parent group     set new parent (move) -            
/user/groups/<ID>/children                    create sub group      load sub groups       -                     remove all sub groups
/user/groups/<ID>/roles                       assign role to group  load roles of group   -                     -            
/user/groups/<ID>/roles/<ID>                  -                     load role             -                     unassign role from group
/user/users                                   -                     list users            -                     -            
/user/users/<ID>                              -                     load user             update user           delete user
/user/users/<ID>/groups                       -                     load groups of user   add to group          -            
/user/roles                                   create new role       load all roles        -                     -            
/user/roles/<ID>                              -                     load role             update role           delete role
/user/roles/<ID>/policies                     -                     load policies         -                     delete all policies from role
/user/roles/<ID>/policies/<module>/<function> -                     load policy           create/update policy  delete policy
============================================= ===================== ===================== ===================== =======================
	

Managing Users and Groups
~~~~~~~~~~~~~~~~~~~~~~~~~

Create User Group
`````````````````
:Resource: - /user/groups
           - /user/groups/<ID>/children
:Method: POST
:Description: Creates a new user group
:Request Format: application/json
:Parameters: 
:Inputschema: UserGroupInput_
:Response: 200 UserGroup_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this user group

Load User Groups
````````````````
:Resource: /user/groups
:Method: GET
:Description: Returns a list of all user groups (TBD - depth parameter)
:Response: 200 array of UserGroup_
:Error Codes:
    :401: If the user has no permission to read user groups

Load User Group
```````````````
:Resource: /user/groups/<ID>
:Method: GET
:Description: loads a user groups for the given <ID>
:Response: 200 UserGroup_
:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist

Update User Group
`````````````````
:Resource: /user/groups/<ID>
:Method: PUT
:Description: Updates a user group
:Request Format: application/json
:Parameters:
:Inputschema: UserGroupInput_
:Response: 200 UserGroup_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the user group

Delete User Group
`````````````````
:Resource: /user/groups/<ID>
:Method: DELETE
:Description: the given user group is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the user group is not empty

Load Users of Group
```````````````````
:Resource: /user/groups/<ID>/users
:Method: GET
:Description: loads the users of the group with the given <ID>
:Response: 200 array of User_
:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist

Create User
```````````
:Resource: /user/groups/<ID>/users
:Method: PUT  (idempotent because a user with the same login can't be created twice)
:Description: Creates a new user in the given group
:Request Format: application/json
:Parameters: 
:Inputschema: UserInput_
:Response: 200 User_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this user 
    :403: If a user with the same login already exists
    :404: If the group with the given ID does not exist

Delete Users of Group
`````````````````````
:Resource: /user/groups/<ID>/users
:Method: DELETE
:Description: All users of the given group are removed
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete users
    :404: If the group with the given ID does not exist

Load Parent Group
`````````````````
:Resource: /user/groups/<ID>/parent
:Method: GET
:Description: loads the parent group for the given <ID>
:Response: 200 UserGroup_
:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist

Move user Group
```````````````
:Resource: /user/groups/<ID>/parent
:Method: PUT
:Description: Moves the gropup to another parent
:Request Format: 
:Parameters: :destParentId: the new parent of the group  
:Inputschema: 
:Response: 200 
:Error Codes:
    :401: If the user is not authorized to update the user group
    :403: If the new parenbt does not exist
    :404: If the user group does not exist

Load Subgroups
``````````````
:Resource: /user/groups/<ID>/children
:Method: GET
:Description: Returns a list of the sub groups
:Response: 200 array of UserGroup_
:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist

Delete Subgroups
````````````````
:Resource: /user/groups/<ID>/children
:Method: DELETE
:Description: All sub groups of the given group are removed
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete user groups
    :403: If the removal of a sub group would delete users
    :404: If the group with the given ID does not exist

List Users
``````````
:Resource: /user/users
:Method: GET
:Description: List users
:Parameters: :limit:  only <limit> items will be returned started by offset
             :offset: offset of the result set
:Response: 200 array of User_
:Error Codes:
    :401: If the user has no permission to read users

(TBD - query/search parameters)

Load User
`````````
:Resource: /user/users/<ID>
:Method: GET
:Description: loads the users of the group with the given <ID>
:Response: 200 User_
:Error Codes:
    :401: If the user has no permission to read users
    :404: If the user does not exist

Update User
```````````
:Resource: /user/users/<ID>
:Method: PUT
:Description: Updates a user 
:Request Format: application/json
:Parameters:
:Inputschema: UserInput_
:Response: 200 User_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the user 
    :404: If the user does not exist

Delete User
```````````
:Resource: /user/users/<ID>
:Method: DELETE
:Description: the given user is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this user
    :403: If the user is the same as the authenticated user
    :404: If the user does not exist

Load Groups Of User
```````````````````
:Resource: /user/users/<ID>/groups
:Method: GET
:Description: Returns a list of user groups the user belongs to
:Response: 200 array of UserGroup_
:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user does not exist

Assign User Group
`````````````````
:Resource: /user/users/<ID>/groups
:Method: PUT
:Description: Assigns the user to a user group
:Request Format: 
:Parameters: :groupId: the new parent group of the user  
:Inputschema: 
:Response: 204 
:Error Codes:
    :401: If the user is not authorized to assign user groups
    :403: - If the new user group does not exist
          - If the user is already in this group
    :404: If the user does not exist


Managing Roles and Policies
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create Role
```````````
:Resource: /user/roles
:Method: POST
:Description: Creates a new role
:Request Format: application/json
:Parameters:  :name: the name of the role
:Inputschema: 
:Response: 200 Role_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this role

Load Roles
``````````
:Resource: /user/roles
:Method: GET
:Description: Returns a list of all roles
:Response: 200 array of Role_
:Error Codes:
    :401: If the user has no permission to read roles

Load Role
`````````
:Resource: - /user/roles/<ID>
           - /user/groups/<ID>/role/<ID>
:Method: GET
:Description: loads a role for the given <ID>
:Response: 200 Role_
:Error Codes:
    :401: If the user has no permission to read roles
    :404: If the role does not exist

Update Role
```````````
:Resource: /user/roles/<ID>
:Method: PUT
:Description: Updates a role
:Request Format: application/json
:Parameters: :name: the new name of the role
:Inputschema: 
:Response: 200 Role_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the role

Delete Role
```````````
:Resource: /user/roles/<ID>
:Method: DELETE
:Description: the given role is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the role is assigned to a user group

Assign Role
```````````
:Resource: /user/groups/<ID>/roles
:Method: POST
:Description: assign a role to a user group
:Request Format: 
:Parameters:  :roleId: the id of the role
:Inputschema: 
:Response: 200 
:Error Codes:
    :401: If the user is not authorized to assign this role

Load Roles for User Group
`````````````````````````
:Resource: /user/groups/<ID>/roles
:Method: GET
:Description: Returns a list of all roles assigned to the given user group
:Response: 200 array of Role_
:Error Codes:
    :401: If the user has no permission to read roles

Remove Role from User Group
```````````````````````````
:Resource: /user/groups/<ID>/roles/<ID>
:Method: DELETE
:Description: the given role is removed from the user group
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type

Load Role
`````````
:Resource: /user/roles/<ID>
:Method: GET
:Description: loads a role for the given <ID>
:Response: 200 Role_
:Error Codes:
    :401: If the user has no permission to read roles

Load Policies
`````````````
:Resource: /user/roles/<ID>/policies
:Method: GET
:Description: loads policies for the given role
:Response: 200 array of Policy_
:Error Codes:
    :401: If the user has no permission to read roles
    :404: If the role does not exist

Delete Policies
```````````````
:Resource: /user/roles/<ID>/policies
:Method: DELETE
:Description: all policies of the given role are deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type

Load Policy
```````````
:Resource: /user/roles/<ID>/policies/<module>/<function>
:Method: GET
:Description: loads a policy for the given module and function
:Response: 200 Policy_
:Error Codes:
    :401: If the user has no permission to read roles
    :404: If the role or policy does not exist

Create or Update Policy
```````````````````````
:Resource: /user/roles/<ID>/policies/<module>/function
:Method: PUT
:Description: Creates or updates a policy for the given module/function
:Request Format: application/json
:Parameters: 
:Inputschema: PolicyInput_
:Response: 200 Policy_
:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update or create the policy
    :404: If the role does not exist

Delete Policy
`````````````
:Resource: /user/roles/<ID>/policies/<module>/function
:Method: DELETE
:Description: the given policy is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :404: If the role or policy does not exist


Input Output Specification
==========================

Object Reference JSON Schema
----------------------------

In the following content and locations can be addressed by using an Id or a remoteId.
This will be encapsulated in the a schema called "Reference".
The Reference can carry wether id or remoteId or both.

::

    {
        "name":"Reference",
        "properties":
            "id": {
                "description":"the id of the object",
                "type":"integer"
            },
            "remoteId": {
                "description":"the remote id of the object",
                "type":"string"
            }
    }

Multi Language Value JSON Schema
--------------------------------

::
    {
        "name":"MLValue",
        "properties": {
            "languageCode": {
                "type":"string",
            }
            "value":{
                "type":"string"
            }
        }
    }

.. _ContentInfo:

ContentInfo JSON Schema
-----------------------

::

    {
        "name":"ContentInfo",
        "properties": 
        {
            "contentId": {
                "type": { "$ref":"#Reference" }
            },
            "contentType" : 
            {
                "description":"the string identifier of the content type",
                "type":"string",
                "required":"true"
            },
            "name" : 
            {
                "description":"the computed name (via name schema) of the content in the main language",
                "type":"string",
            },
            "ownerId": 
            {
                "description":"the user id of the user which owns this content object".
                "type":"integer"
            },
            "state": {
                "description":"indicates if there is a published version of the content",
                "type":"boolean",
            },
            "sectionId": {
                "type":"integer"
            },
            "mainLocationId": {
                "type":"integer"
            },
            "currentVersionNo": {
                "type":"integer"
            },
            "publishDate": {
                "type":"string",
                "format":"date-time"
            },   
            "lastModifiedDate": {
                "type":"string",
                "format":"date-time"
            },   
            "mainLanguageCode": {
                "type":"string",
                "format":"date-time"
            },   
            "alwaysAvailable": {
                "description":"defines if the content object is always shown even it is not 
                               translated in the requested language"
                "type":"boolean",
            },
        }
    }

.. _VersionInfo:

VersionInfo JSON Schema
-----------------------

::

    {
        "name":"VersionInfo",
        "properties": 
        {
            "state": 
            {
                "type":"string",
                "enum": ["DRAFT","PUBLISHED","ARCHIVED"]
            },
            "versionNo": {
                "type":"integer"
            },
            "contentInfo": {
                "type": { "$ref":"#ContentInfo" }
            },
            "creatorId": {
                "type":"integer"
            },
            "createdDate": {
                "type":"string",
                "format":"date-time"
            },
            "lastModifiedDate": {
                "type":"string",
                "format":"date-time"
            },   
            "names": {
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "languageCode": {
                 "description","the main lanugage code for the version",
                 "type":"string",
            },
            "languages": {
                 "description":"the languages occuring in fields",
                 "type":"array",
                 "items": {
                     "type":"string"
                 }
            }
        }
    }

.. _Version:

Version JSON Schema
-------------------

::

    {
        "name":"Version",
        "properties": 
        {
            "versionInfo": {
                "type": { "$ref":"#VersionInfo" }
            },
            "fields": {
                "description":"the collection of fields",
                "type":"array",
                "items": {
                    "type":{
                        "name":"Field",
                        "properties": {
                            "fieldDef": {
                                "type":"string",
                            }
                            "id": {
                                "type":"integer"
                            }
                            "value": {
                                "type":"any"
                            }
                            "language": {
                                "type":"string"
                            }
                        }
                    }
                }
            },
            "relations": {
                "type":"array",
                "items": {
                    "type": { "$ref":"#Relation" }
                }
            }
        }
    }

.. _ContentCreate:

ContentCreate JSON Schema
-------------------------

::

    {
        "name":"ContentCreate",
        "properties": {
            "contentType" : 
            {
                "description":"the string identifier of the content type",
                "type":"string",
                "required":"true"
            },
            "parentLocations": {
                "description":"the parent locations under which the new content should be created (required in 4.x)",
                "type": "array",
                "items": {
                    "type": {
                        "name":"LocationCreate",
                        "properties": {
                             "parentId": {
                                 "description":"the parent under which the new location should be created",
                                 "type": { "$ref":"#Reference" }
                             }
                             "locationParameters": {
                                 "description":"the parameters (remoteId, sort etc.) for the new location",
                                 "type": { "$ref":"#LocationInput" }
                             }   
                        }
                    }
                }
            },
            "initialLanguage" : 
            {
                "description":"if fields are provided in multiple languages this attribute 
                               indicates the initial language",
                "type":"string",
            },
            "alwaysAvailable": 
            {
                "description":"defines if the content object is always shown even it is 
                               not translated in the requested language"
                "type":"boolean",
                "default": "false"
            },
            "remoteId": 
            {
                "description":"the remoteId, if missing the system creates a new one"
                "type":"string"
            },
            "userId": {
                "description":"the owner of the content: If not given the current authenticated user is used",
                "type":"integer"
            },
            "created": {
                "description":"If not given the current timestamp is used on creation. 
                               In staging scanrios the attribute can be used for 
                               aligning the creation date with the one in the source repository"
                "type":"string",
                "format":"date-time"
            },
            "sectionId": {
                "description":"the section assigned to the content, 
                               if not given the section of the parent or a default section is used",
                "type": "integer"
            }
            "fields": 
            {
                "description":"the collection of fields",
                "type":"array",
                "items": 
                {
                    "type":
                     {
                        "name":"FieldValue",
                         "properties": 
                         {
                             "fieldDef": 
                             {
                                 "type":"string",
                                 "required":true
                             }
                             "value": {
                                 "description":"The value in a format according to the 
                                                field type of the field definition"
                                 "type":"any"
                             }
                             "language": {
                                 "type":"string"
                             }
                         }
                      
                     }
                }
            }
        }
    }

.. _ContentUpdate:

ContentUpdate JSON Schema
-------------------------

::

    {
        "name":"ContentUpdate",
        "properties": {
            "initialLanguage" : 
            {
                "description":"if fields are provided in multiple languages this attribute 
                               indicates the initial language",
                "type":"string",
            },
            "alwaysAvailable": 
            {
                "description":"defines if the content object is always shown even it is 
                               not translated in the requested language"
                "type":"boolean",
                "default": "false"
            },
            "remoteId": 
            {
                "description":"the remoteId - if missing the system creates a new one"
                "type":"string"
            },
            "ownerId": {
                "type":"integer"
            },
            "modified": {
                "type":"string",
                "format":"date-time"
            }
        }
    }

.. _ContentVersionInput:

ContentVersionInput JSON Schema
-------------------------------

::

    {
        "name":"ContentVersionInput",
        "properties": {
            "userId": {
                "description":"if not given the current authenticated user is used",
                "type":"integer"
            },
            "date": {
                "description":"if not given the current date is used as creation date or modified date",
                "type":"string",
                "format":"date-time"
            },
            "fields": 
            {
                "description":"the collection of fields",
                "type":"array",
                "items": 
                {
                    "type":
                     {
                        "name":"FieldValue",
                         "properties": 
                         {
                             "fieldDef": 
                             {
                                 "type":"string",
                                 "required":true
                             }
                             "value": {
                                 "description":"The value in a format according to the 
                                                field type of the field definition"
                                 "type":"any"
                             }
                             "language": {
                                 "type":"string"
                             }
                         }
                      
                     }
                }
            }
        }
    }

Specific Field type formats
---------------------------

Author
~~~~~~

::

    {
        "name": "Authors",
        "properties":
            "authors": 
            {
                "type": array,
                "items":
                {
                    type: {
                        "name": "Author",
                        "properties":
                        {
                           "name": {
                               "type: "string"
                            }
                            "email": {
                                "type":"string"
                            }
                        }
                    }
                }
            }
    }

Selection
~~~~~~~~~

::

    {
        "name": "Selection",
        "properties":
            "values": 
            {
                "type": array,
                "items": {
                    "type":"string"
                }
            }
        }
    }



Keyword
~~~~~~~

::

    {
        "name": "Keywords",
        "properties":
            "keywords": 
            {
                "type": array,
                "items": {
                    "type":"string"
                }
            }
        }
    }


Country
~~~~~~~


RelationListInput
~~~~~~~~~~~~~~~~~

::

    {
        "name":"RelationListInput",
        "description":"this schema is used if a field of type ezobjectrelations is created 
                       or updated",
        "properties": {
            "targetObjects": {
                "type":"array",
                "items": {
                    "type":"integer"
                }
            }
        }
    }



.. _Query:

Query JSON Schema
-----------------

::

    {
        "name":"Query",
        "properties": 
        {
            "identifier": {
                "type":"string"
            }
            "criterion": 
            {
                "type": 
                {
                    "name": "Criterion",
                    "properties": 
                     {
                         "name": 
                         {
                             "type": "string",
                             "enum": ["ContentId","ContentTypeGroupId","ContentTypeId",
                                      "DateMetaData", "Field","FullText","LocationId",
                                      "ParentLocationId","RemoteId",
                                      "SectionId","Status","SubtreeId","UrlAlias",
                                      "UserMetaData", "AND","OR","NOT"]
                         },
                         "data": {
                            "type":[
                                      {
                                          "name":"AND",
                                          "properties": {
                                              "terms": {
                                                  "type": "array",
                                                  "items: {
                                                      "type": { "$ref", "#Criterion" }
                                                  }
                                              }
                                          }
                                      }, 
                                      {
                                          "name":"OR",
                                          "properties": {
                                              "terms": {
                                                  "type": "array",
                                                  "items: {
                                                      "type": { "$ref", "#Criterion" }
                                                  }
                                              }
                                          }
                                      }, 
                                      {
                                          "name":"NOT",
                                          "properties": {
                                              "term": {
                                                  "type": { "$ref", "#Criterion" }
                                               }
                                          }
                                      }, 
                                      {
                                          "name": "ContentIdCriterion",
                                          "properties": {
                                              "contentIds": {
                                                  "type": "array",
                                                  "items":  {
                                                      "type" : "integer"
                                                  }
                                              }
                                          }
                                      },
                                      {
                                          "name": "ContentTypeGroupIdCriterion",
                                          "properties": {
                                              "groupId": {
                                                  "type":"integer"
                                              }
                                          }    
                                      },
                                      {
                                          "name": "ContentTypeIdCriterion",
                                          "properties": {
                                              "typeId": {
                                                  "type": ["integer","string"]
                                              }
                                          }    
                                      },
                                      {
                                          "name": "FieldCriterion",
                                          "properties": {
                                              "target": {
                                                  "description":"the identifier of the field",
                                                   "type": "string"
                                              },
                                              "operator": {
                                                  "type":"string",
                                                  "enum": ["IN","LIKE","EQ","LT","LTE","GT","GTE","BETWEEN"]
                                              },
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":"any"
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "DateMetaDataCriterion",
                                          "properties": {
                                              "target": {
                                                  "type":"string",
                                                  "enum": ["CREATED","MODIFIED"]
                                              },
                                              "operator": {
                                                  "type":"string",
                                                  "enum": ["EQ","LT","LTE","GT","GTE","BETWEEN"]
                                              },
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":"integer"
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "FullTextCriterion",
                                          "properties": {
                                              "value": {
                                                  "type":"string"
                                              }
                                          }    
                                      },
                                      {
                                          "name": "LocationIdCriterion",
                                          "properties": {
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":["integer","string"]
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "ParentLocationIdCriterion",
                                          "properties": {
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":["integer","string"]
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "SectionIdCriterion",
                                          "properties": {
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":["integer","string"]
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "RemoteIdCriterion",
                                          "properties": {
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":["integer","string"]
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "StatusCriterion",
                                          "properties": {
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type": "string"
                                                      "enum": ["DRAFT","PUBLISHED","ARCHIVED"]
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "SubtreeCriterion",
                                          "properties": {
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "description":"the full path of the subtree"
                                                      "type": "string"
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "URLAliasCriterion",
                                          "properties": {
                                              "operator": {
                                                  "type":"string",
                                                  "enum": ["EQ","IN","LIKE"]
                                              },
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":"string"
                                                  }
                                              }
                                          }    
                                      },
                                      {
                                          "name": "UserMetaDataCriterion",
                                          "properties": {
                                              "target": {
                                                  "type":"string",
                                                  "enum": ["CREATOR","MODIFIER","OWNER","GROUP"]
                                              },
                                              "value": {
                                                  "type": "array",
                                                  "items": {
                                                      "type":"integer"
                                                  }
                                              }
                                          }    
                                      }
                                  ]
                         }
                    }
                }
            },
            "limit": {
                "type":"integer"
            },
            "offset": {
                "type":"integer"
            },
            "sortClauses": 
            {
                "type":"array",
                "items": 
                {
                    "type"; 
                    {
                        "name":"SortClause",
                        "properties": 
                        {
                            "sortField": 
                            {
                                "type":"string",
                                "enum":  ["PATH","PATHSTRING","MODIFIED","CREATED",
                                          "SECTIONIDENTIFIER","SECTIONID","FIELD",
                                          "PRIORITY","NAME"]
                            },
                            "data": {
                                "type": "any"
                            }
                        }
                    }
                },
                "sortOrder": {
                    "type":"string",
                    "enum": ["ASC","DESC"]
                },
            }
        }
    }


.. _TranslationInfo:

TranslationInfo JSON Schema
---------------------------

::

    {
        "name":"TranslationInfo",
        "properties": 
        {
            "sourceLanguage": {
                "type":"string"
            },
            "destinationLanguage": {
                "type":"string"
            },
            "sourceVersion": {
                "type":"integer"
            },
            "destinationVersion": {
                "type":"integer"
            },
            "translator": {
                "type":"string"
            },
        }
    }


.. _LocationInput:

LocationInput JSON Schema
-------------------------

::

    {
      "name":"LocationInput",
      "properties": {
              "priority": {
                      "type":"integer"
               },
              "remoteId": {
                      "type":"string"
               },
               "hidden": {
                      "description":"if set to false and the location was visible it will be hidden, 
                                     if set to true and the location is hidden it is set to visible",
                      "type":"boolean"
               },
               "sortField": {
                       "type":"string",
               "enum": ["PATH","PUBLISHED","MODIFIED","SECTION","DEPTH","CLASS_IDENTIFIER",
                        "CLASS_NAME","PRIORITY","NAME","MODIFIED_SUBNODE","NODE_ID",
                        "CONTENTOBJECT_ID"]
               },
               "sortOrder": {
               "type":"string",
               "enum": ["ASC","DESC"]
               },
      }
    }



.. _Location:

Location JSON Schema
--------------------

::

    {
      "name":"Location",
      "properties": {
              "pathString": {
                      "type":"string"
               },
              "pathIdentificationString": {
                      "type":"string"
               },
              "id": {
                      "type":"integer"
               },
              "content": {
                      "type": {"$ref":"#ContentInfo"}
               },
              "parentId": {
                      "type":"integer"
               },
              "mainLocationId": {
                      "type":"integer"
               },
              "priority": {
                      "type":"integer"
               },
              "hidden": {
                      "type":"boolean"
               },
              "depth": {
                      "type":"integer"
               },
              "invisible": {
                      "type":"boolean"
               },
              "modifiedSubLocation": {
                       "type":"string",
                       "format":"date-time"
               },
               "remoteId"; {
                   "type":"string"
               },
               "children": {
                   "type":"array",
                   "items": {
                        "type":"integer"
                   }
               },
              "sortField": {
                      "type":"string",    
                      "enum":["PATH","PUBLISHED","MODIFIED","SECTION",
                              "DEPTH","CLASS_IDENTIFIER","CLASS_NAME",
                              "PRIORITY","NAME","MODIFIED_SUBNODE",
                              "NODE_ID","CONTENTOBJECT_ID"]
               },
              "sortOrder": {
                      "type":"string"
                  "enum": ["ASC","DESC"]
               },
      }
    }

.. _SectionInput:

SectionInput JSON Schema
------------------------

::

    {
      "name":"SectionInput",
      "properties": {
              "name": {
                  "type":"string"
               },
              "identifier": {
                  "type":"string"
               }
      }
    }
    
.. _Section:
    
Section JSON Schema
-------------------

::

    {
      "name":"Section",
      "properties": {
              "id": {
                  "type":"integer"
               },
              "name": {
                  "type":"string"
               },
              "identifier": {
                  "type":"string"
               }
      }
    }

.. _Relation:

Relation JSON Schema
--------------------

::

    {
       "name":"Relation",
       "properties": {
               "relationType": {
                   "type":"string",
                   "enum": ["COMMON","EMBED","LINK","ATTRIBUTE"]
                },
               "id": {
                   "type":"integer"
                },
               "contentId": {
                   "type":"integer"
                },
               "versionId": {
                   "type":"integer"
                },
               "destinationContentId": {
                   "type":"integer"
                },
               "fieldDefinitionId": {
                   "type":"integer"
                }
       }
    }

.. _ContentTypeGroup:

ContentTypeGroup JSON Schema
----------------------------

::

    {
        "name":"ContentTypeGroup",
        "properties": {
            "id": {
                "type":"integer"
            },
            "identifier": {
                "type":"string"
                "required":"true"
            },
            "name" : {
                "description":"the name of the content type",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "description" : {
                "description":"the description of the content type",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "contentTypes": {
                "description":"the collection of content types",
                "type":"array",
                "items": {
                    "type": [{ "$ref": "#ContentType" }, "integer" ]
                }
            },
            "creatorId": {
                "type":"integer"
            },
            "created": {
                "type":"string",
                "format":"date-time"
            },
            "modifierId": {
                "type":"integer"
            },
            "modified": {
                "type":"string",
                "format":"date-time"
            }
        }
    }

.. _ContentTypeGroupInput:

ContentTypeGroupInput JSON Schema
---------------------------------

::

    {
        "name":"ContentTypeGroupInput",
        "properties": {
            "identifier": {
                "type":"string"
                "required":"true"
            },
            "name" : {
                "description":"the name of the content type",
                "type":"array",
                "items": {
                    "type": {
                        "name":"MLValue",
                        "properties": {
                            "language": {
                                "type":"string",
                            }
                            "value":{
                                "type":"string"
                            }
                        }
                    }
                }
            },
            "description" : {
                "description":"the description of the content type",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            }
        }
    }





.. _ContentType:

ContentType JSON Schema
-----------------------

::

    {
        "name":"ContentType",
        "properties": {
            "id": {
                "type":"integer"
            },
            "identifier": {
                "type":"string"
                "required":"true"
            },
            "name" : {
                "description":"the name of the content type",
                "type":"array",
                "items": {
                    "type": {
                        "name":"MLValue",
                        "properties": {
                            "language": {
                                "type":"string",
                            }
                            "value":{
                                "type":"string"
                            }
                        }
                    }
                }
            },
            "description" : {
                "description":"the description of the content type",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "state": {
                "type":"string",
                "enum": ["DRAFT","PULISHED","PENDING"]
            },
            "creatorId": {
                "type":"integer"
            },
            "created": {
                "type":"string",
                "format":"date-time"
            },
            "modifierId": {
                "type":"integer"
            },
            "modified": {
                "type":"string",
                "format":"date-time"
            },   
            "defaultAlwaysAvailable": {
                "description":"defines if object instances are always availble 
                               in the main language per default ",
                "type":"boolean"
            },
            "remoteId": {
                "type":"string"
            },
            "urlAliasSchema": {
                "type":"string"
            },
            "objectNameSchema": {
                "type":"string"
            },
            "isContainer": {
                "type":"boolean"
            },
            "groupIds": {
                "description":"the group ids of groups to which this type belongs to",
                "type":"array",
                "items": {
                    "type": "integer"
                }
            },  
            "fieldDefinitions": {
                "description":"the collection of field definitions",
                "type":"array",
                "items": {
                    "type":{
                        "name":"FieldDefinition",
                        "properties": {
                            "indentifer": {
                                "type":"string",
                                "required":"true"
                            },
                            "id": {
                                "type":"integer"
                            },
                            "name" : {
                                "description":"the names of the field definition 
                                               in multiple languages",
                                "type":"array",
                                "items": {
                                    "type": { "$ref":"#MLValue" }
                                }
                            },
                            "description" : {
                                "description":"the descriptions of the field definition 
                                               in multiple languages",
                                "type":"array",
                                "items": {
                                    "type": { "$ref":"#MLValue" }
                                }
                            },
                            "fieldType": {
                                "type":"string"
                            },
                            "fieldGroup": {
                                "type":"string"
                            },
                            "position": {
                                "type":"integer"
                            },
                            "isSearchablle": {
                                "type":"boolean"
                            },
                            "isTrabslatable": {
                                "type":"boolean"
                            },
                            "isInfoCollector": {
                                "type":"boolean"
                            },
                            "isRequired": {
                                "type":"boolean"
                            },
                        }
                    }
                }
            }
        }
    }

.. _ContentTypeInput:

ContentTypeInput JSON Schema
----------------------------

::

    {
        "name":"ContentType",
        "properties": {
            "identifier": {
                "type":"string"
                "required":"true"
            },
            "name" : {
                "description":"the name of the content type",
                "type":"array",
                "items": {
                    "type": {
                        "name":"MLValue",
                        "properties": {
                            "language": {
                                "type":"string",
                            }
                            "value":{
                                "type":"string"
                            }
                        }
                    }
                }
            },
            "description" : {
                "description":"the description of the content type",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "defaultAlwaysAvailable": {
                "description":"defines if object instances are always availble in the 
                               main language per default ",
                "type":"boolean"
            },
            "remoteId": {
                "type":"string"
            },
            "urlAliasSchema": {
                "type":"string"
            },
            "objectNameSchema": {
                "type":"string"
            },
            "isContainer": {
                "type":"boolean"
            },
            "fieldDefinitions": {
                "description":"the collection of field definitions",
                "type":"array",
                "items": {
                    "type":{
                        "name":"FieldDefinition",
                        "properties": {
                            "indentifer": {
                                "type":"string",
                                "required":"true"
                            },
                            "name" : {
                                "description":"the names of the field definition in 
                                               multiple languages",
                                "type":"array",
                                "items": {
                                    "type": { "$ref":"#MLValue" }
                                }
                            },
                            "description" : {
                                "description":"the descriptions of the field definition 
                                               in multiple languages",
                                "type":"array",
                                "items": {
                                    "type": { "$ref":"#MLValue" }
                                }
                            },
                            "fieldType": {
                                "type":"string"
                            },
                            "fieldGroup": {
                                "type":"string"
                            },
                            "position": {
                                "type":"integer"
                            },
                            "isSearchablle": {
                                "type":"boolean"
                            },
                            "isTrabslatable": {
                                "type":"boolean"
                            },
                            "isInfoCollector": {
                                "type":"boolean"
                            },
                            "isRequired": {
                                "type":"boolean"
                            },
                        }
                    }
                }
            }
        }
    }

.. _FieldDefinition:

FieldDefinition JSON Schema
---------------------------

::

    {
        "name":"FieldDefinition",
        "properties": {
            "id": {
                "type":"integer"
            },
            "indentifer": {
                "type":"string",
                "required":"true"
            },
            "name" : {
                "description":"the names of the field definition in multiple languages",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "description" : {
                "description":"the descriptions of the field definition in multiple languages",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "fieldType": {
                "type":"string"
            },
            "fieldGroup": {
                "type":"string"
            },
            "position": {
                "type":"integer"
            },
            "isSearchablle": {
                "type":"boolean"
            },
            "isTrabslatable": {
                "type":"boolean"
            },
            "isInfoCollector": {
                "type":"boolean"
            },
            "isRequired": {
                "type":"boolean"
            },
        }
    }



.. _FieldDefinitionInput:

FieldDefinitionInput JSON Schema
--------------------------------

::

    {
        "name":"FieldDefinitionInput",
        "properties": {
            "indentifer": {
                "type":"string",
                "required":"true"
            },
            "name" : {
                "description":"the names of the field definition in multiple languages",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "description" : {
                "description":"the descriptions of the field definition in multiple languages",
                "type":"array",
                "items": {
                    "type": { "$ref":"#MLValue" }
                }
            },
            "fieldGroup": {
                "type":"string"
            },
            "position": {
                "type":"integer"
            },
            "isSearchablle": {
                "type":"boolean"
            },
            "isTrabslatable": {
                "type":"boolean"
            },
            "isInfoCollector": {
                "type":"boolean"
            },
            "isRequired": {
                "type":"boolean"
            },
        }
    }

.. _UserGroup:

UserGroup JSON Schema
---------------------

::

    {
        name:"UserGroup",
        properties: {
            "parentId": {
                "type": "integer"
            },
            "path": {
                "type": "string"
            },
            "profile": {
                "type":
                 {
                    "name":"UserGroupProfile",
                    "properties": 
                    {
                        "contentType" : 
                        {
                            "description":"the string identifier of the content type",
                            "type":"string",
                            "required":"true"
                        },
                        "name" : 
                        {
                            "description":"the default name of the content",
                            "type":"string",
                        },
                        "id": {
                            "type":"integer"
                        },
                        "ownerId": 
                        {
                            "description":"the user id of the user which owns this 
                                           content object".
                            "type":"integer"
                        },
                        "sectionId": {
                            "type":"integer"
                        },
                        "state": 
                        {
                            "type":"string",
                            "enum": ["DRAFT","PUBLISHED","ARCHIVED"]
                        },
                        "versionNo": {
                            "type":"integer"
                        },
                        "creatorId": {
                            "type":"integer"
                        },
                        "created": {
                            "type":"string",
                            "format":"date-time"
                        },
                        "modified": {
                            "type":"string",
                            "format":"date-time"
                        },   
                        "alwaysAvailable": {
                            "description":"defines if the content object is always shown 
                                           even it is not translated in the requested language"
                            "type":"boolean",
                            "default": "false"
                        },
                        "remoteId": {
                            "type":"string"
                        },
                        "fields": {
                            "description":"the collection of fields",
                            "type":"array",
                            "items": {
                                "type":{
                                    "name":"Field",
                                    "properties": {
                                        "fieldDef": {
                                            "type":"string",
                                            "required":"true"
                                        }
                                        "id": {
                                            "type":"integer"
                                        }
                                        "value": {
                                            "type":"any"
                                        }
                                        "language": {
                                            "type":"string"
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

.. _UserGroupInput:

UserGroupInput JSON Schema
--------------------------

::

    {
        name:"UserGroupInput",
        properties: {
            "profile": {
                "type":
                 {
                    "name":"UserGroupProfile",
                    "properties": {
                        "initialLanguage" : 
                        {
                            "description":"if fields are provided in multiple languages 
                                           this attribute indicates the initial language",
                            "type":"string",
                        },
                        "alwaysAvailable": 
                        {
                            "description":"defines if the content object is always shown 
                                        even it is not translated in the requested language"
                            "type":"boolean",
                            "default": "false"
                        },
                        "remoteId": 
                        {
                            "description":"remoteId - if missing the system creates a new one"
                            "type":"string"
                        },
                        "fields": 
                        {
                            "description":"the collection of fields",
                            "type":"array",
                            "items": 
                            {
                                "type":
                                 {
                                    "name":"FieldValue",
                                     "properties": 
                                     {
                                         "fieldDef": 
                                         {
                                             "type":"string",
                                             "required":true
                                         }
                                         "value": {
                                             "description":"The value in a format according
                                                     to the field type of the field definition"
                                             "type":"any"
                                         }
                                         "language": {
                                             "type":"string"
                                         }
                                     }
                                 }
                            }
                        }
                    }
                }
            }
        }
    }

.. _UserInfo:

::

    {
        name:"UserInfo",
        properties: {
            "login": {
                "type": "string"
            },
            "email": {
                "type": "string"
            },
            "id": {
                "type": "integer"
            },
            "firstName": {
                "description":"Optional if available from UserProfile",
                "type": "string"
            },
            "lastName": {
                "description":"Optional if available from UserProfile",
                "type": "string"
            },
       }
    } 

.. _User:

User JSON Schema
-------------------

::

    {
        name:"User",
        properties: {
            "login": {
                "type": "string"
            },
            "email": {
                "type": "string"
            },
            "password": {
                "type": "string"
            },
            "hashAlg": {
                "type": "string"
            },
            "enabled": {
                "type": "boolean"
            },
            "groupIds": {
                "type": "array",
                "items": {
                   "type":"integer"
                }
            }
            "profile": {
                "type":
                 {
                    "name":"UserProfile",
                    "properties": 
                    {
                        "contentType" : 
                        {
                            "description":"the string identifier of the content type",
                            "type":"string",
                            "required":"true"
                        },
                        "name" : 
                        {
                            "description":"the default name of the content",
                            "type":"string",
                        },
                        "id": {
                            "type":"integer"
                        },
                        "ownerId": 
                        {
                            "description":"the user id of the user which owns this content object".
                            "type":"integer"
                        },
                        "sectionId": {
                            "type":"integer"
                        },
                        "state": 
                        {
                            "type":"string",
                            "enum": ["DRAFT","PUBLISHED","ARCHIVED"]
                        },
                        "versionNo": {
                            "type":"integer"
                        },
                        "creatorId": {
                            "type":"integer"
                        },
                        "created": {
                            "type":"string",
                            "format":"date-time"
                        },
                        "modified": {
                            "type":"string",
                            "format":"date-time"
                        },   
                        "alwaysAvailable": {
                            "description":"defines if the content object is always shown
                                           even it is not translated in the requested language"
                            "type":"boolean",
                            "default": "false"
                        },
                        "remoteId": {
                            "type":"string"
                        },
                        "fields": {
                            "description":"the collection of fields",
                            "type":"array",
                            "items": {
                                "type":{
                                    "name":"Field",
                                    "properties": {
                                        "fieldDef": {
                                            "type":"string",
                                            "required":"true"
                                        }
                                        "id": {
                                            "type":"integer"
                                        }
                                        "value": {
                                            "type":"any"
                                        }
                                        "language": {
                                            "type":"string"
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

.. _UserInput:

UserInput JSON Schema
---------------------

::

    {
        name:"UserInput",
        properties: {
            "login": {
                "type": "string"
            },
            "email": {
                "type": "string"
            },
            "password": {
                "type": "string"
            },
            "hashAlg": {
                "type": "string"
            },
            "enabled": {
                "type": "boolean"
            },
            "profile": {
                "type":
                 {
                    "name":"UserProfile",
                    "properties": {
                        "initialLanguage" : 
                        {
                            "description":"if fields are provided in multiple 
                                    languages this attribute indicates the initial language",
                            "type":"string",
                        },
                        "alwaysAvailable": 
                        {
                            "description":"defines if the content object is always shown even
                                          it is not translated in the requested language"
                            "type":"boolean",
                            "default": "false"
                        },
                        "remoteId": 
                        {
                            "description":"remoteId - if missing the system creates a new one"
                            "type":"string"
                        },
                        "fields": 
                        {
                            "description":"the collection of fields",
                            "type":"array",
                            "items": 
                            {
                                "type":
                                 {
                                    "name":"FieldValue",
                                     "properties": 
                                     {
                                         "fieldDef": 
                                         {
                                             "type":"string",
                                             "required":true
                                         }
                                         "value": {
                                             "description":"The value in a format according
                                                to the field type of the field definition"
                                             "type":"any"
                                         }
                                         "language": {
                                             "type":"string"
                                         }
                                     }
                                 }
                            }
                        }
                    }
                }
            }
        }
    }


.. _Limitation:

Limitation JSON Schema
----------------------

::

    {
        "name":"Limitation",
        "properties: {
            "identifier": {
                "type":"string"
            },
            "values": {
                "type": "array",
                "items": {
                    "type": "integer"
                }
            }
        }
    }

.. _Policy:

Policy JSON Schema
------------------

::

    {
        "name":"Policy",
        "properties: {
            "module": {
                "type":"string"
            },
            "function": {
                "type":"string"
            }
            "limitytions": {
                "type": "array",
                "items": {
                    "type": { "$ref"; "#Limitation" }
                }
            } 
        }
    }

.. _PolicyInput:

PolicyInput JSON Schema
-----------------------

::

    {
        "name":"PolicyInput",
        "properties: {
            "limitytions": {
                "type": "array",
                "items": {
                    "type": { "$ref"; "#Limitation" }
                }
            } 
        }
    }

.. _Role:

Role JSON Schema
----------------

::

    {
        "name":"Role",
        "properties: {
            "id": {
                "type":"integer"
            }
            "name": {
                "type":"string"
            }
            "groupIds": {
                "type":"array",
                "items: {
                    "type": "integer"
                }
            }
        }
    }


.. _ErrorMessage:

ErrorMessage JSON Schema
------------------------

::

    {
        "name":"ErrorMessage",
        "properties: {
            "errorCode": {
                "required" : true
                "type":"integer"
            }
            "errorMessage": {
                "type":"string"
            }
            "errorDescription": {
                "type":"string"
            }
        }
    }
