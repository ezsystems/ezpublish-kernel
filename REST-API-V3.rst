==============================
eZ Publish REST API DRAFT 1.50
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


General considerations
----------------------


Actions and Parameters
~~~~~~~~~~~~~~~~~~~~~~


Overview
--------

In the content module there are the root collections objects, locations, trash and sections 

===================================================== =================== ======================= ============================ ================ ==============
        :Resource:                                          POST                GET                  PATCH                       DELETE            COPY
----------------------------------------------------- ------------------- ----------------------- ---------------------------- ---------------- --------------
/                                                     .                   list root resources     .                            .                             
/content/objects                                      create new content  list/find content       .                            .            
/content/objects/<ID>                                 -                   load content            update content meta data     delete content   copy content
/content/objects/<ID>/translations                    create translation  list translations       .                            .                               
/content/objects/<ID>/<lang_code>                     .                   .                       .                            delete language
                                                                                                                               from content   
/content/objects/<ID>/versions                        create a new draft  load all versions       .                            .            
                                                      from an existing    (version infos)
                                                      version 
/content/objects/<ID>/currentversion                  .                   redirect to current v.  .                            .             
/content/objects/<ID>/versions/<no>                   .                   get a specific version  update a version/draft       delete version    create draft
                                                                                                                                                 from version
/content/objects/<ID>/versions/<no>/relations         create new relation load relations of vers. .                            .              
/content/objects/<ID>/versions/<no>/relations/<ID>    .                   load relation details   .                            delete relation
/content/objects/<ID>/locations                       create location     load locations of cont- .                            .
                                                                          ent                            
/content/locations                                    .                   list/find locations     .                            .                              
/content/locations/<path>                             .                   load a location         update location              delete location  copy subtree
/content/locations/<path>/children                    .                   load children           .                            .                  
/content/views                                        create view         list views              .                            .            
/content/views/<ID>                                   .                   get view                replace view                 delete view
/content/views/<ID>/results                           .                   get view results        .                            .          
/content/sections                                     create section      list all sections       .                            .                    
/content/sections/<ID>                                .                   load section            update setion                delete section
/content/trash                                        .                   list trash items        .                            empty trash
/content/trash/<ID>                                   .                   load trash item         untrash item                 delete from trsh
===================================================== =================== ======================= ============================ ================ ==============


Specification
-------------

General Error Codes
~~~~~~~~~~~~~~~~~~~
(see also HTTP 1.1 Specification)

:500: The server encountered an unexpected condition which prevented it from fulfilling the request - e.g. database down etc.
:501: The requested method was not implemented yet
:404: Requested resource was not found
:405: The request method is not available.  The available methods are returned for this resource
        

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
:Headers:
    :Accept:
         :application/vnd.ez.api.Content+xml:  if set all informations for the content object including the embedded current version are returned in xml format (see Content_)
         :application/vnd.ez.api.Content+json:  if set all informations for the content object including the embedded current version are returned in json format (see Content_)
         :application/vnd.ez.api.ContentInfo+xml:  if set all informations for the content object (excluding the current version) are returned in xml format (see Content_)
         :application/vnd.ez.api.ContentInfo+json:  if set all informations for the content object (excluding the current version) are returned in json format (see Content_)
    :Content-Type:
         :application/vnd.ez.api.ContentCreate+json: the ContentCreate_ schema encoded in json
         :application/vnd.ez.api.ContentCreate+xml: the ContentCreate_ schema encoded in xml
:Response: 

     .. parsed-literal::

          HTTP/1.1 201 Created  
          Location: /content/objects/<newID>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Content_      
          
:Error codes: 
       :400: If the Input does not match the input schema definition or the validation on a field fails, 
       :401: If the user is not authorized to create this object in this location
       :404: If a parent location in specified in the request body (see ContentCreate_) and it does not exist

XML Example
'''''''''''

::

    POST /content/objects HTTP/1.1
    Host: www.example.net
    Accept: application/vnd.ez.api.Content+xml
    Content-Type: application/vnd.ez.api.ContentCreate+xml
    Content-Length: xxx

    <ContentCreate>
      <ContentType href="/content/types/10"/>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <LocationCreate>
        <ParentLocation href="/content/locations/1/4/89" />
        <priority>0</priority>
        <hidden>false</hidden>
        <sortField>PATH</sortField>
        <sortOrder>ASC</sortOrder>
      </LocationCreate>
      <Section href="/content/sections/4"/>
      <alwaysAvailable>true</alwaysAvailable>
      <remoteId>remoteId12345678</remoteId>
      <fields>
        <field>
          <fieldDefinitionIdentifer>title</fieldDefinitionIdentifer>
          <languageCode>eng-US</languageCode>
          <value xsi:type="anyType">This is a title</value>
        </field>
        <field>
          <fieldDefinitionIdentifer>summary</fieldDefinitionIdentifer>
          <languageCode>eng-US</languageCode>
          <value xsi:type="anyType">This is a summary</value>
        </field>
        <field>
          <fieldDefinitionIdentifer>authors</fieldDefinitionIdentifer>
          <languageCode>eng-US</languageCode>
          <value xsi:type="anyType">
            <authors>
              <author name="John Doe" email="john.doe@example.net"/>
              <author name="Bruce Willis" email="bruce.willis@example.net"/>
            </authors>
          </value>
        </field>
      </fields>
    </ContentCreate>
    
    HTTP/1.1 201 Created
    Location: /content/objects/23
    ETag: "12345678"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+xml;charset=utf8
    Content-Type: application/vnd.ez.api.Content+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Content href="/content/objects/23" id="23"
      media-type="application/vnd.ez.api.Content+xml" remoteId="remoteId12345678">
      <ContentType href="/content/types/10" media-type="application/vnd.ez.api.ContentType+xml" />
      <Name>This is a title</Name>
      <Versions href="/content/objects/23/versions" media-type="application/vnd.ez.api.VersionList+xml" />
      <CurrentVersion href="/content/objects/23/currentversion"
        media-type="application/vnd.ez.api.Version+xml">
        <Version href="/content/objects/23/versions/1" media-type="application/vnd.ez.api.Version+xml">
          <VersionInfo>
            <id>123</id>
            <versionNo>1</versionNo>
            <status>DRAFT</status>
            <modificationDate>2012-02-12T12:30:00</modificationDate>
            <Creator href="/users/user/14" media-type="application/vnd.ez.api.User+xml" />
            <creationDate>2012-02-12T12:30:00</creationDate>
            <initialLanguageCode>eng-US</initialLanguageCode>
            <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
          </VersionInfo>
          <fields>
            <field>
              <id>1234</id>
              <fieldDefinitionIdentifer>title</fieldDefinitionIdentifer>
              <languageCode>eng-UK</languageCode>
              <value>This is a title</value>
            </field>
            <field>
              <id>1235</id>
              <fieldDefinitionIdentifer>summary</fieldDefinitionIdentifer>
              <languageCode>eng-UK</languageCode>
              <value>This is a summary</value>
            </field>
            <field>
              <fieldDefinitionIdentifer>authors</fieldDefinitionIdentifer>
              <languageCode>eng-US</languageCode>
              <value>
                <authors>
                  <author name="John Doe" email="john.doe@example.net" />
                  <author name="Bruce Willis" email="bruce.willis@example.net" />
                </authors>
              </value>
            </field>
          </fields>
          <Relations href="/content/objects/23/versions/1/relations" media-type="application/vnd.ez.api.RelationList+xml" />
        </Version>
      </CurrentVersion>
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/4/65" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/23/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/users/user/14" media-type="application/vnd.ez.api.User+xml" />
      <lastModificationDate>2012-02-12T12:30:00</lastModificationDate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
    </Content>

JSON Example
''''''''''''

::

    POST /content/objects
    Host: www.example.net
    Accept: application/vnd.ez.api.Content+json
    Content-Type: application/vnd.ez.api.ContentCreate+json
    Content-Length: xxx

    {
      "ContentCreate": {
        "ContentType": {
          "_href": "/content/types/10",
        },
        "mainLanguageCode": "eng-US",
        "LocationCreate": {
          "ParentLocation": { 
            "_href": "/content/locations/1/4/89" 
          },
          "priority": "0",
          "hidden": "false",
          "sortField": "PATH",
          "sortOrder": "ASC"
        }
        "Section": {
          "_href": "/content/sections/4",
        },
        "alwaysAvailable": "true",
        "remoteId": "remoteId12345678",
        "fields": {
          "field": [
            {
              "fieldDefinitionIdentifer": "title",
              "languageCode": "eng-US",
              "value": "This is a title"
            },
            {
              "fieldDefinitionIdentifer": "summary",
              "languageCode": "eng-US",
              "value": "This is a summary"
            },
            {
              "fieldDefinitionIdentifer": "authors",
              "languageCode": "eng-US",
              "value": {
                "authors": {
                  "author": [
                    {
                      "_name": "John Doe",
                      "_email": "john.doe@example.net"
                    },
                    {
                      "_name": "Bruce Willis",
                      "_email": "bruce.willis@example.net"
                    }
                  ]
                }
              }
            }
          ]
        }
      }
    }

    HTTP/1.1 201 Created
    Location: /content/objects/23
    ETag: "12345678"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+json;charset=utf8
    Content-Type: application/vnd.ez.api.Content+json
    Content-Length: xxx

    {
      "Content": {
        "_href": "/content/objects/23",
        "_id": "23",
        "_media-type": "application/vnd.ez.api.Content+json",
        "_remoteId": "qwert123",
        "ContentType": {
          "_href": "/content/types/10",
          "_media-type": "application/vnd.ez.api.ContentType+json"
        },
        "name": "This is a title",
        "Versions": {
          "_href": "/content/objects/23/versions",
          "_media-type": "application/vnd.ez.api.VersionList+json"
        },
        "CurrentVersion": {
          "_href": "/content/objects/23/currentversion",
          "_media-type": "application/vnd.ez.api.Version+json",
          "Version": {
            "_href": "/content/objects/23/versions/1",
            "_media-type": "application/vnd.ez.api.Version+json",
            "VersionInfo": {
              "id": "123",
              "versionNo": "1",
              "status": "DRAFT",
              "modificationDate": "2012-02-12T12:30:00",
              "creator": {
                "_href": "/users/user/14",
                "_media-type": "application/vnd.ez.api.User+json"
              },
              "creationDate": "2012-02-12T12:30:00",
              "initialLanguageCode": "eng-US",
              "Content": {
                "_href": "/content/objects/23",
                "_media-type": "application/vnd.ez.api.ContentInfo+json"
              }
            },
            "fields": {
              "field": [
                {
                  "id": "1234",
                  "fieldDefinitionIdentifer": "title",
                  "languageCode": "eng-UK",
                  "value": "This is a title"
                },
                {
                  "id": "1235",
                  "fieldDefinitionIdentifer": "summary",
                  "languageCode": "eng-UK",
                  "value": "This is a summary"
                },
                {
                  "fieldDefinitionIdentifer": "authors",
                  "languageCode": "eng-US",
                  "value": {
                    "authors": {
                      "author": [
                        {
                          "_name": "John Doe",
                          "_email": "john.doe@example.net"
                        },
                        {
                          "_name": "Bruce Willis",
                          "_email": "bruce.willis@example.net"
                        }
                      ]
                    }
                  }
                }
              ]
            }
          }
        },
        "Section": {
          "_href": "/content/sections/4",
          "_media-type": "application/vnd.ez.api.Section+json"
        },
        "MainLocation": {
          "_href": "/content/locations/1/4/65",
          "_media-type": "application/vnd.ez.api.Location+json"
        },
        "Locations": {
          "_href": "/content/objects/23/locations",
          "_media-type": "application/vnd.ez.api.LocationList+json"
        },
        "Owner": {
          "_href": "/users/user/14",
          "_media-type": "application/vnd.ez.api.User+json"
        },
        "lastModificationDate": "2012-02-12T12:30:00",
        "mainLanguageCode": "eng-US",
        "alwaysAvailable": "true"
      }
    }



List/Search Content
```````````````````
:Resource: /content/objects
:Method: GET
:Description: List/Search content objects (published version)
:Parameters:
    :q:               (required) query string in lucene format TBD
    :fields:          comma separated list of fields which should be returned in the items of the response (see Content)
    :responseGroups:  comma separated lists of predefined field groups (see REST API Spec v1)
    :limit:           only <limit> items will be returned started by offset
    :offset:          offset of the result set
    :sortField:       the field used for sorting TBD.
    :sortOrder:       DESC or ASC
:Response: TBD
:Error codes:
    :400: If the query string does not match the lucene query string format, In this case the response contains an ErrorMessage_
        
Load Content
````````````
:Resource: /content/objects/<ID> 
:Method: GET
:Description: Loads the content object for the given id. Depending on the Accept header the current version is embedded (i.e the current published version or if not exists the draft of the authenticated user)
:Headers:
    :Accept:
         :application/vnd.ez.api.Content+xml:  if set all informations for the content object including the embedded current version are returned in xml format (see Content_)
         :application/vnd.ez.api.Content+json:  if set all informations for the content object including the embedded current version are returned in json format (see Content_)
         :application/vnd.ez.api.ContentInfo+xml:  if set all informations for the content object (excluding the current version) are returned in xml format (see Content_)
         :application/vnd.ez.api.ContentInfo+json:  if set all informations for the content object (excluding the current version) are returned in json format (see Content_)
:Parameters:
    :fields: comma separated list of fields which should be returned in the response (see Content_)
    :responseGroups: comma separated lists of predefined field groups (see REST API Spec v1)
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Content_      
          
:Error Codes:
    :401: If the user is not authorized to read  this object. This could also happen if there is no published version yet and another user owns a draft of this content
    :404: If the ID is not found

XML Example
'''''''''''

::

    GET /content/objects/23 HTTP/1.1
    Accept: application/vnd.ez.api.ContentInfo+xml
    Content-length: 0

    HTTP/1.1 200 OK
    ETag: "12345678"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+xml;charset=utf8
    Content-Type: application/vnd.ez.api.ContentInfo+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Content href="/content/objects/23" id="23"
      media-type="application/vnd.ez.api.Content+xml" remoteId="qwert123">
      <ContentType href="/content/types/10" media-type="application/vnd.ez.api.ContentType+xml" />
      <Name>This is a title</Name>
      <Versions href="/content/objects/23/versions" media-type="application/vnd.ez.api.VersionList+xml" />
      <CurrentVersion href="/content/objects/23/currentversion"
        media-type="application/vnd.ez.api.Version+xml"/>
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/4/65" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/23/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/users/user/14" media-type="application/vnd.ez.api.User+xml" />
      <lastModificationDate>2012-02-12T12:30:00</lastModificationDate>
      <publishedDate>2012-02-12T15:30:00</publishedDate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
    </Content>
        


Update Content
``````````````
:Resource: /content/objects/<ID> 
:Method: PATCH or POST with header: X-HTTP-Method-Override: PATCH
:Description: this method updates the content metadata which is independent from a version.
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentInfo+xml:  if set all informations for the content object (excluding the current version) are returned in xml format (see Content_)
         :application/vnd.ez.api.ContentInfo+json:  if set all informations for the content object (excluding the current version) are returned in json format (see Content_)
    :If-Match: Causes to patch only if the specified etag is the current one
    :Content-Type: 
         :application/vnd.ez.api.ContentUpdate+json: the ContentUpdate_ schema encoded in json
         :application/vnd.ez.api.ContentUpdate+xml: the ContentUpdate_ schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Content_      
          

:Error Codes:
    :400: If the Input does not match the input schema definition.
    :401: If the user is not authorized to update this object
    :404: If the content id does not exist
    :412: If the current ETag does not match with the provided one in the If-Match header
    :415: If the media-type is not one of those specified in Headers

XML Example
'''''''''''
In this example
    - the main language is changed
    - a new section is assigned
    - the main location is changed
    - the always avalable flag is changed
    - the remoteId is changed
    - the owner of the content object is changed

::
 
    PATCH /content/objects/23 HTTP/1.1
    Host: www.example.net
    If-Match: "12345678"
    Accept: application/vnd.ez.api.ContentInfo+xml
    Content-Type: application/vnd.ez.api.ContentCreate+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentUpdate>
      <mainLanguageCode>ger-DE</mainLanguageCode>
      <Section href="/content/sections/3"/>
      <MainLocation href="/content/locations/1/13/55"/>
      <Owner href="/user/users/13"/>
      <alwaysAvailable>false</alwaysAvailable>
      <remoteId>qwert4321</remoteId>
    </ContentUpdate>
    
    HTTP/1.1 200 OK
    ETag: "12345699"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+xml;charset=utf8
    Content-Type: application/vnd.ez.api.ContentInfo+xml
    Content-Length: xxx
    
    <?xml version="1.0" encoding="UTF-8"?>
    <Content href="/content/objects/23" id="23"
      media-type="application/vnd.ez.api.Content+xml" remoteId="qwert4321">
      <ContentType href="/content/types/10" media-type="application/vnd.ez.api.ContentType+xml" />
      <Name>This is a title</Name>
      <Versions href="/content/objects/23/versions" media-type="application/vnd.ez.api.VersionList+xml" />
      <CurrentVersion href="/content/objects/23/currentversion"
        media-type="application/vnd.ez.api.Version+xml"/>
      <Section href="/content/sections/3" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/13/55" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/23/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/users/user/13" media-type="application/vnd.ez.api.User+xml" />
      <lastModificationDate>2012-02-12T12:30:00</lastModificationDate>
      <publishedDate>2012-02-12T15:30:00</publishedDate>
      <mainLanguageCode>ger-DE</mainLanguageCode>
      <alwaysAvailable>false</alwaysAvailable>
    </Content>

Delete Content
``````````````
:Resource: /content/objects/<ID> 
:Method: DELETE
:Description: The content is deleted. If the content has locations (which is required in 4.x) 
              on delete all locations assigned the content object are deleted via delete subtree.
:Response: 204
:Error Codes:
    :404: content object was not found
    :401: If the user is not authorized to delete this object

Copy content
````````````

:Resource:    /content/objects/<ID>
:Method:      COPY or POST with header: X-HTTP-Method-Override COPY
:Description: Creates a new content object as copy under the given parent location given in the destination header. 
:Headers:
    :Destination: A location resource to which the content object should be copied.
:Response: 

::

      HTTP/1.1 201 Created
      Location: /content/objects/<newId>

:Error codes: 
       :401: If the user is not authorized to copy this object to the given location
       :404: If the source or destination resource do not exist.  

Example
'''''''

::

    COPY /content/objects/23 HTTP/1.1
    Host: api.example.com
    Destination: /content/locations/1/4/78

    HTTP/1.1 201 Created
    Location: /content/objects/<newId>


Managing Versions
~~~~~~~~~~~~~~~~~

Get Current Version
```````````````````
:Resource: /content/objects/<ID>/currentversion
:Method: GET
:Description: Redirects to the current version of the content object
:Response: 

::

    HTTP/1.1 307 Temporary Redirect
    Location: /content/objects/<ID>/version/<current_version_no>

:Error Codes:
     :404: If the resource does not exist


List Versions
`````````````
:Resource: /content/objects/<ID>/versions
:Method: GET
:Description: Returns a list of all versions of the content. This method does not include fields and relations in the Version elements of the response.
:Headers:
    :Accept:
         :application/vnd.ez.api.VersionList+xml:  if set the version list is returned in xml format (see VersionList_)
         :application/vnd.ez.api.VersionList+json:  if set the version list is returned in json format 
:Response: 

    .. parsed-literal::

        HTTP/1.1 200 OK
        Content-Type: <depending on accept header>
        Content-Length: <length>
        VersionList_

:Error Codes:
     :401: If the user has no permission to read the versions

XML Example
'''''''''''

::

    GET /content/objects/23/versions HTTP/1.1
    Host: api.example.com
    Accept: application/vnd.ez.api.VersionList+xml

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.VersionList+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <VersionList href="/content/objects/23/versions" media-type="application/vnd.ez.api.VersionList+xml">
      <Version href="/content/objects/23/versions/1" media-type="application/vnd.ez.api.Version+xml">
        <VersionInfo>
          <id>12</id>
          <versionNo>1</versionNo>
          <status>ARCHIVED</status>
          <modificationDate>2012-02-15T12:00:00</modificationDate>
          <Creator href="/users/user/8" media-type="application/vnd.ez.api.User+xml"/>
          <creationDate>22012-02-15T12:00:00</creationDate>
          <initialLanguageCode>eng-US</initialLanguageCode>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml"/>
        </VersionInfo>
      </Version>
      <Version href="/content/objects/23/versions/2" media-type="application/vnd.ez.api.Version+xml">
        <VersionInfo>
          <id>22</id>
          <versionNo>2</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2012-02-17T12:00:00</modificationDate>
          <Creator href="/users/user/8" media-type="application/vnd.ez.api.User+xml"/>
          <creationDate>22012-02-17T12:00:00</creationDate>
          <initialLanguageCode>eng-US</initialLanguageCode>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml"/>
        </VersionInfo>
      </Version>
      <Version href="/content/objects/23/versions/3" media-type="application/vnd.ez.api.Version+xml">
        <VersionInfo>
          <id>44</id>
          <versionNo>3</versionNo>
          <status>DRAFT</status>
          <modificationDate>2012-02-19T12:00:00</modificationDate>
          <Creator href="/users/user/65" media-type="application/vnd.ez.api.User+xml"/>
          <creationDate>22012-02-19T12:00:00</creationDate>
          <initialLanguageCode>fra-FR</initialLanguageCode>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml"/>
        </VersionInfo>
      </Version>
      <Version href="/content/objects/23/versions/4" media-type="application/vnd.ez.api.Version+xml">
        <VersionInfo>
          <id>45</id>
          <versionNo>4</versionNo>
          <status>DRAFT</status>
          <modificationDate>2012-02-20T12:00:00</modificationDate>
          <Creator href="/users/user/44" media-type="application/vnd.ez.api.User+xml"/>
          <creationDate>22012-02-20T12:00:00</creationDate>
          <initialLanguageCode>ger-DE</initialLanguageCode>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml"/>
        </VersionInfo>
      </Version>
    <VersionList>

Load Version
````````````
:Resource: /content/objects/<ID>/versions/<versionNo>
:Method: GET
:Description: Loads a specific version of a content object. This method returns  fields and relations
:Parameters: 
    :fields: comma separated list of fields which should be returned in the response (see Content)
    :responseGroups: alternative: comma separated lists of predefined field groups (see REST API Spec v1)
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Headers:
    :If-Match: <etag> Only return the version if the <etag> is the current one
    :Accept:
         :application/vnd.ez.api.Version+xml:  if set the version list is returned in xml format (see VersionList_)
         :application/vnd.ez.api.Version+json:  if set the version list is returned in json format 
:Response: 

    .. parsed-literal::

        HTTP/1.1 200 OK
        Content-Type: <depending on accept header>
        Content-Length: <length>
        Version_

:Error Codes:
    :401: If the user is not authorized to read  this object
    :404: If the ID or version is not found
    :304: If the etag does not match the current one

XML Example
'''''''''''

::

    GET /content/objects/23/versions/4 HTTP/1.1
    Host: api.example.com
    Accept: application/vnd.ez.api.Version+xml
       
    HTTP/1.1 200 OK
    Accept-Patch: application/vnd.ez.api.VersionUpdate+xml
    ETag: "a3f2e5b7"
    Content-Type: application/vnd.ez.api.Version+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Version href="/content/objects/23/versions/4" media-type="application/vnd.ez.api.Version+xml">
      <VersionInfo>
        <id>45</id>
        <versionNo>4</versionNo>
        <status>DRAFT</status>
        <modificationDate>2012-02-20T12:00:00</modificationDate>
        <Creator href="/users/user/44" media-type="application/vnd.ez.api.User+xml" />
        <creationDate>22012-02-20T12:00:00</creationDate>
        <initialLanguageCode>ger-DE</initialLanguageCode>
        <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
      </VersionInfo>
      <Fields>
        <field>
          <id>1234</id>
          <fieldDefinitionIdentifer>title</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>Titel</value>
        </field>
        <field>
          <id>1235</id>
          <fieldDefinitionIdentifer>summary</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>Dies ist eine Zusammenfassungy</value>
        </field>
        <field>
          <fieldDefinitionIdentifer>authors</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>
            <authors>
              <author name="Klaus Mustermann" email="klaus.mustermann@example.net" />
            </authors>
          </value>
        </field>
      </Fields>
      <Relations  href="/content/objects/23/relations"  media-type="application/vnd.ez.api.RelationList+xml">>
        <Relation href="/content/objects/23/relations/32" media-type="application/vnd.ez.api.Relation+xml">
          <SourceContent href="/content/objects/23"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <DestinationContent href="/content/objects/45"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <RelationType>COMMON</RelationType>
        </Relation>
      </Relations>
    </Version>


            
Update Version
``````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: A specific draft is updated. 
:Parameters: 
    :fields: comma separated list of fields which should be returned in the response (see Content)
    :responseGroups: alternative: comma separated lists of predefined field groups (see REST API Spec v1)
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Headers:
    :Accept:
         :application/vnd.ez.api.Version+xml:  if set the updated version is returned in xml format (see Version_)
         :application/vnd.ez.api.Version+json:  if set the updated version returned in json format (see Version_)
    :If-Match: Causes to patch only if the specified etag is the current one
    :Content-Type: 
         :application/vnd.ez.api.VersionUpdate+json: the VersionUpdate_ schema encoded in json
         :application/vnd.ez.api.VersionUpdate+xml: the VersionUpdate_ schema encoded in xml
:Response: 

    .. parsed-literal::

        HTTP/1.1 200 OK
        ETag: "<new etag>"
        Accept-Patch: application/vnd.ez.api.VersionUpdate+(json|xml)
        Content-Type: <depending on accept header>
        Content-Length: <length>
        Version_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update this version  
    :403: If the version is not allowed to change - i.e is not a DRAFT
    :404: If the content id or version id does not exist
    :412: If the current ETag does not match with the provided one in the If-Match header
    :415: If the media-type is not one of those specified in Headers
        

XML Example
'''''''''''

::

    PATCH /content/objects/23/versions/4 HTTP/1.1
    Host: www.example.net
    If-Match: "a3f2e5b7"
    Accept: application/vnd.ez.api.Version+xml
    Content-Type: application/vnd.ez.api.VersionUpdate+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <VersionUpdate xmlns:p="http://ez.no/API/Values"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://ez.no/API/Values ../VersionUpdate.xsd ">
      <modificationDate>2001-12-31T12:00:00</modificationDate>
      <fields>
        <field>
          <id>1234</id>
          <fieldDefinitionIdentifer>title</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>Neuer Titel</value>
        </field>
        <field>
          <id>1235</id>
          <fieldDefinitionIdentifer>summary</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>Dies ist eine neue Zusammenfassungy</value>
        </field>
      </fields>
    </VersionUpdate>

    HTTP/1.1 200 OK
    Accept-Patch: application/vnd.ez.api.VersionUpdate+xml
    ETag: "a3f2e5b9"
    Content-Type: application/vnd.ez.api.Version+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Version href="/content/objects/23/versions/4" media-type="application/vnd.ez.api.Version+xml">
      <VersionInfo>
        <id>45</id>
        <versionNo>4</versionNo>
        <status>DRAFT</status>
        <modificationDate>2012-02-20T12:00:00</modificationDate>
        <Creator href="/users/user/44" media-type="application/vnd.ez.api.User+xml" />
        <creationDate>22012-02-20T12:00:00</creationDate>
        <initialLanguageCode>ger-DE</initialLanguageCode>
        <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
      </VersionInfo>
      <Fields>
        <field>
          <id>1234</id>
          <fieldDefinitionIdentifer>title</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>Neuer Titel</value>
        </field>
        <field>
          <id>1235</id>
          <fieldDefinitionIdentifer>summary</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>Dies ist eine neuse Zusammenfassungy</value>
        </field>
        <field>
          <fieldDefinitionIdentifer>authors</fieldDefinitionIdentifer>
          <languageCode>ger-DE</languageCode>
          <value>
            <authors>
              <author name="Klaus Mustermann" email="klaus.mustermann@example.net" />
            </authors>
          </value>
        </field>
      </Fields>
      <Relations>
        <Relation href="/content/object/32/versions/2/relations/43" media-type="application/vnd.ez.api.Relation+xml">
          <SourceContent href="/content/objects/23"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <DestinationContent href="/content/objects/45"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <RelationType>COMMON</RelationType>
        </Relation>
      </Relations>
    </Version>


Create a Draft from a Version
`````````````````````````````

:Resource: /content/objects/<ID>/versions/<no>
:Method: COPY or POST with header X-HTTP-Method-Override: COPY
:Description: The system creates a new draft version as a copy from the given version
:Response:

    .. parsed-literal::
 
        HTTP/1.1 201 Created
        Location: /content/objects/<ID>/versions/<new-versionNo> 
        Version_

:Error Codes:
    :401: If the user is not authorized to update this object  
    :403: If the given version is in status DRAFT
    :404: If the content object was not found

Delete Content Version
``````````````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: DELETE
:Description: The content  version is deleted
:Response: 

::

    HTTP/1.1 204 No Content
    
:Error Codes:
    :404: if the content object or version nr was not found
    :401: If the user is not authorized to delete this version 
    :403: If the version is in state published

Publish a content version
`````````````````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: PUBLISH or POST with header X-HTTP-Method-Override: PUBLISH
:Description: The content version is published
:Response: 

::

    HTTP/1.1 204 No Content

:Error Codes:
    :404: if the content object or version nr was not found
    :401: If the user is not authorized to publish this version
    :403: If the version is not a draft

Managing Relations
~~~~~~~~~~~~~~~~~~

Load relations of content
`````````````````````````
:Resource: /content/objects/<ID>/relations
:Method: GET
:Description: redirects to the relations of the current version
:Response: 

::

    HTTP/1.1 307 Temporary Redirect
    Location: /content/objects/<ID>/versions/<currentversion>/relations

:Error Codes:
:401: If the user is not authorized to read  this object
:404: If the content object was not found

Load relations of version
`````````````````````````
:Resource: /content/objects/<ID>/versions/<no>/relations
:Method: GET
:Description: loads the relations of the given version
:Parameters:
    :offset: the offset of the result set
    :limit: the number of relations returned
:Headers:
    :Accept:
         :application/vnd.ez.api.RelationList+xml:  if set the relation is returned in xml format (see RelationList_)
         :application/vnd.ez.api.RelationList+json:  if set the relation is returned in json format (see RelationList_)
:Response: 

    .. parsed-literal::

        HTTP/1.1 200 OK
        Content-Type: <depending on Accept header>
        Content-Length: xxx
        RelationList_ 

:Error Codes:
:401: If the user is not authorized to read  this object
:404: If the content object was not found

XML Example
'''''''''''

::

    GET /content/objects/23/versions/2/relations HTTP/1.1
    Accept: application/vnd.ez.api.RelationList+xml

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.RelationList+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Relations href="/content/object/32/versions/2/relations" media-type="application/vnd.ez.api.RelationList+xml">
        <Relation href="/content/object/32/versions/2/relations/43" media-type="application/vnd.ez.api.Relation+xml">
          <SourceContent href="/content/objects/23"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <DestinationContent href="/content/objects/45"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <RelationType>COMMON</RelationType>
        </Relation>
        <Relation href="/content/object/32/versions/2/relations/98" media-type="application/vnd.ez.api.Relation+xml">
          <SourceContent href="/content/objects/23"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <DestinationContent href="/content/objects/87"
            media-type="application/vnd.ez.api.ContentInfo+xml" />
          <sourceFieldDefinitionIdentifier>body</sourceFieldDefinitionIdentifier>  
          <RelationType>EMBED</RelationType>
        </Relation>
    </Relations>



Load a relation
```````````````
:Resource: /content/objects/<ID>/versions/<no>/relations/<ID>
:Method: GET
:Description: loads a relation for the given content object
:Headers:
    :Accept:
         :application/vnd.ez.api.Relation+xml:  if set the relation is returned in xml format (see Relation_)
         :application/vnd.ez.api.Relation+json:  if set the relation is returned in json format (see Relation_)
:Response: 

    .. parsed-literal::

        HTTP/1.1 200 OK
        Content-Type: <depending on Accept header>
        Content-Length: xxx
        Relation_ (relationValueType(

:Error Codes:
    :404: If the  object with the given id or the relation does not exist
    :401: If the user is not authorized to read this object  
        
Create a new Relation
`````````````````````
:Resource: /content/objects/<ID>/versions/<no>/relations
:Method: POST
:Description: Creates a new relation of type COMMON for the given draft. 
:Headers:
    :Accept:
         :application/vnd.ez.api.Relation+xml:  if set the updated version is returned in xml format (see RelationCreate_)
         :application/vnd.ez.api.Relation+json:  if set the updated version returned in json format (see RelationCreate_)
    :Content-Type: 
         :application/vnd.ez.api.RelationCreate+xml: the RelationCreate (see RelationCreate_) schema encoded in xml
         :application/vnd.ez.api.RelationCreate+json: the RelationCreate (see RelationCreate_) schema encoded in json
:Response: 

    .. parsed-literal::

        HTTP/1.1 201 Created
        Location: /content/objects/<ID>/versions/<no>/relations/<newId>
        Content-Type: <depending on Accept header>
        Content-Length: xxx
        Relation_ (relationValueType(

:Error Codes:
    :401: If the user is not authorized to update this content object
    :403: If a relation to the destId already exists or the destId does not exist or the version is not a draft.
    :404: If the  object or version with the given id does not exist

XML Example
'''''''''''

::

    POST /content/objects/23/versions/4/relations HTTP/1.1
    Accept: application/vnd.ez.api.Relation+xml
    Content-Type: application/vnd.ez.api.RelationCreate+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <RelationCreate>
      <Destination href="/content/objects/66"/>
    </RelationCreate>

    HTTP/1.1 201 Created
    Location: /content/objects/23/versions/4/relations
    Content-Type: application/vnd.ez.api.RelationCreate+xml
    Content-Length: xxx
    
    <?xml version="1.0" encoding="UTF-8"?>
    <Relation href="/content/object/32/versions/2/relations/66" media-type="application/vnd.ez.api.Relation+xml">
      <SourceContent href="/content/objects/23"
        media-type="application/vnd.ez.api.ContentInfo+xml" />
      <DestinationContent href="/content/objects/66"
        media-type="application/vnd.ez.api.ContentInfo+xml" />
      <RelationType>COMMON</RelationType>
    </Relation>
 

Delete a relation
`````````````````
:Resource: /content/objects/<ID>/versions/<versionNo>/relations/<ID>
:Method: DELETE
:Description: Deletes a relation of the given draft.
:Response: 

    ::

        HTTP/1.1 204 No Content

:Error Codes:
    :404: content object was not found or the relation was not found in the given version
    :401: If the user is not authorized to delete this relation 
    :403: If the relation is not of type COMMON or the given version is not a draft
        


Managing Locations
~~~~~~~~~~~~~~~~~~

Create a new location for a content object
``````````````````````````````````````````
:Resource: /content/objects/<ID>/locations
:Method: POST
:Description: Creates a new location for the given content object
:Headers:
    :Accept:
         :application/vnd.ez.api.Location+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.Location+json:  if set the new location is returned in json format (see Location_)
    :Content-Type:
         :application/vnd.ez.api.LocationCreate+json: the LocationCreate_ schema encoded in json
         :application/vnd.ez.api.LocationCreate+xml: the LocationCreate_ schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 201 Created  
          Location: /content/locations/<newPath>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this location  
    :403: If a location under the given parent id already exists

XML Example
'''''''''''

::

    POST /content/objects/23/locations HTTP/1.1
    Accept: application/vnd.ez.api.Location+xml
    Content-Type: application/vnd.ez.api.LocationCreate+xml
    Contnt-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationCreate>
      <ParentLocation href="/content/locations/1/5/73" />
      <priority>0</priority>
      <hidden>false</hidden>
      <sortField>PATH</sortField>
      <sortOrder>ASC</sortOrder>
    </LocationCreate>


    HTTP/1.1 201 Created
    Location: /content/locations/1/5/73/133
    ETag: "2345563422"
    Accept-Patch: application/vnd.ez.api.LocationUpdate+xml
    Content-Type: application/vnd.ez.api.Location+xml
    Contnt-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Location href="/content/locations/1/5/73/133" media-type="application/vnd.ez.api.Location+xml">
      <id>133</id>
      <priority>0</priority>
      <hidden>false</hidden>
      <invisible>false</invisible>
      <ParentLocation href="/content/locations/1/5/73" media-type="application/vnd.ez.api.Location+xml"/>
      <pathString>/1/5/73/133</pathString>
      <subLocationModificationDate>2001-01-01T12:45:00</subLocationModificationDate>
      <depth>4</depth>
      <childCount>0</childCount>
      <remoteId>remoteId-qwert567</remoteId>
      <Content href="/content/objects/23" media-type="application/vnd.ez.api.Content+xml"/>
      <sortField>PATH</sortField>
      <sortOrder>ASC</sortOrder>
    </Location>
        
 
        
Get locations for a content object
``````````````````````````````````
:Resource: /content/objects/<ID>/locations
:Method: GET
:Description: loads all locations for the given content object
:Headers:
    :Accept:
         :application/vnd.ez.api.LocationList+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.LocationList+json:  if set the new location is returned in json format (see Location_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_  (locationListType)     

:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object  

XML Example
'''''''''''

::

    GET /content/objects/23/locations HTTP/1.1
    Accept: application/vnd.ez.api.LocationList+xml

    HTTP/1.1 200 OK
    ETag: "<etag>"
    Content-Type:  application/vnd.ez.api.LocationList+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationList href="/content/objects/23/locations" media-type="application/vnd.ez.api.LocationList+xml">
      <Location href="/content/locations/1/2/56" media-type="application/vnd.ez.api.Location+xml"/>
      <Location href="/content/locations/1/4/73/133" media-type="application/vnd.ez.api.Location+xml"/>
    </LocationList>
        

Load location 
`````````````
:Resource: /content/locations/<path>
:Method: GET
:Description: loads the location for the given path
:Headers:
    :Accept:
         :application/vnd.ez.api.Location+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.Location+json:  if set the new location is returned in json format (see Location_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Location: /content/locations/<path>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_      

:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to read this location  

XML Example
'''''''''''

::

    GET /content/locations/1/4/73/133 HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Location+xml
    
    HTTP/1.1 200 OK
    ETag: "2345563422"
    Accept-Patch: application/vnd.ez.api.LocationUpdate+xml
    Content-Type: application/vnd.ez.api.Location+xml
    Contnt-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Location href="/content/locations/1/5/73/133" media-type="application/vnd.ez.api.Location+xml">
      <id>133</id>
      <priority>0</priority>
      <hidden>false</hidden>
      <invisible>false</invisible>
      <ParentLocation href="/content/locations/1/5/73" media-type="application/vnd.ez.api.Location+xml"/>
      <pathString>/1/5/73/133</pathString>
      <subLocationModificationDate>2001-01-01T12:45:00</subLocationModificationDate>
      <depth>4</depth>
      <childCount>0</childCount>
      <remoteId>remoteId-qwert567</remoteId>
      <Content href="/content/objects/23" media-type="application/vnd.ez.api.Content+xml"/>
      <sortField>PATH</sortField>
      <sortOrder>ASC</sortOrder>
    </Location>
     

Update location
```````````````
:Resource: /content/locations/<ID>
:Method: PATCH or POST with header: X-HTTP-Method-Override: PATCH
:Description: updates the location,  this method can also be used to hide/unhide a location via the hidden field in the LocationUpdate_
:Headers:
    :Accept:
         :application/vnd.ez.api.Location+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.Location+json:  if set the new location is returned in json format (see Location_)
    :Content-Type:
         :application/vnd.ez.api.LocationUpdate+json: the LocationUpdate_ schema encoded in json
         :application/vnd.ez.api.LocationUpdate+xml: the LocationUpdate_ schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Location: /content/locations/<path>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_      

:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to update this location  


XML Example
'''''''''''

::

    PATCH /content/locations/1/5/73/133 HTTP/1.1
    Host: www.example.net
    If-Match: "12345678"
    Accept: application/vnd.ez.api.Location+xml
    Content-Type: :application/vnd.ez.api.LocationUpdate+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationUpdate>
      <priority>3</priority>
      <hidden>true</hidden>
      <remoteId>remoteId-qwert999</remoteId>
      <sortField>CLASS</sortField>
      <sortOrder>DESC</sortOrder>
    </LocationUpdate>


    HTTP/1.1 200 OK
    ETag: "2345563444"
    Accept-Patch: application/vnd.ez.api.LocationUpdate+xml
    Content-Type: application/vnd.ez.api.Location+xml
    Content-Length: xxx
    
    <?xml version="1.0" encoding="UTF-8"?>
    <Location href="/content/locations/1/5/73/133" media-type="application/vnd.ez.api.Location+xml">
      <id>133</id>
      <priority>3</priority>
      <hidden>true</hidden>
      <invisible>true</invisible>
      <ParentLocation href="/content/locations/1/5/73" media-type="application/vnd.ez.api.Location+xml"/>
      <pathString>/1/5/73/133</pathString>
      <subLocationModificationDate>2001-01-01T12:45:00</subLocationModificationDate>
      <depth>4</depth>
      <childCount>0</childCount>
      <remoteId>remoteId-qwert999</remoteId>
      <Content href="/content/objects/23" media-type="application/vnd.ez.api.Content+xml"/>
      <sortField>CLASS</sortField>
      <sortOrder>ASC</sortOrder>
    </Location>
     

Get child locations 
```````````````````
:Resource: /content/locations/<path>/children
:Method: GET
:Description: loads all child locations for the given parent location
:Parameters:
    :offset: the offset of the result set
    :limit: the number of locations returned
:Headers:
    :Accept:
         :application/vnd.ez.api.LocationList+xml:  if set the new location list is returned in xml format (see Location_)
         :application/vnd.ez.api.LocationList+json:  if set the new location list is returned in json format (see Location_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_      

:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object  

XML Example
'''''''''''

::

    GET /content/locations/1/2/54/children HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.LocationList+xml

    HTTP/1.1 200 OK
    ETag: "<etag>"
    Content-Type:  application/vnd.ez.api.LocationList+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationList href="/content/locations/1/2/54" media-type="application/vnd.ez.api.LocationList+xml">
      <Location href="/content/locations/1/2/54/134" media-type="application/vnd.ez.api.Location+xml"/>
      <Location href="/content/locations/1/4/54/143" media-type="application/vnd.ez.api.Location+xml"/>
    </LocationList>

Move Subtree
````````````
:Resource: /content/locations/<path>
:Method: MOVE or POST with header X-HTTP-Method-Override: MOVE
:Description: moves the location to another parent. The destination can also be /content/trash where the location is put into the trash.
:Headers:
    :Destination: A parent location resource to which the location is moved
:Response: 

    ::
    
        HTTP/1.1 201 Created
        Location: /content/locations/<newPath>
        or Location: /content/trash/<ID>

:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to move this location  
        
Copy Subtree
````````````
:Resource: /content/locations/<path>
:Method: COPY or POST with header X-HTTP-Method-Override: COPY
:Description: copies the subtree to another parent
:Headers:
    :Destination: A parent location resource to which the location is moved
:Response: 

    ::
    
        HTTP/1.1 201 Created
        Location: /content/locations/<newPath>

:Error Codes:
    :404: If the location with the given id does not exist
    :401: If the user is not authorized to move this location  

Swap Location
`````````````
:Resource: /content/locations/<ID>
:Method: SWAP or POST with header X-HTTP-Method-Override: SWAP
:Description: Swaps the content of the location with the content of the given location
:Headers:
    :Destination: A location resource with which the content is swapped
:Response: 

    ::
    
        HTTP/1.1 204 No Content

:Error Codes:
    :404: If the location with the given id does not exist
    :401: If the user is not authorized to swap this location  

Delete Subtree
``````````````
:Resource: /content/locations/<path>
:Method: DELETE
:Description: Deletes the complete subtree for the given path. Every content object is deleted which does not have any other location. Otherwise the deleted location is removed from the content object. The children a recursively deleted.
:Response: 204
:Response: 

    ::
    
        HTTP/1.1 204 No Content

:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to delete this subtree  

Views
~~~~~

Create View
```````````
:Resource: /content/views
:Method:  POST
:Description: executes a query and returns view including the results 
              The View_ input reflects the criteria model of the public API.
:Headers:
    :Accept:
        :application/vnd.ez.api.View+xml: the view in xml format (see View_)
        :application/vnd.ez.api.View+json: the view in xml format (see View_)
    :Content-Type: 
        :application/vnd.ez.api.ViewInput+xml: the view input in xml format (see View_)
        :application/vnd.ez.api.ViewInput+json: the view input in xml format (see View_)
:Response:

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<new etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          View_

:Error codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_

XML Example
'''''''''''

Perform a query on articles with a specific title.

::

    POST /content/views HTTP/1.1
    Accept: application/vnd.ez.api.View+xml
    Content-Type: application/vnd.ez.api.ViewInput+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ViewInput>
      <Query>
        <Criteria>
          <AND>
            <ContentTypeIdentifierCriterion>article</ContentTypeIdentifierCriterion>
            <FieldCritierion>
              <operator>EQ</operator>
              <target>title</target>
              <value>Title</value>
            </FieldCritierion>
          </AND>
        </Criteria>
        <limit>10</limit>
        <offset>0</offset>
        <SortClauses>
          <SortClause>
            <SortField>NAME</SortField>
          </SortClause>
        </SortClauses>
      </Query>
    </ViewInput>

    
    HTTP/1.1 201 Created
    Location: /content/views/view1234
    Content-Type: application/vnd.ez.api.View+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <View href="/content/views/ArticleTitleView" media-type="vnd.ez.api.View+xml">
      <identifier>ArticleTitleView</identifier>
      <Query>
        <Criteria>
          <AND>
            <ContentTypeIdentifierCriterion>article
            </ContentTypeIdentifierCriterion>
            <FieldCritierion>
              <operator>EQ</operator>
              <target>title</target>
              <value>Title</value>
            </FieldCritierion>
          </AND>
        </Criteria>
        <limit>10</limit>
        <offset>0</offset>
        <SortClauses>
          <SortClause>
            <SortField>NAME</SortField>
          </SortClause>
        </SortClauses>
      </Query>
      <Result href="/content/views/view1234/results"
        media-type="vnd.ez.api.ViewResult+xml">
        <count>1</count>
        <Content href="/content/objects/23" id="23"
          media-type="application/vnd.ez.api.Content+xml" remoteId="qwert123"
          xmlns:p="http://ez.no/API/Values" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://ez.no/API/Values Content.xsd ">
          <ContentType href="/content/types/10"
            media-type="application/vnd.ez.api.ContentType+xml" />
          <Name>Name</Name>
          <Versions href="/content/objects/23/versions" media-type="application/vnd.ez.api.VersionList+xml" />
          <CurrentVersion href="/content/objects/23/currentversion"
            media-type="application/vnd.ez.api.Version+xml">
            <Version href="/content/objects/23/versions/2"
              media-type="application/vnd.ez.api.Version+xml">
              <VersionInfo>
                <id>123</id>
                <versionNo>2</versionNo>
                <status>PUBLISHED</status>
                <modificationDate>2001-12-31T12:00:00</modificationDate>
                <creator href="/users/user/14" media-type="application/vnd.ez.api.User+xml" />
                <creationDate>2001-12-31T12:00:00</creationDate>
                <initialLanguageCode>eng-UK</initialLanguageCode>
                <Content href="/content/objects/23"
                  media-type="application/vnd.ez.api.ContentInfo+xml" />
              </VersionInfo>
              <Fields>
                <field>
                  <id>1234</id>
                  <fieldDefinitionIdentifer>title</fieldDefinitionIdentifer>
                  <languageCode>eng-UK</languageCode>
                  <value>Title</value>
                </field>
                <field>
                  <id>1235</id>
                  <fieldDefinitionIdentifer>summary
                  </fieldDefinitionIdentifer>
                  <languageCode>eng-UK</languageCode>
                  <value>This is a summary</value>
                </field>
              </Fields>
              <Relations />
            </Version>
          </CurrentVersion>
          <Section href="/content/objects/23/section" media-type="application/vnd.ez.api.Section+xml" />
          <MainLocation href="/content/objects/23/mainlocation"
            media-type="application/vnd.ez.api.Location+xml" />
          <Locations href="/content/objects/23/locations"
            media-type="application/vnd.ez.api.LocationList+xml" />
          <Owner href="/users/user/14" media-type="application/vnd.ez.api.User+xml" />
          <PublishDate>2001-12-31T12:00:00</PublishDate>
          <LastModificationDate>2001-12-31T12:00:00</LastModificationDate>
          <MainLanguageCode>eng-UK</MainLanguageCode>
          <AlwaysAvailable>true</AlwaysAvailable>
        </Content>
      </Result>
    </View>



Managing Sections
~~~~~~~~~~~~~~~~~

Create a new Section
````````````````````
:Resource: /content/sections
:Method: POST
:Description: Creates a new section
:Headers:
    :Accept:
         :application/vnd.ez.api.Section+xml:  if set the new section is returned in xml format (see Section_)
         :application/vnd.ez.api.Section+json:  if set the new section is returned in json format (see Section_)
    :Content-Type:
         :application/vnd.ez.api.SectionInput+json: the Section_ input schema encoded in json
         :application/vnd.ez.api.SectionInput+xml: the Section_ input schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 201 Created
          Location: /content/section/<ID>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.SectionInput+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Section_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this section  
    :403: If a section with same identifier already exists

XML Example
'''''''''''

::

    POST /content/sections HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Section+xml
    Content-Type: application/vnd.ez.api.SectionInput+xml
    Content-Length: xxxx

    <?xml version="1.0" encoding="UTF-8"?>
    <SectionInput>
      <identifier>restricted</identifier>
      <name>Restricted</name>
    </SectionInput>
        
    HTTP/1.1 201 Created
    Location: /content/section/5
    ETag: "4567867894564356"
    Accept-Patch: application/vnd.ez.api.SectionInput+(json|xml)
    Content-Type:  application/vnd.ez.api.Section+xml
    Content-Length: xxxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Section href="/content/sections/5" media-type="vnd.ez.api.Section+xml">
      <sectionId>5</sectionId>
      <identifier>restricted</identifier>
      <name>Restriced</name>
    </Section>
       


Get Sections
````````````
:Resource: /content/sections
:Method: GET
:Description: Returns a list of all sections
:Headers:
    :Accept:
         :application/vnd.ez.api.SectionList+xml:  if set the new section list is returned in xml format (see Section_)
         :application/vnd.ez.api.SectionList+json:  if set the new section list is returned in json format (see Section_)
:Response:
 
    .. parsed-literal::

          HTTP/1.1 200
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Section_  (sectionListType)    

:Error Codes:
    :401: If the user has no permission to read the sections

XML Example
'''''''''''

::

    GET /content/sections
    Host: api.example.net
    Accept: application/vnd.ez.api.SectionList+xml

    HTTP/1.1 200 OK
    ETag: "43450986743098576"
    Content-Type: application/vnd.ez.api.SectionList+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <SectionList href="/content/sections" media-type="vnd.ez.api.SectionList+xml">
      <Section href="/content/sections/1" media-type="vnd.ez.api.Section+xml">
        <sectionId>1</sectionId>
        <identifier>standard</identifier>
        <name>Standard</name>
      </Section>
      <Section href="/content/sections/2" media-type="vnd.ez.api.Section+xml">
        <sectionId>2</sectionId>
        <identifier>users</identifier>
        <name>Users</name>
      </Section>
      <Section href="/content/sections/3" media-type="vnd.ez.api.Section+xml">
        <sectionId>3</sectionId>
        <identifier>media</identifier>
        <name>Media</name>
      </Section>
      <Section href="/content/sections/4" media-type="vnd.ez.api.Section+xml">
        <sectionId>4</sectionId>
        <identifier>setup</identifier>
        <name>Setup</name>
      </Section>
    </SectionList>

        
Get Section
```````````
:Resource: /content/sections/<ID>
:Method: GET
:Description: Returns the section given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.Section+xml:  if set the new section is returned in xml format (see Section_)
         :application/vnd.ez.api.Section+json:  if set the new section is returned in json format (see Section_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.SectionInput+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Section_  (sectionListType)    
:ErrorCodes:
    :401: If the user is not authorized to read this section  
    :404: If the section does not exist

XML Example
'''''''''''

::

    GET /content/sections/3 HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Section+xml

    HTTP/1.1 200 OK
    ETag: "4567867894564356"
    Accept-Patch: application/vnd.ez.api.SectionInput+(json|xml)
    Content-Type:  application/vnd.ez.api.Section+xml
    Content-Length: xxxx

    <?xml version="1.0" encoding="UTF-8"?>
    <Section href="/content/sections/3" media-type="vnd.ez.api.Section+xml">
      <sectionId>3</sectionId>
      <identifier>media</identifier>
      <name>Media</name>
    </Section>


Update a Section
````````````````
:Resource: /content/sections/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override
:Description: Updates a section
:Headers:
    :Accept:
         :application/vnd.ez.api.Section+xml:  if set the new section is returned in xml format (see Section_)
         :application/vnd.ez.api.Section+json:  if set the new section is returned in json format (see Section_)
    :Content-Type:
         :application/vnd.ez.api.SectionInput+json: the Section_ input schema encoded in json
         :application/vnd.ez.api.SectionInput+xml: the Section_ input schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 200
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.SectionInput+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Section_  (sectionListType)    

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
:Response: 

    ::
        
         HTTP/1.1 204 No Content
:Error Codes:
    :401: If the user is not authorized to delete this section
    :404: If the section does not exist

Managing Trash
~~~~~~~~~~~~~~

List TrashItems
```````````````
:Resource: /content/trash
:Method: GET
:Description: Returns a list of all trash items
:Headers:
    :Accept:
         :application/vnd.ez.api.LocationList+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.LocationList+json:  if set the new location is returned in json format (see Location_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_  (locationListType)     

:ErrorCodes: 
    :401: If the user has no permission to read the trash

Get TrashItem
`````````````
:Resource: /content/trash/<ID>
:Method: GET
:Description: Returns the trash item given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.Location+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.Location+json:  if set the new location is returned in json format (see Location_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Location_      
:Error Codes:
    :401: If the user has no permission to read the trash item
    :404: If the trash item with the given id does not exist

Untrash Item
````````````
:Resource: /content/trash/<ID>
:Method: MOVE or POST with header X-HTTP-Method-Override: MOVE
:Description: Restores a trashItem
:Headers:
        :Destination: if given the trash item is restored under this location otherwise under its orifinal parent location
:Response: 

    ::
    
        HTTP/1.1 201 Created
        Location: /content/locations/<newPath>
        
:Error Codes:
    :401: If the user is not authorized to restore this trash item  
    :403: if the given parent location does not exist
    :404: if the given trash item does not exist

Empty Trash
```````````
:Resource: /content/trash
:Method: DELETE
:Description: Empties the trash
:Response: 

    ::
  
        HTTP/1.1 204 No Content
    
:Error Codes:
    :401: If the user is not authorized to empty all trash items

Delete TrashItem
````````````````
:Resource: /content/trash/items/<ID>
:Method: DELETE
:Description: Deletes the given trash item
:Response: 

    ::
  
        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to empty the given trash item
    :404: if the given trash item does not exist

Content Types
=============

Overview
--------

================================================== =================== =================== ======================= =======================
      Resource                                           POST             GET                 PUT/PATCH               DELETE
-------------------------------------------------- ------------------- ------------------- ----------------------- -----------------------
/content/typegroups                                create new group    load all groups     .                       .            
/content/typegroups/<ID>                           .                   load group          update group            delete group
/content/typegroups/<ID>/types                     create content type list content types  .                       .                  
/content/types                                     copy content type   list content types  .                       .            
/content/types/<ID>                                create draft        load content type   .                       delete content type
/content/types/<ID>/draft                          publish draft       load draft          update draft            delete draft       
/content/types/<ID>/draft/fieldDefinitions         create field def.   .                   .                       .            
/content/types/<ID>/draft/fieldDefinitions/<ID>    .                   load field def.     update field definition delete field definition
================================================== =================== =================== ======================= =======================

Specification
-------------

Managing Content Type Groups
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create Content Type Group
`````````````````````````
:Resource: /content/typegroups
:Method: POST
:Description: Creates a new content type group 
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroup+xml:  if set the new section is returned in xml format (see ContentTypeGroup_)
         :application/vnd.ez.api.ContentTypeGroup+json:  if set the new section is returned in json format (see ContentTypeGroup_)
    :Content-Type:
         :application/vnd.ez.api.ContentTypeGroupInput+json: the ContentTypeGroup_ input schema encoded in json
         :application/vnd.ez.api.ContentTypeGroupInput+xml: the ContentTypeGroup_ input schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 201 Created
          Loction: /content/typegroups/<newId>
          Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this content type group
    :403: If a content type group with same identifier already exists


XML Example
'''''''''''

::

    POST /content/typegroups HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Type: application/vnd.ez.api.ContentTypeGroupInput+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroupInput>
      <identifier>newContentTypeGroup</identifier>
    </ContentTypeGroupInput>

    HTTP/1.1 201 Created
    Location: /content/typegroups/7
    Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+xml
    ETag: "9587649865938675"
    Content-Type: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroup href="/content/typesgroups/7" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
      <id>7</id>
      <identifier>newContentTypeGroup</identifier>
      <created>2012-02-31T12:45:00</created>
      <modified>2012-02-31T12:45:00</modified>
      <Creator href="/users/user/13" media-type="application/vnd.ez.api.User+xml"/>
      <Modifier href="/users/user/13" media-type="application/vnd.ez.api.User+xml"/>
      <ContentTypes href="/content/typegroups/7/types" media-type="application/vnd.ez.api.ContentTypeList+xml"/>
    </ContentTypeGroup>
     

Get Content Type Groups
```````````````````````
:Resource: /content/typegroups
:Method: GET
:Description: Returns a list of all content types groups
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroupList+xml:  if set the new section is returned in xml format (see ContentTypeGroup_)
         :application/vnd.ez.api.ContentTypeGroupList+json:  if set the new section is returned in json format (see ContentTypeGroup_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_  (contentTypeGroupListType)     

:Error Codes:
    :401: If the user has no permission to read the content types

XML Example
'''''''''''

::

    GET /content/typegroups HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.ContentTypeGroupList+xml

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.ContentTypeGroupList+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroupList href="/content/typegroups" media-type="application/vnd.ez.api.ContentTypeGroupList+xml">
      <ContentTypeGroup href="/content/typegroups/1" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
        <id>1</id>
        <identifier>Content</identifier>
        <created>2010-06-31T12:00:00</created>
        <modified>2010-07-31T12:00:00</modified>
        <Creator href="/users/user/13" media-type="application/vnd.ez.api.User+xml"/>
        <Modifier href="/users/user/6" media-type="application/vnd.ez.api.User+xml"/>
        <ContentTypes href="/content/typegroups/1/types" media-type="application/vnd.ez.api.ContentTypeList+xml"/>
      </ContentTypeGroup>
      <ContentTypeGroup href="/content/typegroups/2" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
        <id>2</id>
        <identifier>Media</identifier>
        <created>2010-06-31T14:00:00</created>
        <modified>2010-09-31T12:00:00</modified>
        <Creator href="/users/user/13" media-type="application/vnd.ez.api.User+xml"/>
        <Modifier href="/users/user/9" media-type="application/vnd.ez.api.User+xml"/>
        <ContentTypes href="/content/typegroups/2/types" media-type="application/vnd.ez.api.ContentTypeList+xml"/>
      </ContentTypeGroup>
    </ContentTypeGroupList>

        
Get Content Type Group
``````````````````````
:Resource: /content/typegroups/<ID>
:Method: GET
:Description: Returns the content type given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroup+xml:  if set the new section is returned in xml format (see ContentTypeGroup_)
         :application/vnd.ez.api.ContentTypeGroup+json:  if set the new section is returned in json format (see ContentTypeGroup_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+(json|xml)
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_      

:ErrorCodes:
    :401: If the user is not authorized to read this content type  
    :404: If the content type group does not exist

Update Content Type Group
`````````````````````````
:Resource: /content/typegroups/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates a content type group 
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroup+xml:  if set the new section is returned in xml format (see ContentTypeGroup_)
         :application/vnd.ez.api.ContentTypeGroup+json:  if set the new section is returned in json format (see ContentTypeGroup_)
    :Content-Type:
         :application/vnd.ez.api.ContentTypeGroupInput+json: the ContentTypeGroup_ input schema encoded in json
         :application/vnd.ez.api.ContentTypeGroupInput+xml: the ContentTypeGroup_ input schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this content type group
    :403: If a content type group with the given identifier already exists


XML Example
'''''''''''

::

    PATCH /content/typegroups/7 HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Type: application/vnd.ez.api.ContentTypeGroupInput+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroupInput>
      <identifier>updatedIdentifer</identifier>
    </ContentTypeGroupInput>

    HTTP/1.1 200 OK
    Location: /content/typegroups/7
    Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+xml
    ETag: "95876498659383245"
    Content-Type: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroup href="/content/typesgroups/7" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
      <id>7</id>
      <identifier>updatedIdentifer</identifier>
      <created>2012-02-31T12:45:00</created>
      <modified>2012-04-13T12:45:00</modified>
      <Creator href="/users/user/13" media-type="application/vnd.ez.api.User+xml"/>
      <Modifier href="/users/user/8" media-type="application/vnd.ez.api.User+xml"/>
      <ContentTypes href="/content/typegroups/7/types" media-type="application/vnd.ez.api.ContentTypeList+xml"/>
    </ContentTypeGroup>
     

Delete Content Type Group
`````````````````````````
:Resource: /content/typegroups/<ID>
:Method: DELETE
:Description: the given content type group is deleted
:Response: 

    ::
  
        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the content type group is not empty
    :404: If the content type does not exist


Managing Content Types
~~~~~~~~~~~~~~~~~~~~~~

Create Content Type
```````````````````
:Resource: /content/typegroups/<ID>/types
:Method: POST
:Description: Creates a new content type draft in the given content type group
:Parameters: :publish: (default false) If true the content type is published after creating
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentType+xml:  if set the new content type or draft is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentType+json:  if set the new content type or draft is returned in json format (see ContentType_)
    :Content-Type:
         :application/vnd.ez.api.ContentTypeCreate+json: the ContentTypeCreate_  schema encoded in json
         :application/vnd.ez.api.ContentTypeCreate+xml: the ContentTypeCreate_  schema encoded in xml
:Response: 
    If publish = false:

    .. parsed-literal::

          HTTP/1.1 201 Created
          Location: /content/types/<newId>/draft
          Accept-Patch:  application/vnd.ez.api.ContentTypeUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_      

    If publish = true:

    .. parsed-literal::

          HTTP/1.1 201 Created
          Location: /content/types/<newId>
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_      

:Error Codes:
    :400: - If the Input does not match the input schema definition,
          - If publish = true and the input is not complete e.g. no field definitions are provided 
    :401: If the user is not authorized to create this content type  
    :403: If a content type with same identifier already exists

XML Example
'''''''''''

::

    POST /content/typegroups/<ID> HTTP/1.1
    Accept: application/vnd.ez.api.ContentType
    Content-Type: application/vnd.ez.api.ContentTypeCreate
    Content-Length: xxx
    
    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeCreate>
      <identifier>newContentType</identifier>
      <names>
        <value languageCode="eng-US">New Content Type</value>
      </names>
      <descriptions>
        <value languageCode="eng-US">This is a description</value>
      </descriptions>
      <remoteId>remoteId-qwert548</remoteId>
      <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
      <nameSchema>&lt;title&gt;</nameSchema>
      <isContainer>true</isContainer>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
      <defaultSortField>PATH</defaultSortField>
      <defaultSortOrder>ASC</defaultSortOrder>
      <FieldDefinitions>
        <FieldDefinition>
          <identifier>title</identifier>
          <fieldType>ezstring</fieldType>
          <fieldGroup>content</fieldGroup>
          <position>1</position>
          <isTranslatable>true</isTranslatable>
          <isRequired>true</isRequired>
          <isInfoCollector>false</isInfoCollector>
          <defaultValue>New Title</defaultValue>
          <isSearchable>true</isSearchable>
          <names>
            <value languageCode="eng-US">Title</value>
          </names>
          <descriptions>
            <value languageCode="eng-US">This is the title</value>
          </descriptions>
        </FieldDefinition>
       <FieldDefinition>
          <identifier>summary</identifier>
          <fieldType>ezxmltext</fieldType>
          <fieldGroup>content</fieldGroup>
          <position>2</position>
          <isTranslatable>true</isTranslatable>
          <isRequired>false</isRequired>
          <isInfoCollector>false</isInfoCollector>
          <defaultValue></defaultValue>
          <isSearchable>true</isSearchable>
          <names>
            <value languageCode="eng-US">Summary</value>
          </names>
          <descriptions>
            <value languageCode="eng-US">This is the summary</value>
          </descriptions>
        </FieldDefinition>
       </FieldDefinitions>
    </ContentTypeCreate>

    HTTP/1.1 201 Created
    Location: /content/types/32/draft
    Accept-Patch:  application/vnd.ez.api.ContentTypeUpdate+(json|xml)
    ETag: "45674567543546"
    Content-Type: application/vnd.ez.api.ContentType+xml
    Content-Length: xxx


    <?xml version="1.0" encoding="UTF-8"?>
    <ContentType href="/content/types/32/draft" media-type="vnd.ez.api.ContentType+xml">
      <id>32</id>
      <status>DRAFT</status>
      <identifier>newContentType</identifier>
      <names>
        <value languageCode="eng-US">New Content Type</value>
      </names>
      <descriptions>
        <value languageCode="eng-US">This is a description</value>
      </descriptions>
      <creationDate>2001-01-01T16:37:00</creationDate>
      <modificationDate>2001-01-01T16:37:00</modificationDate>
      <Creator href="/user/users/13" media-type="vnd.ez.api.User+xml"/>
      <Modifier href="/user/users/13" media-type="vnd.ez.api.User+xml"/>
      <remoteId>remoteId-qwert548</remoteId>
      <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
      <nameSchema>&lt;title&gt;</nameSchema>
      <isContainer>true</isContainer>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
      <defaultSortField>PATH</defaultSortField>
      <defaultSortOrder>ASC</defaultSortOrder>
      <FieldDefinitions href="/content/types/32/draft/fielddefinitions" media-type="vnd.ez.api.FieldDefinitionList+xml">
        <FieldDefinition href="/content/types/32/draft/fielddefinitions/34" media-type="vnd.ez.api.FieldDefinition+xml">
          <id>34</id>
          <identifier>title</identifier>
          <fieldType>ezstring</fieldType>
          <fieldGroup>content</fieldGroup>
          <position>1</position>
          <isTranslatable>true</isTranslatable>
          <isRequired>true</isRequired>
          <isInfoCollector>false</isInfoCollector>
          <defaultValue>New Title</defaultValue>
          <isSearchable>true</isSearchable>
          <names>
            <value languageCode="eng-US">Title</value>
          </names>
          <descriptions>
            <value languageCode="eng-US">This is the title</value>
          </descriptions>
        </FieldDefinition>
        <FieldDefinition href="/content/types/32/draft/fielddefinitions/36" media-type="vnd.ez.api.FieldDefinition+xml">
          <id>36</id>
          <identifier>summary</identifier>
          <fieldType>ezxmltext</fieldType>
          <fieldGroup>content</fieldGroup>
          <position>2</position>
          <isTranslatable>true</isTranslatable>
          <isRequired>false</isRequired>
          <isInfoCollector>false</isInfoCollector>
          <defaultValue></defaultValue>
          <isSearchable>true</isSearchable>
          <names>
            <value languageCode="eng-US">Summary</value>
          </names>
          <descriptions>
            <value languageCode="eng-US">This is the summary</value>
          </descriptions>
        </FieldDefinition>
      </FieldDefinitions>
    </ContentType>



Copy Content Type
`````````````````
:Resource: /content/types/<ID>
:Method:      COPY or POST with header: X-HTTP-Method-Override COPY
:Description: copies a content type. The identifier of the copy is changed to copy_of_<identifier> anda new remoteIdis generated. 
:Response:

::

     HTTP/1.1 201 Created
     Location: /content/types/<newId>


:Error Codes:
    :401: If the user is not authorized to copy this content type  
        
Get Content Types
`````````````````
:Resource: /content/types
:Method: GET
:Description: Returns a list of content types 
:Parameters:
    :identifier: retrieves the content type for the given identifer
    :remoteId: retieves the content type for the given remoteId 
    :limit:    only <limit> items will be returned started by offset
    :offset:   offset of the result set
    :orderby:   one of (name | lastmodified)
    :sort:      one of (asc|desc)
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeInfoList+xml:  if set the list of content type info objects is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeInfoList+json:  if set the list of content type info objects is returned in json format (see ContentType_)
         :application/vnd.ez.api.ContentTypeList+xml:  if set the list of content type objects (including field definitions) is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeList+json:  if set the list content type objects (including field definitions) is returned in json format (see ContentType_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_      

:Error Codes:
    :401: If the user has no permission to read the content types

Get Content Type 
````````````````
:Resource: /content/types/<ID>
:Method: GET
:Description: Returns the content type given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentType+xml:  if set the list is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentType+json:  if set the list is returned in json format (see ContentType_)

:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_
      
:ErrorCodes:
    :401: If the user is not authorized to read this content type  
    :404: If the content type does not exist


Create Draft
````````````
:Resource: /content/types/<ID>
:Method: POST 
:Description: Cretes a draft and updates it with the given data
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeInfo+xml:  if set the new content type draft is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeInfo+json:  if set the new content type draft is returned in json format (see ContentType_)
    :Content-Type:
         :application/vnd.ez.api.ContentTypeUpdate+json: the ContentTypeUpdate_  schema encoded in json
         :application/vnd.ez.api.ContentTypeUpdate+xml: the ContentTypeUpdate_  schema encoded in xml
:Response:

    .. parsed-literal::

          HTTP/1.1 201 Created
          Location: /content/types/<ID>/draft
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create the draft
    :403: - If a content type with the given new identifier already exists.
          - If there exists already a draft. 



Update Draft
````````````
:Resource: /content/types/<ID>/draft
:Method: PATCH or POST with header: X-HTTP-Method-Override: PATCH
:Description: Updates meta data of a draft. This method does not handle field definitions
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeInfo+xml:  if set the new content type draft is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeInfo+json:  if set the new content type draft is returned in json format (see ContentType_)
    :Content-Type:
         :application/vnd.ez.api.ContentTypeUpdate+json: the ContentTypeUpdate_  schema encoded in json
         :application/vnd.ez.api.ContentTypeUpdate+xml: the ContentTypeUpdate_  schema encoded in xml
:Response:

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the draft.
    :403: If a content type with the given new identifier already exists.  
    :404: If there is no draft on this content type

XML Example
'''''''''''

::

    PATCH /content/types/32 HTTO/1.1
    Accept: application/vnd.ez.api.ContentTypeInfo+xml 
    Content-Type: application/vnd.ez.api.ContentTypeUpdate+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeUpdate>
      <names>
        <value languageCode="ger-DE">Neuer Content Typ</value>
      </names>
      <descriptions>
        <value languageCode="ger-DE">Das ist ein neuer Content Typ</value>
      </descriptions>
    </ContentTypeUpdate>

    HTTP/1.1 200 OK
    ETag: "56435634576543"
    Content-Type: application/vnd.ez.api.ContentTypeInfo+xml
    Content-Length: xxx

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentType href="/content/types/32/draft" media-type="vnd.ez.api.ContentType+xml">
      <id>32</id>
      <status>DRAFT</status>
      <identifier>newContentType</identifier>
      <names>
        <value languageCode="eng-US">New Content Type</value>
        <value languageCode="ger-DE">Neuer Content Typ</value>
      </names>
      <descriptions>
        <value languageCode="eng-US">This is a description</value>
        <value languageCode="ger-DE">Das ist ein neuer Content Typ</value>
      </descriptions>
      <creationDate>2001-01-01T16:37:00</creationDate>
      <modificationDate>2001-01-01T16:37:00</modificationDate>
      <Creator href="/user/users/13" media-type="vnd.ez.api.User+xml"/>
      <Modifier href="/user/users/13" media-type="vnd.ez.api.User+xml"/>
      <remoteId>remoteId-qwert548</remoteId>
      <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
      <nameSchema>&lt;title&gt;</nameSchema>
      <isContainer>true</isContainer>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
      <defaultSortField>PATH</defaultSortField>
      <defaultSortOrder>ASC</defaultSortOrder>
    </ContentType> 

Add Field definition
````````````````````
:Resource: /content/types/<ID>/draft/fielddefinitions
:Method: POST
:Description: Creates a new field definition for the given content type
:Headers:
    :Accept:
         :application/vnd.ez.api.FieldDefinition+xml:  if set the new fielddefinition is returned in xml format (see FieldDefinition_)
         :application/vnd.ez.api.FieldDefinition+json:  if set the new fielddefinition is returned in json format (see FieldDefinition_)
    :Content-Type:
         :application/vnd.ez.api.FieldDefinitionCreate+json: the FieldDefinitionCreate_  schema encoded in json
         :application/vnd.ez.api.FieldDefinitionCreate+xml: the FieldDefinitionCreate_  schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 201 Created
          Location: /content/types/<ID>/draft/fielddefinitions/<newId>
          Accept-Patch:  application/vnd.ez.api.FieldDefinitionUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          FieldDefinition_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to add a field definition  
    :403: If a field definition with same identifier already exists in the given content type 

Get Fielddefinition
```````````````````
:Resource: /content/types/<ID>/draft/fielddefinitions/<ID>
:Method: GET
:Description: Returns the field definition given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.FieldDefinition+xml:  if set the new fielddefinition is returned in xml format (see FieldDefinition_)
         :application/vnd.ez.api.FieldDefinition+json:  if set the new fielddefinition is returned in json format (see FieldDefinition_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.FieldDefinitionUpdate+(json|xml)
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          FieldDefinition_      

:ErrorCodes: 
    :401: If the user is not authorized to read the content type draft
    :404: If the content type or draft does not exist

Update Fielddefinition
``````````````````````
:Resource: /content/types/<ID>/draft/fielddefinitions/<ID>
:Method: PUT
:Description: Updates the attributes of a field definitions
:Headers:
    :Accept:
         :application/vnd.ez.api.FieldDefinition+xml:  if set the new fielddefinition is returned in xml format (see FieldDefinition_)
         :application/vnd.ez.api.FieldDefinition+json:  if set the new fielddefinition is returned in json format (see FieldDefinition_)
    :Content-Type:
         :application/vnd.ez.api.FieldDefinitionUpdate+json: the FieldDefinitionUpdate_  schema encoded in json
         :application/vnd.ez.api.FieldDefinitionUpdate+xml: the FieldDefinitionUpdate_  schema encoded in xml
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.FieldDefinitionUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          FieldDefinition_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the field definition
    :403: If a field definition with the given new identifier already exists in the given content type. 

Delete Fielddefinition
``````````````````````
:Resource: /content/types/<ID>/draft/fielddefinitions/<ID>
:Method: DELETE
:Description: the given field definition is deleted
:Response: 

::

    HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: - if there is no draft of the content type assigned to the authenticated user

Publish content type
````````````````````
:Resource: /content/types/<ID>/draft
:Method: PUBLISH or POST with header: X-HTTP-Method-Override: PUBLISH
:Description: Publishes a content type draft
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<newEag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentType_

:Error Codes:
    :401: If the user is not authorized to publish this content type draft
    :403: If the content type draft is not complete e.g. there is no field definition provided
    :404: If there is no draft or content type with the given ID

Delete Content Type
```````````````````
:Resource: /content/types/<ID>
:Method: DELETE
:Description: the given content type is deleted
:Response: 

    ::
  
        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If there are object instances of this content type - the response should contain an ErrorMessage_
    :404: If the content type does not exist


Get Groups of Content Type
``````````````````````````
:Resource: /content/type/<ID>/groups/<ID>
:Method: GET
:Description: Returns the content type groups the content type belongs to.
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroupRefList+xml:  if set the list is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeGroupRefList+json:  if set the list is returned in json format (see ContentType_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_
      
:ErrorCodes:
    :401: If the user is not authorized to read this content type  
    :404: If the content type does not exist

Link Group to Content Type
``````````````````````````
:Resource: /content/types/<ID>/groups
:Method: POST
:Description: links a content type group to the content type and returns the updated group list
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroupRefList+xml:  if set the list is returned in xml format (see ContentTypeGroup_)
         :application/vnd.ez.api.ContentTypeGroupRefList+json:  if set the list is returned in json format (see ContentTypeGroup_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to add a group
    :403: If the content type is already assigned to the group



Unlink Group from Content Type
``````````````````````````````
:Resource: /content/type/<ID>/groups/<ID>
:Method: DELETE
:Description: removes the given group from the content type and returns the updated group list
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroupRefList+xml:  if set the list is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeGroupRefList+json:  if set the list is returned in json format (see ContentType_)
:Response: 

    .. parsed-literal::

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ContentTypeGroup_

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the given group is the last one
    :404: If the resource does not exist
        

User Management
===============

Overview
--------

============================================= ===================== ===================== ===================== =======================
Resource                                      POST                  GET                   PUT                   DELETE
--------------------------------------------- --------------------- --------------------- --------------------- -----------------------
/user/groups                                  create user group     load all topl. groups .                     .            
/user/groups/<ID>                             .                     load user group       update user group     delete user group
/user/groups/<ID>/users                       .                     load users of group   .                     delete all users in this group
/user/groups/<ID>/children                    create sub group      load sub groups       .                     remove all sub groups
/user/groups/<ID>/roles                       assign role to group  load roles of group   .                     .            
/user/groups/<ID>/roles/<ID>                  .                     .                     .                     unassign role from group
/user/users                                   create user           list users            .                     .            
/user/users/<ID>                              .                     load user             update user           delete user
/user/users/<ID>/groups                       .                     load groups of user   add to group          .            
/user/users/<ID>/drafts                       .                     list all drafts owned .                     .                
                                                                    by the user                                                     
/user/roles                                   create new role       load all roles        .                     .            
/user/roles/<ID>                              .                     load role             update role           delete role
/user/roles/<ID>/policies                     create policy         load policies         .                     delete all policies from role
/user/roles/<ID>/policies/<ID>                .                     load policy           update policy         delete policy
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
:Resource: /user/roles/<ID>/policies/<module>/<function>
:Method: DELETE
:Description: the given policy is deleted
:Parameters: 
:Response: 204
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :404: If the role or policy does not exist


Input Output Specification
==========================

Common Definitions
------------------

Common definition which are used from multiple schema definitions

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:complexType name="ref">
        <xsd:annotation>
          <xsd:documentation>
            A base schema for referencing resources.
          </xsd:documentation>
        </xsd:annotation>
        <xsd:attribute name="href" type="xsd:string" />
        <xsd:attribute name="media-type" type="xsd:string" />
      </xsd:complexType>

      <xsd:complexType name="fieldInputValueType">
        <xsd:annotation>
          <xsd:documentation>
            Schema for field inputs in content create and
            update structures
          </xsd:documentation>
        </xsd:annotation>
        <xsd:all>
          <xsd:element name="fieldDefinitionIdentifer" type="xsd:string" />
          <xsd:element name="languageCode" type="xsd:string" />
          <xsd:element name="value" type="xsd:anyType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="multiLanguageValuesType">
        <xsd:sequence>
          <xsd:element name="value" minOccurs="1" maxOccurs="unbounded">
            <xsd:complexType>
              <xsd:simpleContent>
                <xsd:extension base="xsd:string">
                  <xsd:attribute name="languageCode" type="xsd:string" />
                </xsd:extension>
              </xsd:simpleContent>
            </xsd:complexType>
          </xsd:element>
        </xsd:sequence>
      </xsd:complexType>

      <xsd:simpleType name="sortFieldType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="PATH" />
          <xsd:enumeration value="PUBLISHED" />
          <xsd:enumeration value="MODIFIED" />
          <xsd:enumeration value="SECTION" />
          <xsd:enumeration value="DEPTH" />
          <xsd:enumeration value="CLASS_IDENTIFIER" />
          <xsd:enumeration value="CLASS_NAME" />
          <xsd:enumeration value="PRIORITY" />
          <xsd:enumeration value="NAME" />
          <xsd:enumeration value="MODIFIED_SUBNODE" />
          <xsd:enumeration value="NODE_ID" />
          <xsd:enumeration value="CONTENTOBJECT_ID" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="versionStatus">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="DRAFT" />
          <xsd:enumeration value="PUBLISHED" />
          <xsd:enumeration value="ARCHIVED" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="contentTypeStatus">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="DRAFT" />
          <xsd:enumeration value="DEFINED" />
          <xsd:enumeration value="MODIFIED" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="sortOrderType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="ASC" />
          <xsd:enumeration value="DESC" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="intList">
        <xsd:list itemType="xsd:integer" />
      </xsd:simpleType>
      <xsd:simpleType name="dateList">
        <xsd:list itemType="xsd:dateTime" />
      </xsd:simpleType>
      <xsd:simpleType name="stringList">
        <xsd:list itemType="xsd:string" />
      </xsd:simpleType>
    </xsd:schema>


.. _Content:

Content XML Schema
------------------

::

    <?xml version="1.0" encoding="utf-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="Version.xsd" />
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="embeddedVersionType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="Version" minOccurs="0" type="versionType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentInfo">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="ContentType" type="ref" />
              <xsd:element name="name" type="xsd:string" />
              <xsd:element name="Versions" type="ref" />
              <xsd:element name="Section" type="ref" />
              <xsd:element name="MainLocation" type="ref" minOccurs="0" />
              <xsd:element name="Locations" type="ref" minOccurs="0" />
              <xsd:element name="Owner" type="ref" />
              <xsd:element name="publishDate" type="xsd:dateTime"
                minOccurs="0" />
              <xsd:element name="lastModificationDate" type="xsd:dateTime" />
              <xsd:element name="mainLanguageCode" type="xsd:string" />
              <xsd:element name="alwaysAvailable" type="xsd:boolean" />
            </xsd:all>
            <xsd:attribute name="id" type="xsd:int" />
            <xsd:attribute name="remoteId" type="xsd:string" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.Content">
        <xsd:complexContent>
          <xsd:extension base="vnd.ez.api.ContentInfo">
            <xsd:all>
              <xsd:element name="CurrentVersion" type="embeddedVersionType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="ContentInfo" type="vnd.ez.api.ContentInfo" />
    </xsd:schema>


.. _Relation:

Relation XML Schema
-------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">

      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:simpleType name="relationType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="COMMON" />
          <xsd:enumeration value="LINK" />
          <xsd:enumeration value="EMBED" />
          <xsd:enumeration value="ATTRIBUTE" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:complexType name="relationValueType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="SourceContent" type="ref" />
              <xsd:element name="DestinationContent" type="ref" />
              <xsd:element name="RelationType" type="relationType" />
              <xsd:element name="SourceFieldDefinitionIdentifier"
                type="xsd:string" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

.. _RelationList:

::

      <xsd:complexType name="relationListType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Relation" type="relationValueType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

.. _RelationCreate:

::

      <xsd:complexType name="relationCreateType">
        <xsd:all>
          <xsd:element name="Destination" type="ref" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="Relation" type="relationValueType"></xsd:element>
      <xsd:element name="RelationList" type="relationListType"></xsd:element>
      <xsd:element name="RelationCreate" type="relationCreateType"></xsd:element>
    </xsd:schema>


.. _Version:

Version XML Schema
------------------

VersionInfo
~~~~~~~~~~~

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:simpleType name="versionStatus">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="DRAFT" />
          <xsd:enumeration value="PUBLISHED" />
          <xsd:enumeration value="ARCHIVED" />
        </xsd:restriction>
      </xsd:simpleType>
      <xsd:complexType name="versionInfoType">
        <xsd:all>
          <xsd:element name="id" type="xsd:int">
            <xsd:annotation>
              <xsd:documentation>
                The version id.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="versionNo" type="xsd:int">
            <xsd:annotation>
              <xsd:documentation>
                The version number.
                This is the version
                number, which only
                increments in scope of a single Content
                object.
                    </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="status" type="versionStatus" />
          <xsd:element name="modificationDate" type="xsd:dateTime">
            <xsd:annotation>
              <xsd:documentation>
                The date of the last modification of this
                version
                    </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="Creator" type="ref">
            <xsd:annotation>
              <xsd:documentation>
                The user which has created this version
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="creationDate" type="xsd:dateTime">
            <xsd:annotation>
              <xsd:documentation>
                The date this version was created
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="initialLanguageCode" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                In 4.x this is the language code which is
                used for labeling a
                translation.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="languageCodes" type="xsd:string"
            minOccurs="0" maxOccurs="1" default="array()">
            <xsd:annotation>
              <xsd:documentation>
                List of languages in this version
                Reflects
                which languages fields exists in for this version.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="Content" type="ref" />
        </xsd:all>
      </xsd:complexType>
    </xsd:schema>

Version
~~~~~~~

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="VersionInfo.xsd" />
      <xsd:include schemaLocation="Relation.xsd" />
      <xsd:complexType name="fieldValueType">
        <xsd:all>
          <xsd:element name="id" type="xsd:integer" />
          <xsd:element name="fieldDefinitionIdentifer" type="xsd:string" />
          <xsd:element name="languageCode" type="xsd:string" />
          <xsd:element name="value" type="xsd:anyType" />
        </xsd:all>
      </xsd:complexType>
      <xsd:complexType name="versionType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="VersionInfo" type="versionInfoType" />
              <xsd:element name="Fields" minOccurs="0">
                <xsd:complexType>
                  <xsd:sequence>
                    <xsd:element name="field" type="fieldValueType"
                      minOccurs="1" maxOccurs="unbounded" />
                  </xsd:sequence>
                </xsd:complexType>
              </xsd:element>
              <xsd:element name="Relations" minOccurs="0">
                <xsd:complexType>
                  <xsd:sequence>
                    <xsd:element name="Relation" type="relationValueType"
                      minOccurs="0" maxOccurs="unbounded" />
                  </xsd:sequence>
                </xsd:complexType>
              </xsd:element>
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Version" type="versionType"></xsd:element>
    </xsd:schema>

.. _VersionList:

VersionList XML Schema
----------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="Version.xsd" />
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="versionListType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Version" type="versionType"/>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="VersionList" type="versionListType"></xsd:element>
    </xsd:schema>



.. _VersionUpdate:

VersionUpdate XML Schema
------------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="versionUpdateType">
        <xsd:all>
          <xsd:element name="User" type="ref" minOccurs="0" />
          <xsd:element name="modificationDate" type="xsd:dateTime"
            minOccurs="0" />
          <xsd:element name="fields">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="field" type="fieldInputValueType" />
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="VersionUpdate" type="versionUpdateType"></xsd:element>
    </xsd:schema>


.. _ContentCreate:

ContentCreate XML Schema
------------------------

::

    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="contentCreateType">
        <xsd:all>
          <xsd:element name="ContentType" type="ref" />
          <xsd:element name="mainLanguageCode" type="xsd:string" />
          <xsd:element name="LocationCreate" type="locationCreateType"/>
          <xsd:element name="Section" type="ref" minOccurs="0" />
          <xsd:element name="User" type="ref" minOccurs="0" />
          <xsd:element name="alwaysAvailable" type="xsd:boolean"
            default="true" minOccurs="0" />
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="modificationDate" type="xsd:dateTime"
            minOccurs="0" />
          <xsd:element name="fields">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="field" type="fieldInputValueType" />
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ContentCreate" type="contentCreateType"></xsd:element>
    </xsd:schema>

.. _ContentUpdate:

ContentUpdate XML Schema
------------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="contentUpdateType">
        <xsd:all>
          <xsd:element name="mainLanguageCode" type="xsd:string" minOccurs="0" />
          <xsd:element name="Section" type="ref" minOccurs="0" />
          <xsd:element name="MainLocation" type="ref" minOccurs="0" />
          <xsd:element name="Owner" type="ref" minOccurs="0" />
          <xsd:element name="alwaysAvailable" type="xsd:boolean"
            default="true" minOccurs="0" />
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="modificationDate" type="xsd:dateTime"
            minOccurs="0" />
          <xsd:element name="publishDate" type="xsd:dateTime"
            minOccurs="0" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ContentUpdate" type="contentUpdateType"></xsd:element>
    </xsd:schema>


Specific Field type formats
---------------------------

Author
~~~~~~


Selection
~~~~~~~~~

Keyword
~~~~~~~


Country
~~~~~~~


RelationListInput
~~~~~~~~~~~~~~~~~


.. _View:

View XML Schema
---------------


::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="Content.xsd" />
      <xsd:simpleType name="fieldOperatorType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="IN" />
          <xsd:enumeration value="LIKE" />
          <xsd:enumeration value="EQ" />
          <xsd:enumeration value="LT" />
          <xsd:enumeration value="LTE" />
          <xsd:enumeration value="GT" />
          <xsd:enumeration value="GTE" />
          <xsd:enumeration value="BETWEEN" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="dateOperatorType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="EQ" />
          <xsd:enumeration value="LT" />
          <xsd:enumeration value="LTE" />
          <xsd:enumeration value="GT" />
          <xsd:enumeration value="GTE" />
          <xsd:enumeration value="BETWEEN" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="dateMetaDataType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="CREATED" />
          <xsd:enumeration value="MODIFIED" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="urlAliasOperatorType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="EQ" />
          <xsd:enumeration value="IN" />
          <xsd:enumeration value="LIKE" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="userMetaDataType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="CREATOR" />
          <xsd:enumeration value="MODIFIER" />
          <xsd:enumeration value="OWNER" />
          <xsd:enumeration value="GROUP" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="sortClauseEnumType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="PATH" />
          <xsd:enumeration value="PATHSTRING" />
          <xsd:enumeration value="MODIFIED" />
          <xsd:enumeration value="CREATED" />
          <xsd:enumeration value="SECTIONIDENTIFER" />
          <xsd:enumeration value="SECTIONID" />
          <xsd:enumeration value="FIELD" />
          <xsd:enumeration value="PRIORITY" />
          <xsd:enumeration value="NAME" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:complexType name="fieldCriterionType">
        <xsd:all>
          <xsd:element name="target" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                The identifier of the field i.e identifier
                of the corresponding field
                definition 
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="operator" type="fieldOperatorType" />
          <xsd:element name="value" type="xsd:anyType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="dateCriterionType">
        <xsd:all>
          <xsd:element name="target" type="dateMetaDataType">
          </xsd:element>
          <xsd:element name="operator" type="dateOperatorType" />
          <xsd:element name="value" type="dateList" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="urlaliasCriterionType">
        <xsd:all>
          <xsd:element name="operator" type="urlAliasOperatorType" />
          <xsd:element name="value" type="stringList" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="userMetaDataCriterionType">
        <xsd:all>
          <xsd:element name="target" type="userMetaDataType">
          </xsd:element>
          <xsd:element name="value" type="dateList" />
        </xsd:all>
      </xsd:complexType>


      <xsd:complexType name="criterionType">
        <xsd:choice minOccurs="1" maxOccurs="unbounded">
          <xsd:element name="AND" type="criterionType" />
          <xsd:element name="OR" type="criterionType" />
          <xsd:element name="NOT" type="criterionType" />
          <xsd:element name="ContentIdCriterion" type="intList" />
          <xsd:element name="ContentRemoteIdCriterion" type="stringList" />
          <xsd:element name="ContentTypeGroupIdCriterion" type="xsd:int" />
          <xsd:element name="ContentTypeIdCriterion" type="xsd:int" />
          <xsd:element name="ContentTypeIdentifierCriterion"
            type="xsd:string" />
          <xsd:element name="FieldCritierion" type="fieldCriterionType" />
          <xsd:element name="DateMetaDataCritierion" type="dateCriterionType" />
          <xsd:element name="FullTextCriterion" type="xsd:string" />
          <xsd:element name="LocationIdCriterion" type="intList" />
          <xsd:element name="LocationRemoteIdCriterion" type="stringList" />
          <xsd:element name="ParentLocationIdCriterion" type="intList" />
          <xsd:element name="ParentLocationRemoteIdCriterion"
            type="stringList" />
          <xsd:element name="SectionIdCriterion" type="xsd:int" />
          <xsd:element name="SectionIdentifierCriterion" type="xsd:string" />
          <xsd:element name="VersionStatusCriterion" type="versionStatus" />
          <xsd:element name="SubtreeCriterion" type="stringList" />
          <xsd:element name="URLAliasCriterion" type="urlaliasCriterionType" />
          <xsd:element name="UserMetaDataCriterion" type="userMetaDataCriterionType" />
        </xsd:choice>
      </xsd:complexType>

      <xsd:complexType name="sortClauseType">
        <xsd:sequence>
          <xsd:element name="SortClause">
            <xsd:complexType>
              <xsd:all>
                <xsd:element name="SortField" type="sortClauseEnumType" />
                <xsd:element name="TargetData" type="xsd:anyType"
                  minOccurs="0" />
              </xsd:all>
            </xsd:complexType>
          </xsd:element>
        </xsd:sequence>
      </xsd:complexType>

      <xsd:complexType name="queryType">
        <xsd:all>
          <xsd:element name="Criteria" type="criterionType" />
          <xsd:element name="limit" type="xsd:int" />
          <xsd:element name="offset" type="xsd:int" />
          <xsd:element name="SortClauses" type="sortClauseType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="resultType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="count" type="xsd:int"
                minOccurs="1" maxOccurs="1" />
              <xsd:element name="Content" type="contentValueType"
                maxOccurs="unbounded" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="viewInputType">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string" minOccurs="0" />
          <xsd:element name="Query" type="queryType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="viewType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="identifier" type="xsd:string" />
              <xsd:element name="Query" type="queryType" />
              <xsd:element name="Result" type="resultType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="ViewInput" type="viewInputType" />
      <xsd:element name="View" type="viewType" />
    </xsd:schema>


.. _LocationCreate:

LocationCreate XML Schema
-------------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="locationCreateType">
        <xsd:all>
          <xsd:element name="ParentLocation" type="ref">
            <xsd:annotation>
              <xsd:documentation>
                The parent location where the new location
                is created
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="priority" type="xsd:int" minOccurs="0" default="0">
            <xsd:annotation>
              <xsd:documentation>
                Location priority
                Position of the
                Location
                among its siblings when sorted using priority
                sort order.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="hidden" type="xsd:boolean"
            minOccurs="0" default="false">
            <xsd:annotation>
              <xsd:documentation>
                Indicates that the Location entity has
                been
                explicitly marked as hidden.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="sortField" type="sortFieldType" />
          <xsd:element name="sortOrder" type="sortOrderType" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="LocationCreate" type="locationCreateType" />
    </xsd:schema>



.. _LocationUpdate:

LocationUpdate XML Schema
-------------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="locationUpdateType">
        <xsd:all>
          <xsd:element name="priority" type="xsd:int" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                 If set the location priority is changed to the given value
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="hidden" type="xsd:boolean"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                If set to true the location is hidden, if set to false the location is unhidden.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="sortField" type="sortFieldType" />
          <xsd:element name="sortOrder" type="sortOrderType" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="LocationUpdate" type="locationUpdateType" />
    </xsd:schema>




.. _Location:

Location XML Schema
-------------------


::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="locationType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    Location ID.
                              </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="priority" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    Location priority
                    Position of the
                    Location among its siblings when sorted using priority
                    sort order.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="hidden" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    Indicates that the Location entity has
                    been explicitly marked as hidden.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="invisible" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    Indicates that the Location is
                    implicitly marked as hidden by a parent
                    location.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="ParentLocation" type="ref">
                <xsd:annotation>
                  <xsd:documentation>
                    The parent location
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="pathString" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    The materialized path of the location
                    entry, eg: /1/2/
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="subLocationModificationDate"
                type="xsd:dateTime">
                <xsd:annotation>
                  <xsd:documentation>
                    Timestamp of the latest update of a
                    content object in a sub location.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="depth" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    Depth location has in the location
                    tree.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="childCount" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    the number of chidren visible to the
                    authenticated user which has
                    loaded this instance.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="remoteId" type="xsd:string"
                minOccurs="0" />
              <xsd:element name="Content" type="ref" />
              <xsd:element name="sortField" type="sortFieldType" />
              <xsd:element name="sortOrder" type="sortOrderType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="locationListType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Location" type="ref" minOccurs="0"
                maxOccurs="unbounded"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Location" type="locationType" />
      <xsd:element name="LocationList" type="locationListType" />
    </xsd:schema>


.. _Section:
    
Section XML Schema
------------------

::

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">

      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      
        <xsd:complexType name="sectionValueType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
            <xsd:element name="sectionId" type="xsd:int"/>
            <xsd:element name="identifier" type="xsd:string"/>
            <xsd:element name="name" type="xsd:string"/>
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="sectionListType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Section" type="sectionValueType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="sectionInputType">
        <xsd:all>
            <xsd:element name="identifier" type="xsd:string" minOccurs="0"/>
            <xsd:element name="name" type="xsd:string" minOccurs="0"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="Section" type="sectionValueType"></xsd:element>
      <xsd:element name="SectionList" type="sectionListType"></xsd:element>
      <xsd:element name="SectionInput" type="sectionInputType"></xsd:element>
      
      
    </xsd:schema>


.. _ContentTypeGroup:

ContentTypeGroup XML Schema
---------------------------


.. _ContentTypeGroupInput:

ContentTypeGroupInput XML Schema
--------------------------------


.. _ContentType:
.. _ContentTypeCreate:
.. _ContentTypeUpdate:

ContentType XML Schema
----------------------
.. _include: xsd/ContentType.xsd
   :literal:

.. _ContentTypeInput:

ContentTypeInput JSON Schema
----------------------------

.. _FieldDefinition:

FieldDefinition JSON Schema
---------------------------


.. _FieldDefinitionCreate:
.. _FieldDefinitionUpdate:

FieldDefinitionInput JSON Schema
--------------------------------


.. _UserGroup:

UserGroup JSON Schema
---------------------

.. _UserGroupInput:

UserGroupInput JSON Schema
--------------------------


.. _UserInfo:


.. _User:

User JSON Schema
-------------------

.. _UserInput:

UserInput JSON Schema
---------------------


.. _Limitation:

Limitation JSON Schema
----------------------


.. _Policy:

Policy JSON Schema
------------------

.. _PolicyInput:

PolicyInput JSON Schema
-----------------------

.. _Role:

Role JSON Schema
----------------


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
