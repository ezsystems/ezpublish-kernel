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

===================================================== =================== ======================= ============================ ================
        :Resource:                                          POST                GET                  PUT                         DELETE
----------------------------------------------------- ------------------- ----------------------- ---------------------------- ----------------
/                                                     -                   list root resources     -                            -            
/content/objects                                      create new content  list/find content       -                            -            
/content/objects/<ID>                                 -                   load content            update content meta data     delete content
/content/objects/<ID>/translations                    create translation  list translations       -                            -            
/content/objects/<ID>/languages                       -                   list languages of cont. -                            -              
/content/objects/<ID>/languages/<lang_code>           -                   load content in the     -                            delete language
                                                                          given language                                       from content   
/content/objects/<ID>/versions                        create a new draft  load all versions       -                            -            
                                                      from an existing    (version infos)
                                                      version 
/content/objects/<ID>/currentversion                  -                   redirect to current v.  -                            -             
/content/objects/<ID>/versions/<no>                   -                   get a specific version  update a version/draft       delete version
/content/objects/<ID>/versions/<no>/relations         create new relation load relations of vers. -                            -              
/content/objects/<ID>/versions/<no>/relations/<ID>    -                   load relation details   -                            delete relation
/content/objects/<ID>/locations                       -                   load locations of cont- create a new location for    delete all locations
                                                                          ent                     content
/content/views                                        create view         list views              -                            -            
/content/views/<ID>                                   -                   get view                replace view                 delete view
/content/views/<ID>/results                           -                   get view results        -                            -          
/content/locations                                    -                   list/find locations     create a new location refer- -            
                                                                                                  ing to an existing content 
                                                                                                  and a parent
/content/locations/<ID>                               -                   load a location         update location              delete a location (subtree)
/content/locations/<ID>/children                      -                   load children           create a new location refer- delete all children
                                                                                                  ing to a existing content 
                                                                                                  object
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
          Etag: "<new etag>"
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
      <ParentLocation href="/content/locations/17"/>
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
    Etag: "12345678"
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
      <MainLocation href="/content/locations/65" media-type="application/vnd.ez.api.Location+xml" />
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
        "ParentLocation": {
          "_href": "/content/locations/17",
        },
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
    Etag: "12345678"
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
          "_href": "/content/locations/65",
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
      Etag: "<new etag>"
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
    Etag: "12345678"
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
      <MainLocation href="/content/locations/65" media-type="application/vnd.ez.api.Location+xml" />
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
      Etag: "<new etag>"
      Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
      Content-Type: <depending on accept header>
      Content-Length: <length>
      Content_      
      

:Error Codes:
    :400: If the Input does not match the input schema definition.
    :401: If the user is not authorized to update this object
    :404: If the content id does not exist
    :412: If the current Etag does not match with the provided one in the If-Match header
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
      <MainLocation href="/content/locations/55"/>
      <Owner href="/user/users/13"/>
      <alwaysAvailable>false</alwaysAvailable>
      <remoteId>qwert4321</remoteId>
    </ContentUpdate>
    
    HTTP/1.1 200 OK
    Etag: "12345699"
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
      <MainLocation href="/content/locations/55" media-type="application/vnd.ez.api.Location+xml" />
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
:Description: The content is deleted. On delete all locations assigned the content object are deleted via delete subtree. 
:Response: 204
:Error Codes:
    :404: content object was not found
    :401: If the user is not authorized to delete this object

Copy content
````````````
:Resource:    /content/objects/<ID>
:Method:      COPY or POST with header: X-eZ-method: COPY
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
    Destination: /content/locations/78

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
:Description: Returns a list of all versions of the content. This method does not include fields and relations int the Version elements of the response.
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
      Etag: "<new etag>"
      Accept-Patch: application/vnd.ez.api.VersionUpdate+(json|xml)
      Content-Type: <depending on accept header>
      Content-Length: <length>
      Version_      

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update this version  
    :403: If the version is not allowed to change - i.e is not a DRAFT
    :404: If the content id or version id does not exist
    :412: If the current Etag does not match with the provided one in the If-Match header
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


Create a Draft from an archived or published Version
````````````````````````````````````````````````````
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
         :application/vnd.ez.api.RelationList+xml:  if set the relation is returned in xml format (see Relation_)
         :application/vnd.ez.api.RelationList+json:  if set the relation is returned in json format (see Relation_)
:Response: 

.. parsed-literal::

    HTTP/1.1 200 OK
    Content-Type: <depending on Accept header>
    Content-Length: xxx
    Relation_ (relationListType)

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
         :application/vnd.ez.api.Relation+xml:  if set the updated version is returned in xml format (see Relation_)
         :application/vnd.ez.api.Relation+json:  if set the updated version returned in json format (see Relation_)
    :Content-Type: 
         :application/vnd.ez.api.RelationCreate+xml: the RelationCreate (see Relation_) schema encoded in xml
         :application/vnd.ez.api.RelationCreate+json: the RelationCreate (see Relation_) schema encoded in json
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

    POST /content/objects/23/versions/4/relations
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
	


END OF CURRENT WORK

Views
~~~~~
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
/user/users/<ID>/drafts                       -                     list all drafts owned -                     -                
                                                                    by the user                                                     
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
        Schema for field inputs in content create and update structures
        </xsd:documentation>
        </xsd:annotation>
        <xsd:all>
          <xsd:element name="fieldDefinitionIdentifer" type="xsd:string" />
          <xsd:element name="languageCode" type="xsd:string" />
          <xsd:element name="value" type="xsd:anyType" />
        </xsd:all>
      </xsd:complexType>
    </xsd:schema>



.. _Content:

Content XML Schema
------------------

::

    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values"
      targetNamespace="http://ez.no/API/Values">
      <xsd:include
        schemaLocation="Version.xsd" />
      <xsd:include
        schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="embeddedVersionType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="Version" minOccurs="0"
                type="versionType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Content">
        <xsd:complexType>
          <xsd:complexContent>
            <xsd:extension base="ref">
              <xsd:all>
                <xsd:element name="ContentType" type="ref" />
                <xsd:element name="name" type="xsd:string" />
                <xsd:element name="Versions" type="ref" />
                <xsd:element name="CurrentVersion" type="embeddedVersionType" />
                <xsd:element name="Section" type="ref" />
                <xsd:element name="MainLocation" type="ref" />
                <xsd:element name="Locations" type="ref" />
                <xsd:element name="Owner" type="ref" />
                <xsd:element name="publishDate" type="xsd:dateTime" />
                <xsd:element name="lastModificationDate" type="xsd:dateTime" />
                <xsd:element name="mainLanguageCode" type="xsd:string" />
                <xsd:element name="alwaysAvailable" type="xsd:boolean" />
              </xsd:all>
              <xsd:attribute name="id" type="xsd:int" />
              <xsd:attribute name="remoteId" type="xsd:string" />
            </xsd:extension>
          </xsd:complexContent>
        </xsd:complexType>
      </xsd:element>
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
      <xsd:complexType name="relationListType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Relation" type="relationValueType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
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
          <xsd:element name="ParentLocation" type="ref"/>
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
