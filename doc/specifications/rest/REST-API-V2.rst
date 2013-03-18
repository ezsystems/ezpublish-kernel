
==========================
eZ Publish REST API V2 RFC
==========================

.. sectnum::

.. contents:: Table of Contents

General considerations
======================

Media Types
-----------

The methods on resources provide multiple media types in their responses. A media type can be selected in the Accept Header.
For each xml media type there is a unique name e.g. application/vnd.ez.api.User+xml. In this case the returned xml response
conforms with the complex type definition with name vnd.ez.api.User in the user.xsd (see User_) xml schema definition file.
Each JSON schema is implicit derived from the xml schema by making a uniform transformation from XML to JSON as shown below.


Example:

.. code:: xml

    <test attr1="attr1">
       <value attr2="attr2">value</value>
       <simpleValue>45</simpleValue>
       <fields>
         <field>1</field>
         <field>2</field>
       </fields>
    </test>

transforms to:

.. code:: javascript

    {
      "test":{
        "_attr1":"attr1",
        "value":{
          "_attr2":"attr2",
          "#text":"value"
        },
        "simpleValue":"45",
        "fields": {
           "field": [ 1, 2 ]
        }
      }
    }


Different schemas which induce different media types one on resource can be used to allow to make specific
representations optimized for purposes of clients.
It is possible to make a new schema for mobile devices for retieving e.g. an article.

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.MobileContent">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="Title" type="xsd:string" />
              <xsd:element name="Summary" type="xsd:string" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="MobileContent" type="vnd.ez.api.MobileContent"/>
    </xsd:schema>


so that

.. code:: http

   GET /content/objects/23 HTTP/1.1
   Accept: application/vnd.ez.api.MobileContent+xml

returns:

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <MobileContent href="/content/objects/23" media-type="application/vnd.ez.api.MobileContent+xml">
      <Title>Title</Title>
      <Summary>This is a summary</Summary>
    </MobileContent>



However in this specification only the standard schemas and media types are defined (see InputOutput_).
If there is only one media type defined for xml or json, it is also possible to specify
application/xml or application/json.

URIs
----

The REST api is designed that the client has not to construct any uri's to resources by itself.
Starting from the root resources (ListRoot_) every response includes further links to related resources.
The uris should be used directly as identifiers on the client side and the client should not
contruct an uri by using an id.


Authentication
==============

Note: Use of HTTPS for authenticated (REST) traffic is highly recommended!

Basic Authentication
--------------------

See http://tools.ietf.org/html/rfc2617

OAuth
-----

See http://oauth.net/2/
TBD - setting up oauth.


Session based Authentication
----------------------------

This approach violates generally the principles of RESTful services. However,
the sessions are only created to re-authenticate the user (and perform authorization,
which has do be done anyway) and not to hold session state in the service.
So we consider this method to support AJAX based applications.

See "/user/sessions/" section for details on performing login / logout.

Session cookie
~~~~~~~~~~~~~~
If activated the user has to login to use this and the client has to send the session cookie in every request, using a standard Cookie header. The name (sessionName) and value (sessionID) of the header is defined  in response when doing a POST /user/sessions.

Example request header:
    Cookie: <SessionName> : <sessionID>

CSRF
~~~~
A CSRF token needs to be sent in every request using "unsafe" methods (as in: not GET or HEAD) when a session has been established. It should be sent with header X-CSRF-Token. The token (csrfToken) is defined in response when login via POST /user/sessions.

Example request headers:

.. code:: http

    DELETE /content/types/32 HTTP/1.1
    X-CSRF-Token: <csrfToken>

.. code:: http

    DELETE /user/sessions/<sessionID>
    X-CSRF-Token: <csrfToken>

If an unsafe request is missing CSRF token, or it has wrong value, a response error must be given:
    401 Unauthorized

Rich client application security concerns
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The whole point of CSRF protection is to avoid that users accidentally can do harmful operations by being tricked into executing a http(s) request against a web applications they are logged into, in case of browsers this will then be blocked by lack of CSRF token. However if you develop a rich client application (javascript, java, flash, silverlight, iOS, android, ..) that is:

* Registering itself as a protocol handler

  * In a way that exposes unsafe methods

* Authenticates using either:

  * Session based authentication
  * "Client side session" by remembering user login/password

Then you have to make sure to ask the user if he really want to perform an unsafe operation when this is asked by over such a protocol handler.

Example: A rich javascript/web application is using navigator.registerProtocolHandler() to register "web+ez:" links to go against REST api, it uses some sort of session based authentication and it is in widespread use across the net, or/and it is used by everyone within a company. A person with minimal insight into this application and the company can easily send out the following link to all employees in that company using mail: <a href="web+ez:DELETE /content/locations/1/2">latest reports</a>


SSL Client Authentication
-------------------------

The REST API provides authenticating a user by a subject in a client certificate delivered by the web server configured as SSL endpoint.


Content
=======


Overview
--------

In the content module there are the root collections objects, locations, trash and sections

===================================================== =================== ======================= ============================ ================ ==============
        :Resource:                                          POST                GET                  PATCH/PUT                   DELETE            COPY
----------------------------------------------------- ------------------- ----------------------- ---------------------------- ---------------- --------------
/                                                     .                   list root resources     .                            .
/content/objects                                      create new content  .                       .                            .
/content/objects/<ID>                                 .                   load content            update content meta data     delete content   copy content
/content/objects/<ID>/<lang_code>                     .                   .                       .                            delete language
                                                                                                                               from content
/content/objects/<ID>/versions                        .                   load all versions       .                            .
                                                                          (version infos)
/content/objects/<ID>/currentversion                  .                   redirect to current v.  .                            .                 create draft
                                                                                                                                                 from current
                                                                                                                                                 version
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
/content/views/<ID>                                   .                   get view                .                            delete view
/content/views/<ID>/results                           .                   get view results        .                            .
/content/sections                                     create section      list all sections       .                            .
/content/sections/<ID>                                .                   load section            update section               delete section
/content/trash                                        .                   list trash items        .                            empty trash
/content/trash/<ID>                                   .                   load trash item         untrash item                 delete from trsh
/content/objectstategroups                            create objectstate  list objectstategroups  .                            .
                                                      group
/content/objectstategroups/<ID>                       .                   get objectstate group   update objectstategroup      delete osg.
/content/objectstategroups/<ID>/objectstates          create object state list object states      .                            .
/content/objectstategroups/<ID>/objectstates/<ID>     .                   get object state        update objectstate           delete objectst.
/content/objects/<ID>/objectstates                    .                   get object states of    update objectstates of       .
                                                                          content                 content
/content/urlaliases                                   create url alias    list url aliases        .                            .
/content/urlaliases/<ID>                              .                   get url alias           .                            delete url wc.
/content/urlwildcards                                 create url wildcard list url wildcards      .                            .
/content/urlwildcards/<ID>                            .                   get url wildcard        .                            delete url wc.
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
:406: The request contains an Accept header which is not supported.

.. _ListRoot:

List Root Resources
~~~~~~~~~~~~~~~~~~~

:Resource: /
:Method: GET
:Description: list the root resources of the ez publish installation
:Headers:
    :Accept:
         :application/vnd.ez.api.Root+xml:  if set the list is return in xml format (see Root_)
         :application/vnd.ez.api.Root+json:  if set the list is returned in json format (see Root_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Root_

XML Example
```````````

.. code:: http

    GET / HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Root+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.Root+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Root>
      <content href="/content/objects" media-type=""/>
      <contentTypes href="/content/types" media-type="application/vnd.ez.api.ContentTypeInfoList+xml"/>
      <users href="/user/users" media-type="application/vnd.ez.api.UserRefList+xml"/>
      <roles href="/user/roles" media-type="application/vnd.ez.api.RoleList+xml"/>
      <rootLocation href="/content/locations/1" media-type="application/vnd.ez.api.Location+xml"/>
      <rootUserGroup href="/user/groups/1/3" media-type="application/vnd.ez.api.UserGroup+xml"/>
      <rootMediaFolder href="/content/locations/1/43" media-type="application/vnd.ez.api.Location+xml"/>
      <trash href="/content/trash" media-type="application/vnd.ez.api.LocationList+xml"/>
      <sections href="/content/sections" media-type="application/vnd.ez.api.SectionList+xml"/>
      <views href="/content/views" media-type="application/vnd.ez.api.RefList+xml"/>
    </Root>

JSON Example
````````````

.. code:: http

    GET / HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Root+json

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.Root+json
    Content-Length: xxx

.. code:: javascript

    {
      "Root": {
        "content": { "_href": "/content/objects" },
        "contentTypes": {
          "_href": "/content/types",
          "_media-type": "application/vnd.ez.api.ContentTypeInfoList+json"
        },
        "users": {
          "_href": "/user/users",
          "_media-type": "application/vnd.ez.api.UserRefList+json"
        },
        "roles": {
          "_href": "/user/roles",
          "_media-type": "application/vnd.ez.api.RoleList+json"
        },
        "rootLocation": {
          "_href": "/content/locations/1",
          "_media-type": "application/vnd.ez.api.Location+json"
        },
        "rootUserGroup": {
          "_href": "/user/groups/1/5",
          "_media-type": "application/vnd.ez.api.UserGroup+json"
        },
        "rootMediaFolder": {
          "_href": "/content/locations/1/43",
          "_media-type": "application/vnd.ez.api.Location+json"
        }
        "trash": {
          "_href": "/content/trash",
          "_media-type": "application/vnd.ez.api.LocationList+json"
        },
        "sections": {
          "_href": "/content/sections",
          "_media-type": "application/vnd.ez.api.SectionList+json"
        }
        "sections": {
          "_href": "/content/views",
          "_media-type": "application/vnd.ez.api.ViewList+json"
        }
      }
    }


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


.. code:: http

          HTTP/1.1 201 Created
          Location: /content/objects/<newID>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Content_

:Error codes:
       :400: If the Input does not match the input schema definition or the validation on a field fails,
       :401: If the user is not authorized to create this object in this location
       :404: If a parent location in specified in the request body (see ContentCreate_) and it does not exist

XML Example
'''''''''''

.. code:: http

    POST /content/objects HTTP/1.1
    Host: www.example.net
    Accept: application/vnd.ez.api.Content+xml
    Content-Type: application/vnd.ez.api.ContentCreate+xml
    Content-Length: xxx

.. code:: xml

    <ContentCreate xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
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
          <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>This is a title</fieldValue>
        </field>
        <field>
          <fieldDefinitionIdentifier>summary</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>This is a summary</fieldValue>
        </field>
        <field>
          <fieldDefinitionIdentifier>authors</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>
            <value>
              <value key="name">John Doe</value>
              <value key="email">john.doe@example.net</value>
            </value>
            <value>
              <value key="name">Bruce Willis</value>
              <value key="email">bruce.willis@example.net</value>
            </value>
          </fieldValue>
        </field>
      </fields>
    </ContentCreate>

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/objects/23
    ETag: "12345678"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+xml;charset=utf8
    Content-Type: application/vnd.ez.api.Content+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Content href="/content/objects/23" id="23"
      media-type="application/vnd.ez.api.Content+xml" remoteId="remoteId12345678" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
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
            <Creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
            <creationDate>2012-02-12T12:30:00</creationDate>
            <initialLanguageCode>eng-US</initialLanguageCode>
            <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
          </VersionInfo>
          <fields>
            <field>
              <id>1234</id>
              <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
              <languageCode>eng-UK</languageCode>
              <fieldValue>This is a title</fieldValue>
            </field>
            <field>
              <id>1235</id>
              <fieldDefinitionIdentifier>summary</fieldDefinitionIdentifier>
              <languageCode>eng-UK</languageCode>
              <fieldValue>This is a summary</fieldValue>
            </field>
            <field>
              <fieldDefinitionIdentifier>authors</fieldDefinitionIdentifier>
              <languageCode>eng-US</languageCode>
              <fieldValue>
                <value>
                  <value key="name">John Doe</value>
                  <value key="email">john.doe@example.net</value>
                </value>
                <value>
                  <value key="name">Bruce Willis</value>
                  <value key="email">bruce.willis@example.net</value>
                </value>
              </fieldValue>
            </field>
          </fields>
          <Relations href="/content/objects/23/versions/1/relations" media-type="application/vnd.ez.api.RelationList+xml" />
        </Version>
      </CurrentVersion>
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/4/65" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/23/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
      <lastModificationDate>2012-02-12T12:30:00</lastModificationDate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
    </Content>

JSON Example
''''''''''''

.. code:: http

    POST /content/objects HTTP/1.1
    Host: www.example.net
    Accept: application/vnd.ez.api.Content+json
    Content-Type: application/vnd.ez.api.ContentCreate+json
    Content-Length: xxx

.. code:: javascript

    {
      "ContentCreate": {
        "ContentType": {
          "_href": "/content/types/10"
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
          "_href": "/content/sections/4"
        },
        "alwaysAvailable": "true",
        "remoteId": "remoteId12345678",
        "fields": {
          "field": [
            {
              "fieldDefinitionIdentifier": "title",
              "languageCode": "eng-US",
              "fieldValue": "This is a title"
            },
            {
              "fieldDefinitionIdentifier": "summary",
              "languageCode": "eng-US",
              "fieldValue": "This is a summary"
            },
            {
              "fieldDefinitionIdentifier": "authors",
              "languageCode": "eng-US",
              "fieldValue": [
                    {
                      "name": "John Doe",
                      "email": "john.doe@example.net"
                    },
                    {
                      "name": "Bruce Willis",
                      "email": "bruce.willis@example.net"
                    }
                  ]
            }
          ]
        }
      }
    }

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/objects/23
    ETag: "12345678"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+json;charset=utf8
    Content-Type: application/vnd.ez.api.Content+json
    Content-Length: xxx

.. code:: javascript

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
                "_href": "/user/users/14",
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
                  "fieldDefinitionIdentifier": "title",
                  "languageCode": "eng-UK",
                  "fieldValue": "This is a title"
                },
                {
                  "id": "1235",
                  "fieldDefinitionIdentifier": "summary",
                  "languageCode": "eng-UK",
                  "fieldValue": "This is a summary"
                },
                {
                  "fieldDefinitionIdentifier": "authors",
                  "languageCode": "eng-US",
                  "fieldValue":
                  [
                    {
                      "name": "John Doe",
                      "email": "john.doe@example.net"
                    },
                    {
                      "name": "Bruce Willis",
                      "email": "bruce.willis@example.net"
                    }
                  ]
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
          "_href": "/user/users/14",
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
:Method: GET (not implemented)
:Description: This resource will used in future for searching content by providing a query string as alternative to posting a view to /content/views.

Load Content by remote id
`````````````````````````
:Resource: /content/objects
:Method: GET
:Description: loads the content for a given remote id
:Parameters: :remoteId: the remote id of the content. If present the content with the given remote id is returned
:Response:

.. code:: http

          HTTP/1.1 307 Temporary Redirect
          Location: /content/objects/<id>

:Error Codes:
    :404: If the content with the given remote id does not exist

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
    :If-None-Match: <etag> If the provided etag matches the current etag then a 304 Not Modified is returned. The etag changes if the meta data was changed - this happens also if there is a new published version..
:Parameters:
    :languages: (comma separated list) restricts the output of translatable fields to the given languages
:Response:


.. code:: http

          HTTP/1.1 200 OK
          ETag: "<ETag>"
          Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Content_

:Error Codes:
    :401: If the user is not authorized to read  this object. This could also happen if there is no published version yet and another user owns a draft of this content
    :404: If the ID is not found

XML Example
'''''''''''

.. code:: http

    GET /content/objects/23 HTTP/1.1
    Accept: application/vnd.ez.api.ContentInfo+xml
    If-None-Match: "12340577"

.. code:: http

    HTTP/1.1 200 OK
    ETag: "12345678"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+xml;charset=utf8
    Content-Type: application/vnd.ez.api.ContentInfo+xml
    Content-Length: xxx

.. code:: xml

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
      <Owner href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
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
    :If-Match: <etag> Causes to patch only if the specified etag is the current one. Otherwise a 412 is returned.
    :Content-Type:
         :application/vnd.ez.api.ContentUpdate+json: the ContentUpdate_ schema encoded in json
         :application/vnd.ez.api.ContentUpdate+xml: the ContentUpdate_ schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ContentUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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

.. code:: http

    POST /content/objects/23 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Host: www.example.net
    If-Match: "12345678"
    Accept: application/vnd.ez.api.ContentInfo+xml
    Content-Type: application/vnd.ez.api.ContentCreate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentUpdate>
      <mainLanguageCode>ger-DE</mainLanguageCode>
      <Section href="/content/sections/3"/>
      <MainLocation href="/content/locations/1/13/55"/>
      <Owner href="/user/users/13"/>
      <alwaysAvailable>false</alwaysAvailable>
      <remoteId>qwert4321</remoteId>
    </ContentUpdate>

.. code:: http

    HTTP/1.1 200 OK
    ETag: "12345699"
    Accept-Patch: application/vnd.ez.api.ContentUpdate+xml;charset=utf8
    Content-Type: application/vnd.ez.api.ContentInfo+xml
    Content-Length: xxx

.. code:: xml

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
      <Owner href="/user/users/13" media-type="application/vnd.ez.api.User+xml" />
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

.. code:: http

      HTTP/1.1 201 Created
      Location: /content/objects/<newId>

:Error codes:
       :401: If the user is not authorized to copy this object to the given location
       :404: If the source or destination resource do not exist.

Example
'''''''

.. code:: http

    COPY /content/objects/23 HTTP/1.1
    Host: api.example.com
    Destination: /content/locations/1/4/78

    HTTP/1.1 201 Created
    Location: /content/objects/74


Managing Versions
~~~~~~~~~~~~~~~~~

Get Current Version
```````````````````
:Resource: /content/objects/<ID>/currentversion
:Method: GET
:Description: Redirects to the current version of the content object
:Response:

.. code:: http

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

.. code:: http

        HTTP/1.1 200 OK
        Content-Type: <depending on accept header>
        Content-Length: <length>
.. parsed-literal::
        VersionList_

:Error Codes:
     :401: If the user has no permission to read the versions

XML Example
'''''''''''

.. code:: http

    GET /content/objects/23/versions HTTP/1.1
    Host: api.example.com
    Accept: application/vnd.ez.api.VersionList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.VersionList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <VersionList href="/content/objects/23/versions" media-type="application/vnd.ez.api.VersionList+xml">
      <VersionItem>
        <Version href="/content/objects/23/versions/1" media-type="application/vnd.ez.api.Version+xml"/>
        <VersionInfo>
          <id>12</id>
          <versionNo>1</versionNo>
          <status>ARCHIVED</status>
          <modificationDate>2012-02-15T12:00:00</modificationDate>
          <Creator href="/user/users/8" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>22012-02-15T12:00:00</creationDate>
          <initialLanguageCode>eng-US</initialLanguageCode>
          <names>
            <value languageCode="eng-US">Name</value>
          </names>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
      </VersionItem>
      <VersionItem>
        <Version href="/content/objects/23/versions/2" media-type="application/vnd.ez.api.Version+xml"/>
        <VersionInfo>
          <id>22</id>
          <versionNo>2</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2012-02-17T12:00:00</modificationDate>
          <Creator href="/user/users/8" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>22012-02-17T12:00:00</creationDate>
          <initialLanguageCode>eng-US</initialLanguageCode>
          <names>
            <value languageCode="eng-US">Name</value>
          </names>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
      </VersionItem>
      <VersionItem>
        <Version href="/content/objects/23/versions/3" media-type="application/vnd.ez.api.Version+xml"/>
        <VersionInfo>
          <id>44</id>
          <versionNo>3</versionNo>
          <status>DRAFT</status>
          <modificationDate>2012-02-19T12:00:00</modificationDate>
          <Creator href="/user/users/65" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>22012-02-19T12:00:00</creationDate>
          <initialLanguageCode>fra-FR</initialLanguageCode>
          <names>
            <value languageCode="eng-US">Name</value>
            <value languageCode="fra-FR">Nom</value>
          </names>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
      </VersionItem>
      <VersionItem>
        <Version href="/content/objects/23/versions/4" media-type="application/vnd.ez.api.Version+xml"/>
        <VersionInfo>
          <id>45</id>
          <versionNo>4</versionNo>
          <status>DRAFT</status>
          <modificationDate>2012-02-20T12:00:00</modificationDate>
          <Creator href="/user/users/44" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>22012-02-20T12:00:00</creationDate>
          <initialLanguageCode>ger-DE</initialLanguageCode>
          <names>
            <value languageCode="eng-US">Name</value>
            <value languageCode="ger-DE">Name</value>
          </names>
          <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
      </VersionItem>
    </VersionList>

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
    :If-None-Match: <etag> Only return the version if the given <etag> is the not current one otherwise a 304 is returned.
    :Accept:
         :application/vnd.ez.api.Version+xml:  if set the version list is returned in xml format (see VersionList_)
         :application/vnd.ez.api.Version+json:  if set the version list is returned in json format
:Response:

.. code:: http

        HTTP/1.1 200 OK
        Content-Type: <depending_on_accept_header>
        Content-Length: <length>
        ETag: <etag>
        Accept-Patch: application/vnd.ez.api.VersionUpdate+xml (ONLY if version is a draft)

.. parsed-literal::
        Version_

:Error Codes:
    :401: If the user is not authorized to read  this object
    :404: If the ID or version is not found
    :304: If the etag does not match the current one

XML Example
'''''''''''

.. code:: http

    GET /content/objects/23/versions/4 HTTP/1.1
    Host: api.example.com
    If-None-Match: "1758f762"
    Accept: application/vnd.ez.api.Version+xml

.. code:: http

    HTTP/1.1 200 OK
    Accept-Patch: application/vnd.ez.api.VersionUpdate+xml
    ETag: "a3f2e5b7"
    Content-Type: application/vnd.ez.api.Version+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Version href="/content/objects/23/versions/4" media-type="application/vnd.ez.api.Version+xml"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
      <VersionInfo>
        <id>45</id>
        <versionNo>4</versionNo>
        <status>DRAFT</status>
        <modificationDate>2012-02-20T12:00:00</modificationDate>
        <Creator href="/user/users/44" media-type="application/vnd.ez.api.User+xml" />
        <creationDate>22012-02-20T12:00:00</creationDate>
        <initialLanguageCode>ger-DE</initialLanguageCode>
        <names>
          <value languageCode="ger-DE">Name</value>
        </names>
        <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
      </VersionInfo>
      <Fields>
        <field>
          <id>1234</id>
          <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>Titel</fieldValue>
        </field>
        <field>
          <id>1235</id>
          <fieldDefinitionIdentifier>summary</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>Dies ist eine Zusammenfassungy</fieldValue>
        </field>
        <field>
          <fieldDefinitionIdentifier>authors</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>
            <value>
              <value key="name">Karl Mustermann</value>
              <value key="email">karl.mustermann@example.net</value>
            </value>
          </fieldValue>
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

.. code:: xml

        HTTP/1.1 200 OK
        ETag: "<new etag>"
        Accept-Patch: application/vnd.ez.api.VersionUpdate+(json|xml)
        Content-Type: <depending on accept header>
        Content-Length: <length>
.. parsed-literal::
        Version_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update this version
    :403: If the version is not allowed to change - i.e is not a DRAFT
    :404: If the content id or version id does not exist
    :412: If the current ETag does not match with the provided one in the If-Match header

XML Example
'''''''''''

.. code:: http

    POST /content/objects/23/versions/4 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Host: www.example.net
    If-Match: "a3f2e5b7"
    Accept: application/vnd.ez.api.Version+xml
    Content-Type: application/vnd.ez.api.VersionUpdate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <VersionUpdate xmlns:p="http://ez.no/API/Values"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://ez.no/API/Values ../VersionUpdate.xsd ">
      <modificationDate>2001-12-31T12:00:00</modificationDate>
      <initialLanguageCode>ger-DE</initialLanguageCode>
      <fields>
        <field>
          <id>1234</id>
          <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>Neuer Titel</fieldValue>
        </field>
        <field>
          <id>1235</id>
          <fieldDefinitionIdentifier>summary</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>Dies ist eine neue Zusammenfassungy</fieldValue>
        </field>
      </fields>
    </VersionUpdate>

.. code:: http

    HTTP/1.1 200 OK
    Accept-Patch: application/vnd.ez.api.VersionUpdate+xml
    ETag: "a3f2e5b9"
    Content-Type: application/vnd.ez.api.Version+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Version href="/content/objects/23/versions/4" media-type="application/vnd.ez.api.Version+xml">
      <VersionInfo>
        <id>45</id>
        <versionNo>4</versionNo>
        <status>DRAFT</status>
        <modificationDate>2012-02-20T12:00:00</modificationDate>
        <Creator href="/user/users/44" media-type="application/vnd.ez.api.User+xml" />
        <creationDate>22012-02-20T12:00:00</creationDate>
        <initialLanguageCode>ger-DE</initialLanguageCode>
        <names>
          <value languageCode="ger-DE">Neuer Titel</value>
        </names>
        <Content href="/content/objects/23" media-type="application/vnd.ez.api.ContentInfo+xml" />
      </VersionInfo>
      <Fields>
        <field>
          <id>1234</id>
          <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>Neuer Titel</fieldValue>
        </field>
        <field>
          <id>1235</id>
          <fieldDefinitionIdentifier>summary</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>Dies ist eine neuse Zusammenfassungy</fieldValue>
        </field>
        <field>
          <fieldDefinitionIdentifier>authors</fieldDefinitionIdentifier>
          <languageCode>ger-DE</languageCode>
          <fieldValue>
            <authors>
              <author name="Klaus Mustermann" email="klaus.mustermann@example.net" />
            </authors>
          </fieldValue>
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
:Headers:
    :Accept:
         :application/vnd.ez.api.Version+xml:  if set the updated version is returned in xml format (see Version_)
         :application/vnd.ez.api.Version+json:  if set the updated version returned in json format (see Version_)
:Response:

.. code:: http

        HTTP/1.1 201 Created
        Location: /content/objects/<ID>/versions/<new-versionNo>
        ETag: <etag>
        Accept-Patch: application/vnd.ez.api.VersionUpdate+xml
        Content-Type: <depending on accept header>
        Content-Length: <length>
.. parsed-literal::
        Version_

:Error Codes:
    :401: If the user is not authorized to update this object
    :404: If the content object was not found

Create a Draft from current Version
```````````````````````````````````

:Resource: /content/objects/<ID>/currentversion
:Method: COPY or POST with header X-HTTP-Method-Override: COPY
:Description: The system creates a new draft version as a copy from the current version
:Headers:
    :Accept:
         :application/vnd.ez.api.Version+xml:  if set the updated version is returned in xml format (see Version_)
         :application/vnd.ez.api.Version+json:  if set the updated version returned in json format (see Version_)
:Response:

.. code:: http

        HTTP/1.1 201 Created
        Location: /content/objects/<ID>/versions/<new-versionNo>
        ETag: <etag>
        Accept-Patch: application/vnd.ez.api.VersionUpdate+xml
        Content-Type: <depending on accept header>
        Content-Length: <length>
.. parsed-literal::
        Version_

:Error Codes:
    :401: If the user is not authorized to update this object
    :403: If the current version is already a draft
    :404: If the content object was not found

Delete Content Version
``````````````````````
:Resource: /content/objects/<ID>/version/<versionNo>
:Method: DELETE
:Description: The version is deleted
:Response:

.. code:: http

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

.. code:: http

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

.. code:: http

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

.. code:: http

        HTTP/1.1 200 OK
        Content-Type: <depending on Accept header>
        Content-Length: xxx
.. parsed-literal::
        RelationList_

:Error Codes:
    :401: If the user is not authorized to read  this object
    :404: If the content object was not found

XML Example
'''''''''''

.. code:: http

    GET /content/objects/23/versions/2/relations HTTP/1.1
    Accept: application/vnd.ez.api.RelationList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.RelationList+xml
    Content-Length: xxx

.. code:: xml

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

.. code:: http

        HTTP/1.1 200 OK
        Content-Type: <depending on Accept header>
        Content-Length: xxx
.. parsed-literal::
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

.. code:: http

        HTTP/1.1 201 Created
        Location: /content/objects/<ID>/versions/<no>/relations/<newId>
        Content-Type: <depending on Accept header>
        Content-Length: xxx
.. parsed-literal::
        Relation_ (relationValueType(

:Error Codes:
    :401: If the user is not authorized to update this content object
    :403: If a relation to the destId already exists or the destId does not exist or the version is not a draft.
    :404: If the  object or version with the given id does not exist

XML Example
'''''''''''

.. code:: http

    POST /content/objects/23/versions/4/relations HTTP/1.1
    Accept: application/vnd.ez.api.Relation+xml
    Content-Type: application/vnd.ez.api.RelationCreate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <RelationCreate>
      <Destination href="/content/objects/66"/>
    </RelationCreate>

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/objects/23/versions/4/relations
    Content-Type: application/vnd.ez.api.RelationCreate+xml
    Content-Length: xxx

.. code:: xml

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

.. code:: http

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

.. code:: xml

          HTTP/1.1 201 Created
          Location: /content/locations/<newPath>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Location_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this location
    :403: If a location under the given parent id already exists

XML Example
'''''''''''

.. code:: http

    POST /content/objects/23/locations HTTP/1.1
    Accept: application/vnd.ez.api.Location+xml
    Content-Type: application/vnd.ez.api.LocationCreate+xml
    Contnt-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationCreate>
      <ParentLocation href="/content/locations/1/5/73" />
      <priority>0</priority>
      <hidden>false</hidden>
      <sortField>PATH</sortField>
      <sortOrder>ASC</sortOrder>
    </LocationCreate>

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/locations/1/5/73/133
    ETag: "2345563422"
    Accept-Patch: application/vnd.ez.api.LocationUpdate+xml
    Content-Type: application/vnd.ez.api.Location+xml
    Contnt-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Location href="/content/locations/1/5/73/133" media-type="application/vnd.ez.api.Location+xml">
      <id>133</id>
      <priority>0</priority>
      <hidden>false</hidden>
      <invisible>false</invisible>
      <ParentLocation href="/content/locations/1/5/73" media-type="application/vnd.ez.api.Location+xml"/>
      <pathString>/1/5/73/133</pathString>
      <depth>4</depth>
      <childCount>0</childCount>
      <remoteId>remoteId-qwert567</remoteId>
      <Children href="/content/locations/1/5/73/133/children" media-type="application/vnd.ez.api.LocationList+xml"/>
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
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Location_  (locationListType)

:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object

XML Example
'''''''''''

.. code:: http

    GET /content/objects/23/locations HTTP/1.1
    Accept: application/vnd.ez.api.LocationList+xml

.. code:: http

    HTTP/1.1 200 OK
    ETag: "<etag>"
    Content-Type:  application/vnd.ez.api.LocationList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationList href="/content/objects/23/locations" media-type="application/vnd.ez.api.LocationList+xml">
      <Location href="/content/locations/1/2/56" media-type="application/vnd.ez.api.Location+xml"/>
      <Location href="/content/locations/1/4/73/133" media-type="application/vnd.ez.api.Location+xml"/>
    </LocationList>

Load locations by id
````````````````````
:Resource: /content/locations
:Method: GET
:Description: loads the location for a given id (x)or remote id
:Parameters: :id: the id of the location. If present the location is with the given id is returned.
             :remoteId: the remoteId of the location. If present the location with the given remoteId is returned
:Response:

.. code:: http

          HTTP/1.1 307 Temporary Redirect
          Location: /content/locations/<path>

:Error Codes:
    :404: If the  location with the given id (remoteId) does not exist

Load location
`````````````
:Resource: /content/locations/<path>
:Method: GET
:Description: loads the location for the given path
:Headers:
    :Accept:
         :application/vnd.ez.api.Location+xml:  if set the new location is returned in xml format (see Location_)
         :application/vnd.ez.api.Location+json:  if set the new location is returned in json format (see Location_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Location: /content/locations/<path>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Location_

:Error Codes:
    :404: If the  location with the given path does not exist
    :401: If the user is not authorized to read this location

XML Example
'''''''''''

.. code:: http

    GET /content/locations/1/4/73/133 HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Location+xml
    If-None-Match: "2345503255"

.. code:: http

    HTTP/1.1 200 OK
    ETag: "2345563422"
    Accept-Patch: application/vnd.ez.api.LocationUpdate+xml
    Content-Type: application/vnd.ez.api.Location+xml
    Contnt-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Location href="/content/locations/1/5/73/133" media-type="application/vnd.ez.api.Location+xml">
      <id>133</id>
      <priority>0</priority>
      <hidden>false</hidden>
      <invisible>false</invisible>
      <ParentLocation href="/content/locations/1/5/73" media-type="application/vnd.ez.api.Location+xml"/>
      <pathString>/1/5/73/133</pathString>
      <depth>4</depth>
      <childCount>0</childCount>
      <remoteId>remoteId-qwert567</remoteId>
      <Children href="/content/locations/1/5/73/133/children" media-type="application/vnd.ez.api.LocationList+xml"/>
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
    :If-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Location: /content/locations/<path>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.LocationUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Location_

:Error Codes:
    :404: If the  location with the given id does not exist
    :401: If the user is not authorized to update this location


XML Example
'''''''''''

.. code:: http

    POST /content/locations/1/5/73/133 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Host: www.example.net
    If-Match: "12345678"
    Accept: application/vnd.ez.api.Location+xml
    Content-Type: :application/vnd.ez.api.LocationUpdate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <LocationUpdate>
      <priority>3</priority>
      <hidden>true</hidden>
      <remoteId>remoteId-qwert999</remoteId>
      <sortField>CLASS</sortField>
      <sortOrder>DESC</sortOrder>
    </LocationUpdate>

.. code:: http

    HTTP/1.1 200 OK
    ETag: "2345563444"
    Accept-Patch: application/vnd.ez.api.LocationUpdate+xml
    Content-Type: application/vnd.ez.api.Location+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Location href="/content/locations/1/5/73/133" media-type="application/vnd.ez.api.Location+xml">
      <id>133</id>
      <priority>3</priority>
      <hidden>true</hidden>
      <invisible>true</invisible>
      <ParentLocation href="/content/locations/1/5/73" media-type="application/vnd.ez.api.Location+xml"/>
      <pathString>/1/5/73/133</pathString>
      <depth>4</depth>
      <childCount>0</childCount>
      <remoteId>remoteId-qwert999</remoteId>
      <Children href="/content/locations/1/5/73/133/children" media-type="application/vnd.ez.api.LocationList+xml"/>
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

.. code:: xml

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Location_

:Error Codes:
    :404: If the  object with the given id does not exist
    :401: If the user is not authorized to read this object

XML Example
'''''''''''

.. code:: http

    GET /content/locations/1/2/54/children HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.LocationList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type:  application/vnd.ez.api.LocationList+xml
    Content-Length: xxx

.. code:: xml

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

.. code:: http

        HTTP/1.1 201 Created
        Location: /content/locations/<newPath>

or if destination is /content/trash

.. code:: http

        HTTP/1.1 201 Created
        Location: /content/trash/<ID>

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

.. code:: http

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

.. code:: http

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

.. code:: http

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

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<new etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          View_

:Error codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_

XML Example
'''''''''''

Perform a query on articles with a specific title.

.. code:: http

    POST /content/views HTTP/1.1
    Accept: application/vnd.ez.api.View+xml
    Content-Type: application/vnd.ez.api.ViewInput+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ViewInput>
      <identifier>TitleView</identifier>
      <Query>
        <Criteria>
          <FullTextCritierion>Title</FieldCritierion>
        </Criteria>
        <limit>10</limit>
        <offset>0</offset>
        <SortClauses>
          <SortClause>
            <SortField>NAME</SortField>
          </SortClause>
        </SortClauses>
        <FacetBuilders>
          <contentTypeFacetBuilder/>
        </FacetBuilders>
      </Query>
    </ViewInput>

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/views/view1234
    Content-Type: application/vnd.ez.api.View+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <View href="/content/views/TitleView" media-type="application/vnd.ez.api.View+xml">
      <identifier>TitleView</identifier>
      <User href="/user/users/14" media-type="vnd.ez.api.User+xml"/>
      <public>false</public>
      <Query>
        <Criteria>
          <FullTextCritierion>Title</FieldCritierion>
        </Criteria>
        <limit>10</limit>
        <offset>0</offset>
        <SortClauses>
          <SortClause>
            <SortField>NAME</SortField>
          </SortClause>
        </SortClauses>
        <FacetBuilders>
          <contentTypeFacetBuilder/>
        </FacetBuilders>
      </Query>
      <Result href="/content/views/view1234/results"
        media-type="application/vnd.ez.api.ViewResult+xml" count="34" time="31" maxScore="1.0">
        <searchHits>
          <searchHit score="1.0" index="installid1234567890">
            <hightlight/>
            <value>
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
                      <creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
                      <creationDate>2001-12-31T12:00:00</creationDate>
                      <initialLanguageCode>eng-UK</initialLanguageCode>
                      <Content href="/content/objects/23"
                        media-type="application/vnd.ez.api.ContentInfo+xml" />
                    </VersionInfo>
                    <Fields>
                      <field>
                        <id>1234</id>
                        <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
                        <languageCode>eng-UK</languageCode>
                        <fieldValue>Title</fieldValue>
                      </field>
                      <field>
                        <id>1235</id>
                        <fieldDefinitionIdentifier>summary
                        </fieldDefinitionIdentifier>
                        <languageCode>eng-UK</languageCode>
                        <fieldValue>This is a summary</fieldValue>
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
                <Owner href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
                <PublishDate>2001-12-31T12:00:00</PublishDate>
                <LastModificationDate>2001-12-31T12:00:00</LastModificationDate>
                <MainLanguageCode>eng-UK</MainLanguageCode>
                <AlwaysAvailable>true</AlwaysAvailable>
              </Content>
            </value>
          </searchHit>
          ....
        </searchHits>
        <facets>
          <contentTypeFacet>
            <contentTypeFacetEntry>
              <contentType href="/content/types/1"  media-type="application/vnd.ez.api.ContentType+xml"/>
              <count>3</count>
            </contentTypeFacetEntry>
            <contentTypeFacetEntry>
              <contentType href="/content/types/7"  media-type="application/vnd.ez.api.ContentType+xml"/>
              <count>9</count>
            </contentTypeFacetEntry>
            <contentTypeFacetEntry>
              <contentType href="/content/types/11"  media-type="application/vnd.ez.api.ContentType+xml"/>
              <count>1</count>
            </contentTypeFacetEntry>
            <contentTypeFacetEntry>
              <contentType href="/content/types/15"  media-type="application/vnd.ez.api.ContentType+xml"/>
              <count>8</count>
            </contentTypeFacetEntry>
          </contentTypeFacet>
        </facets>
      </Result>
    </View>


List views
``````````
:Resource: /content/views
:Method: GET
:Description: Returns a list of view uris. The list includes public view and private view of the authenticated user.
:Headers:
    :Accept:
        :application/vnd.ez.api.RefList+xml: the view link list in xml format (see View_)
        :application/vnd.ez.api.RefList+json: the view link list in xml format (see View_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Common_

Get View
````````
:Resource: /content/views/<identifier>
:Method: GET
:Description: Returns the view
:Headers:
    :Accept:
        :application/vnd.ez.api.View+xml: the view excluding results in xml format (see View_)
        :application/vnd.ez.api.View+json: the view excluding results in json format (see View_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          View_

:Error Codes:
    :401: if the view is not public and from another user


Get Results of existing View
````````````````````````````
:Resource: /content/views/<identifier>/results
:Method: GET
:Description: Returns result of the view
:Headers:
    :Accept:
        :application/vnd.ez.api.ViewResult+xml: the view excluding results in xml format (see View_)
        :application/vnd.ez.api.ViewResult+json: the view excluding results in json format (see View_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          View_

:Error Codes:
    :401: if the view is not public and from another user

Delete View
```````````
:Resource: /content/views/<identifier>
:Method: DELETE
:Description: the given view is deleted
:Parameters:
:Response:

.. code:: http

         HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this view
    :404: If the view does not exist



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

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/section/<ID>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.SectionInput+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Section_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this section
    :403: If a section with same identifier already exists

XML Example
'''''''''''

.. code:: http

    POST /content/sections HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.Section+xml
    Content-Type: application/vnd.ez.api.SectionInput+xml
    Content-Length: xxxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <SectionInput>
      <identifier>restricted</identifier>
      <name>Restricted</name>
    </SectionInput>

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/section/5
    ETag: "4567867894564356"
    Accept-Patch: application/vnd.ez.api.SectionInput+(json|xml)
    Content-Type:  application/vnd.ez.api.Section+xml
    Content-Length: xxxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Section href="/content/sections/5" media-type="application/vnd.ez.api.Section+xml">
      <sectionId>5</sectionId>
      <identifier>restricted</identifier>
      <name>Restriced</name>
    </Section>



Get Sections
````````````
:Resource: /content/sections
:Method: GET
:Description: Returns a list of all sections
:Parameters:
    :identifer: only the section with the given identifier is returned.
:Headers:
    :Accept:
         :application/vnd.ez.api.SectionList+xml:  if set the section list is returned in xml format (see Section_)
         :application/vnd.ez.api.SectionList+json:  if set the section list is returned in json format (see Section_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Section_  (sectionListType)

:Error Codes:
    :401: If the user has no permission to read the sections

XML Example
'''''''''''

.. code:: http

    GET /content/sections
    Host: api.example.net
    If-None-Match: "43450986749098765"
    Accept: application/vnd.ez.api.SectionList+xml

.. code:: http

    HTTP/1.1 200 OK
    ETag: "43450986743098576"
    Content-Type: application/vnd.ez.api.SectionList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <SectionList href="/content/sections" media-type="application/vnd.ez.api.SectionList+xml">
      <Section href="/content/sections/1" media-type="application/vnd.ez.api.Section+xml">
        <sectionId>1</sectionId>
        <identifier>standard</identifier>
        <name>Standard</name>
      </Section>
      <Section href="/content/sections/2" media-type="application/vnd.ez.api.Section+xml">
        <sectionId>2</sectionId>
        <identifier>users</identifier>
        <name>Users</name>
      </Section>
      <Section href="/content/sections/3" media-type="application/vnd.ez.api.Section+xml">
        <sectionId>3</sectionId>
        <identifier>media</identifier>
        <name>Media</name>
      </Section>
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml">
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
         :application/vnd.ez.api.Section+xml:  if set the section is returned in xml format (see Section_)
         :application/vnd.ez.api.Section+json:  if set the section is returned in json format (see Section_)
    :If-None-match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.SectionInput+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Section_

:ErrorCodes:
    :401: If the user is not authorized to read this section
    :404: If the section does not exist

XML Example
'''''''''''

.. code:: http

    GET /content/sections/3 HTTP/1.1
    Host: api.example.net
    If-None-Match: "43450986749098765"
    Accept: application/vnd.ez.api.Section+xml

.. code:: http

    HTTP/1.1 200 OK
    ETag: "4567867894564356"
    Accept-Patch: application/vnd.ez.api.SectionInput+(json|xml)
    Content-Type:  application/vnd.ez.api.Section+xml
    Content-Length: xxxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Section href="/content/sections/3" media-type="application/vnd.ez.api.Section+xml">
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
         :application/vnd.ez.api.Section+xml:  if set the updated section is returned in xml format (see Section_)
         :application/vnd.ez.api.Section+json:  if set the updated section is returned in json format (see Section_)
    :Content-Type:
         :application/vnd.ez.api.SectionInput+json: the Section_ input schema encoded in json
         :application/vnd.ez.api.SectionInput+xml: the Section_ input schema encoded in xml
    :If-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.SectionInput+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Section_  (sectionListType)

:Error Codes:
    :400; If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this section
    :403: If a section with the given new identifier already exists
    :412: If the current ETag does not match with the provided one in the If-Match header

Delete Section
``````````````
:Resource: /content/sections/<ID>
:Method: DELETE
:Description: the given section is deleted
:Headers:
    :Accept:
         :application/vnd.ez.api.ErrorMessage+xml:  if set in the case of an error the error message is returned in xml format (see ErrorMessage_)
         :application/vnd.ez.api.ErrorMessage+json:  if set in the case of an error the error message is returned in json format (see ErrorMessage_)
:Response:

.. code:: http

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
:Parameters:
    :limit:    only <limit> items will be returned started by offset
    :offset:   offset of the result set
:Headers:
    :Accept:
         :application/vnd.ez.api.Trash+xml:  if set the new location is returned in xml format (see Trash_)
         :application/vnd.ez.api.Trash+json:  if set the new location is returned in json format (see Trash_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Trash_

:ErrorCodes:
    :401: If the user has no permission to read the trash

Get TrashItem
`````````````
:Resource: /content/trash/<ID>
:Method: GET
:Description: Returns the trash item given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.TrashItem+xml:  if set the new trash item is returned in xml format (see Trash_)
         :application/vnd.ez.api.TrashItem+json:  if set the new trash item is returned in json format (see Trash_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Trash_

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

.. code:: http

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

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to empty all trash items

Delete TrashItem
````````````````
:Resource: /content/trash/items/<ID>
:Method: DELETE
:Description: Deletes the given trash item
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to empty the given trash item
    :404: if the given trash item does not exist

Object States
~~~~~~~~~~~~~

Create ObjectStateGroup
```````````````````````
:Resource: /content/objectstategroups
:Method: POST
:Description: Creates a new objectstategroup
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectStateGroup+xml:  if set the new object state group is returned in xml format (see ObjectStateGroup_)
         :application/vnd.ez.api.ObjectStateGroup+json:  if set the new object state group is returned in json format (see ObjectStateGroup_)
    :Content-Type:
         :application/vnd.ez.api.ObjectStateGroupCreate+json: the ObjectStateGroup_ input schema encoded in json
         :application/vnd.ez.api.ObjectStateGroupCreate+xml: the ObjectStateGroup_ input schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/objectstategroup/<ID>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ObjectStateGroupInput+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create an object state group
    :403: If a object state group with same identifier already exists

List ObjectStateGroups
``````````````````````
:Resource: /content/objectstategroups
:Method: GET
:Description: Returns a list of all object state groups
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectStateGroupList+xml:  if set the object state group list is returned in xml format (see ObjectStateGroup_)
         :application/vnd.ez.api.ObjectStateGroupList+json:  if set the object state group list is returned in json format (see ObjectStateGroup_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:Error Codes:
    :401: If the user has no permission to read object state groups



Get ObjectStateGroup
````````````````````
:Resource: /content/objectstategroups/<ID>
:Method: GET
:Description: Returns the object state group given by id
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectStateGroup+xml:  if set the object state group is returned in xml format (see ObjectStateGroup_)
         :application/vnd.ez.api.ObjectStateGroup+json:  if set the object state group is returned in json format (see ObjectStateGroup_)
    :If-None-match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.ObjectStateGroupUpdate+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:ErrorCodes:
    :401: If the user is not authorized to read object state groups
    :404: If the sobject state group does not exist

Update ObjectStateGroup
```````````````````````
:Resource: /content/objectstategroups/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates an object state group
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectStateGroup+xml:  if set the updated object state group  is returned in xml format (see ObjectStateGroup_)
         :application/vnd.ez.api.ObjectStateGroup+json:  if set the updated object state group is returned in json format (see ObjectStateGroup_)
    :Content-Type:
         :application/vnd.ez.api.ObjectStateGroupUpdate+json: the ObjectStateGroup_ input schema encoded in json
         :application/vnd.ez.api.ObjectStateGroupUpdate+xml: the ObjectStateGroup_ input schema encoded in xml
    :If-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.ObjectStateGroupUpdate+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:Error Codes:
    :400; If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update an object state group
    :403: If an object state group with the given new identifier already exists
    :412: If the current ETag does not match with the provided one in the If-Match header

Delete ObjectStateGroup
```````````````````````
:Resource: /content/objectstategroups/<ID>
:Method: DELETE
:Description: the given object state group including the object states is deleted
:Parameters:
:Response:

.. code:: http

         HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete an object state group
    :404: If the object statee group does not exist

Create ObjectState
``````````````````
:Resource: /content/objectstategroups/<ID>/objectstates
:Method: POST
:Description: Creates a new objectstate
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectState+xml:  if set the new object state is returned in xml format (see ObjectState_)
         :application/vnd.ez.api.ObjectState+json:  if set the new object state is returned in json format (see ObjectState_)
    :Content-Type:
         :application/vnd.ez.api.ObjectStateGroupCreate+json: the ObjectState_ input schema encoded in json
         :application/vnd.ez.api.ObjectStateGroupCreate+xml: the ObjectState_ input schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/objectstategroup/<ID>/objectstate/<ID>
          ETag: "<new etag>"
          Accept-Patch: application/vnd.ez.api.ObjectStateUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create an object state
    :403: If a object state with same identifier already exists in the given group

List Objectstates
`````````````````
:Resource: /content/objectstategroups/<ID>/objectstates
:Method: GET
:Description: Returns a list of all object states of the given group
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectStateList+xml:  if set the object state list is returned in xml format (see ObjectState_)
         :application/vnd.ez.api.ObjectStateList+json:  if set the object state list is returned in json format (see ObjectState_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:Error Codes:
    :401: If the user has no permission to read object states



Get ObjectState
```````````````
:Resource: /content/objectstategroups/<ID>/objectstates/<ID>
:Method: GET
:Description: Returns the object state
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectState+xml:  if set the object state is returned in xml format (see ObjectState_)
         :application/vnd.ez.api.ObjectState+json:  if set the object state is returned in json format (see ObjectState_)
    :If-None-match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.ObjectStateUpdate+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:ErrorCodes:
    :401: If the user is not authorized to read object state groups
    :404: If the sobject state group does not exist

Update ObjectState
``````````````````
:Resource: /content/objectstategroups/<ID>/objectstates/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates an object state
:Headers:
    :Accept:
         :application/vnd.ez.api.ObjectState+xml:  if set the updated object state  is returned in xml format (see ObjectState_)
         :application/vnd.ez.api.ObjectState+json:  if set the updated object state is returned in json format (see ObjectState_)
    :Content-Type:
         :application/vnd.ez.api.ObjectStateUpdate+json: the ObjectState_ input schema encoded in json
         :application/vnd.ez.api.ObjectStateUpdate+xml: the ObjectState_ input schema encoded in xml
    :If-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.ObjectStateUpdate+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ObjectStateGroup_

:Error Codes:
    :400; If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update an object state
    :403: If an object state with the given new identifier already exists in this group
    :412: If the current ETag does not match with the provided one in the If-Match header

Delete ObjectState
``````````````````
:Resource: /content/objectstategroups/<ID>/objectstates/<ID>
:Method: DELETE
:Description: the given object state is deleted
:Parameters:
:Response:

.. code:: http

         HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete an object state group
    :404: If the object state does not exist


Get ObjectStates of Content
```````````````````````````
:Resource: /content/objects/<ID>/objectstates
:Method: GET
:Description: Returns the object states of content
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentObjectStates+xml:  if set the object state is returned in xml format (see ContentObjectStates_)
         :application/vnd.ez.api.ContentObjectStates+json:  if set the object state is returned in json format (see ContentObjectStates_)
    :If-None-match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.ContentObjectStates+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentObjectStates_

:ErrorCodes:
    :404: If the content object does not exist

Set ObjectStates of Content
```````````````````````````
:Resource: /content/objects/<ID>/objectstates
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates object states of content. An object state in the input overrides the state of the object state group.
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentObjectStates+xml:  if set the updated object state  is returned in xml format (see ContentObjectStates_)
         :application/vnd.ez.api.ContentObjectStates+json:  if set the updated object state is returned in json format (see ContentObjectStates_)
    :Content-Type:
         :application/vnd.ez.api.ContentObjectStates+json: the ContentObjectStates_ input schema encoded in json
         :application/vnd.ez.api.ContentObjectStates+xml: the ContentObjectStates_ input schema encoded in xml
    :If-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Accept-Patch: application/vnd.ez.api.ContentObjectStates+(xml|json)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentObjectStates_

:Error Codes:
    :400; If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to set an object state
    :403: If the input contains multiple object states of the same object state group
    :412: If the current ETag does not match with the provided one in the If-Match header

Url Alias
~~~~~~~~~

Create Url Alias
````````````````
:Resource: /content/urlaliases
:Method: POST
:Description: Creates a new url alias
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlAlias+xml:  if set the new object state group is returned in xml format (see UrlAlias_)
         :application/vnd.ez.api.UrlAlias+json:  if set the new object state group is returned in json format (see UrlAlias_)
    :Content-Type:
         :application/vnd.ez.api.UrlAliasCreate+json: the UrlAlias_ input schema encoded in json
         :application/vnd.ez.api.UrlAliasCreate+xml: the UrlAlias_ input schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/urlaliases/<ID>
          ETag: "<new etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlAlias_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create an url alias
    :403: If an url alias same identifier already exists

List UrlAliases for location
````````````````````````````
:Resource: /content/locations/<path>/urlaliases
:Method: GET
:Description: Returns the list of url aliases for a location
:Parameters:
    :custom: (default true) this flag indicates wether autogenerated (false) or manual url aliases (true) should be returned.
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlAliasRefList+xml:  if set the url alias list contains only references and is returned in xml format (see UrlAlias_)
         :application/vnd.ez.api.UrlAliasRefList+json:  if set the url alias list contains only references is and returned in json format (see UrlAlias_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlAlias_

:Error Codes:
    :401: If the user has no permission to read urlaliases
    :401: If the location was not found


List Global UrlAliases
``````````````````````
:Resource: /content/urlaliases
:Method: GET
:Description: Returns the list of url global aliases
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlAliasRefList+xml:  if set the url alias list contains only references and is returned in xml format (see UrlAlias_)
         :application/vnd.ez.api.UrlAliasRefList+json:  if set the url alias list contains only references is and returned in json format (see UrlAlias_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlAlias_

:Error Codes:
    :401: If the user has no permission to read urlaliases

Get UrlAlias
````````````
:Resource: /content/urlaliases/<ID>
:Method: GET
:Description: Returns the urlalias with the given id
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlAlias+xml:  if set the url alias is returned in xml format (see UrlAlias_)
         :application/vnd.ez.api.UrlAlias+json:  if set the url alias is returned in json format (see UrlAlias_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlAlias_

:ErrorCodes:
    :401: If the user is not authorized to read url aliases
    :404: If the url alias does not exist

Delete UrlAlias
```````````````
:Resource: /content/urlaliases/<ID>
:Method: DELETE
:Description: the given url alias is deleted
:Parameters:
:Response:

.. code:: http

         HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete an url alias
    :404: If the url alias does not exist


Url Wildcards
~~~~~~~~~~~~~

Create Url Wildcard
```````````````````
:Resource: /content/urlwildcards
:Method: POST
:Description: Creates a new url wildcard
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlWildcard+xml:  if set the new object state group is returned in xml format (see UrlWildcard_)
         :application/vnd.ez.api.UrlWildcard+json:  if set the new object state group is returned in json format (see UrlWildcard_)
    :Content-Type:
         :application/vnd.ez.api.UrlWildcardCreate+json: the UrlWildcard_ input schema encoded in json
         :application/vnd.ez.api.UrlWildcardCreate+xml: the UrlWildcard_ input schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/urlwildcards/<ID>
          ETag: "<new etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlWildcard_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create an url wildcard
    :403: If an url wildcardsame identifier already exists

List UrlWildcards
`````````````````
:Resource: /content/urlwildcards
:Method: GET
:Description: Returns a list of url wildcards
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlWildcardList+xml:  if set the url wildcard list is returned in xml format (see UrlWildcard_)
         :application/vnd.ez.api.UrlWildcardList+json:  if set the url wildcard list is returned in json format (see UrlWildcard_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlWildcard_

:Error Codes:
    :401: If the user has no permission to read urlwildcards

Get UrlWildcard
```````````````
:Resource: /content/urlwildcards/<ID>
:Method: GET
:Description: Returns the urlwildcard with the given id
:Headers:
    :Accept:
         :application/vnd.ez.api.UrlWildcard+xml:  if set the url wildcard is returned in xml format (see UrlWildcard_)
         :application/vnd.ez.api.UrlWildcard+json:  if set the url wildcard is returned in json format (see UrlWildcard_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UrlWildcard_

:ErrorCodes:
    :401: If the user is not authorized to read url wildcards
    :404: If the url wildcard does not exist

Delete UrlWildcard
``````````````````
:Resource: /content/urlwildcards/<ID>
:Method: DELETE
:Description: the given url wildcard is deleted
:Parameters:
:Response:

.. code:: http

         HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete an url wildcard
    :404: If the url wildcard does not exist


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
/content/types                                     .                   list content types  .                       .
/content/types/<ID>                                copy content type   load content type   create draft            delete content type
/content/types/<ID>/groups                         link group          list groups         .                       .
/content/types/<ID>/groups/<ID>                    .                   .                   .                       unlink group
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

.. code:: http

          HTTP/1.1 201 Created
          Loction: /content/typegroups/<newId>
          Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this content type group
    :403: If a content type group with same identifier already exists


XML Example
'''''''''''

.. code:: http

    POST /content/typegroups HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Type: application/vnd.ez.api.ContentTypeGroupInput+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroupInput>
      <identifier>newContentTypeGroup</identifier>
    </ContentTypeGroupInput>

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/typegroups/7
    Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+xml
    ETag: "9587649865938675"
    Content-Type: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroup href="/content/typesgroups/7" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
      <id>7</id>
      <identifier>newContentTypeGroup</identifier>
      <created>2012-02-31T12:45:00</created>
      <modified>2012-02-31T12:45:00</modified>
      <Creator href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
      <Modifier href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
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

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_  (contentTypeGroupListType)

:Error Codes:
    :401: If the user has no permission to read the content types

XML Example
'''''''''''

.. code:: http

    GET /content/typegroups HTTP/1.1
    Host: api.example.net
    Accept: application/vnd.ez.api.ContentTypeGroupList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.ContentTypeGroupList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroupList href="/content/typegroups" media-type="application/vnd.ez.api.ContentTypeGroupList+xml">
      <ContentTypeGroup href="/content/typegroups/1" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
        <id>1</id>
        <identifier>Content</identifier>
        <created>2010-06-31T12:00:00</created>
        <modified>2010-07-31T12:00:00</modified>
        <Creator href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
        <Modifier href="/user/users/6" media-type="application/vnd.ez.api.User+xml"/>
        <ContentTypes href="/content/typegroups/1/types" media-type="application/vnd.ez.api.ContentTypeList+xml"/>
      </ContentTypeGroup>
      <ContentTypeGroup href="/content/typegroups/2" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
        <id>2</id>
        <identifier>Media</identifier>
        <created>2010-06-31T14:00:00</created>
        <modified>2010-09-31T12:00:00</modified>
        <Creator href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
        <Modifier href="/user/users/9" media-type="application/vnd.ez.api.User+xml"/>
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
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+(json|xml)
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_

:ErrorCodes:
    :401: If the user is not authorized to read this content type
    :404: If the content type group does not exist


Get Content Type Group by id
````````````````````````````
:Resource: /content/typegroups
:Method: GET
:Description: loads the content type group for a given identifier
:Parameters: :identifier: the identifier of the content type group. If present the content type group is with the given identifier is returned.
:Response:

.. code:: http

          HTTP/1.1 307 Temporary Redirect
          Location: /content/typegroups/<ID>

:Error Codes:
        :404: If the content type group with the given identifier does not exist


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
    :If-Match: <etag> Causes to patch only if the specified etag is the current one. Otherwise a 412 is returned.
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this content type group
    :403: If a content type group with the given identifier already exists
    :412: If the current ETag does not match with the provided one in the If-Match header


XML Example
'''''''''''

.. code:: http

    POST /content/typegroups/7 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Host: api.example.net
    If-Match: "958764986593830900"
    Accept: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Type: application/vnd.ez.api.ContentTypeGroupInput+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroupInput>
      <identifier>updatedIdentifer</identifier>
    </ContentTypeGroupInput>

.. code:: http

    HTTP/1.1 200 OK
    Location: /content/typegroups/7
    Accept-Patch:  application/vnd.ez.api.ContentTypeGroupInput+xml
    ETag: "95876498659383245"
    Content-Type: application/vnd.ez.api.ContentTypeGroup+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeGroup href="/content/typesgroups/7" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
      <id>7</id>
      <identifier>updatedIdentifer</identifier>
      <created>2012-02-31T12:45:00</created>
      <modified>2012-04-13T12:45:00</modified>
      <Creator href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
      <Modifier href="/user/users/8" media-type="application/vnd.ez.api.User+xml"/>
      <ContentTypes href="/content/typegroups/7/types" media-type="application/vnd.ez.api.ContentTypeList+xml"/>
    </ContentTypeGroup>


Delete Content Type Group
`````````````````````````
:Resource: /content/typegroups/<ID>
:Method: DELETE
:Description: the given content type group is deleted
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the content type group is not empty
    :404: If the content type does not exist

List Content Types for Group
````````````````````````````
:Resource: /content/typegroups/<ID>/types
:Method: GET
:Description: Returns a list of content types of the group
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeInfoList+xml:  if set the list of content type info objects is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeInfoList+json:  if set the list of content type info objects is returned in json format (see ContentType_)
         :application/vnd.ez.api.ContentTypeList+xml:  if set the list of content type objects (including field definitions) is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeList+json:  if set the list content type objects (including field definitions) is returned in json format (see ContentType_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentType_

:Error Codes:
    :401: If the user has no permission to read the content types


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

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/types/<newId>/draft
          Accept-Patch:  application/vnd.ez.api.ContentTypeUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentType_

If publish = true:

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/types/<newId>
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentType_

:Error Codes:
    :400: - If the Input does not match the input schema definition,
          - If publish = true and the input is not complete e.g. no field definitions are provided
    :401: If the user is not authorized to create this content type
    :403: If a content type with same identifier already exists

XML Example
'''''''''''

.. code:: http

    POST /content/typegroups/<ID>/types HTTP/1.1
    Accept: application/vnd.ez.api.ContentType
    Content-Type: application/vnd.ez.api.ContentTypeCreate
    Content-Length: xxx

.. code:: xml

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

.. code:: http

    HTTP/1.1 201 Created
    Location: /content/types/32/draft
    Accept-Patch:  application/vnd.ez.api.ContentTypeUpdate+(json|xml)
    ETag: "45674567543546"
    Content-Type: application/vnd.ez.api.ContentType+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentType href="/content/types/32/draft" media-type="application/vnd.ez.api.ContentType+xml">
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
      <Creator href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
      <Modifier href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
      <remoteId>remoteId-qwert548</remoteId>
      <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
      <nameSchema>&lt;title&gt;</nameSchema>
      <isContainer>true</isContainer>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
      <defaultSortField>PATH</defaultSortField>
      <defaultSortOrder>ASC</defaultSortOrder>
      <FieldDefinitions href="/content/types/32/draft/fielddefinitions" media-type="application/vnd.ez.api.FieldDefinitionList+xml">
        <FieldDefinition href="/content/types/32/draft/fielddefinitions/34" media-type="application/vnd.ez.api.FieldDefinition+xml">
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
        <FieldDefinition href="/content/types/32/draft/fielddefinitions/36" media-type="application/vnd.ez.api.FieldDefinition+xml">
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

.. code:: http

         HTTP/1.1 201 Created
         Location: /content/types/<newId>


:Error Codes:
    :401: If the user is not authorized to copy this content type

List Content Types
``````````````````
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

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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
    :If-None-Match: <etag>

:Response:

.. code:: http

          HTTP/1.1 200 OK
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/types/<ID>/draft
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentType_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the draft.
    :403: If a content type with the given new identifier already exists.
    :404: If there is no draft on this content type

XML Example
'''''''''''

.. code:: http

    POST /content/types/32/draft HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Accept: application/vnd.ez.api.ContentTypeInfo+xml
    Content-Type: application/vnd.ez.api.ContentTypeUpdate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentTypeUpdate>
      <names>
        <value languageCode="ger-DE">Neuer Content Typ</value>
      </names>
      <descriptions>
        <value languageCode="ger-DE">Das ist ein neuer Content Typ</value>
      </descriptions>
    </ContentTypeUpdate>

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.ContentTypeInfo+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <ContentType href="/content/types/32/draft" media-type="application/vnd.ez.api.ContentType+xml">
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
      <Creator href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
      <Modifier href="/user/users/13" media-type="application/vnd.ez.api.User+xml"/>
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

.. code:: http

          HTTP/1.1 201 Created
          Location: /content/types/<ID>/draft/fielddefinitions/<newId>
          Accept-Patch:  application/vnd.ez.api.FieldDefinitionUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.FieldDefinitionUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.FieldDefinitionUpdate+(json|xml)
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
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

.. code:: http

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

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentType_

:Error Codes:
    :401: If the user is not authorized to publish this content type draft
    :403: If the content type draft is not complete e.g. there is no field definition provided
    :404: If there is no draft or content type with the given ID

Delete Content Type Draft
`````````````````````````
:Resource: /content/types/<ID>/draft
:Method: DELETE
:Description: the given content type draft is deleted
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type draft
    :404: If the content type/draft does not exist


Delete Content Type
```````````````````
:Resource: /content/types/<ID>
:Method: DELETE
:Description: the given content type is deleted
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If there are object instances of this content type - the response should contain an ErrorMessage_
    :404: If the content type does not exist


Get Groups of Content Type
``````````````````````````
:Resource: /content/type/<ID>/groups
:Method: GET
:Description: Returns the content type groups the content type belongs to.
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroupRefList+xml:  if set the list is returned in xml format (see ContentType_)
         :application/vnd.ez.api.ContentTypeGroupRefList+json:  if set the list is returned in json format (see ContentType_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_

:ErrorCodes:
    :401: If the user is not authorized to read this content type
    :404: If the content type does not exist

XML Example
'''''''''''

.. code:: http

    GET /content/types/32/groups HTTP/1.1
    Accept: application/vnd.ez.api.ContentTypeGroupRefList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.ContentTypeGroupRefList+xml
    Gontent-Length: xxx

.. code:: xml

    <ContentTypeGroupRefList>
      <ContentTypeGroupRef href="/content/typegroups/7" media-type="application/vnd.ez.api.ContentTypeGroup+xml"/>
    </ContentTypeGroupRefList>

Link Group to Content Type
``````````````````````````
:Resource: /content/types/<ID>/groups
:Method: POST
:Description: links a content type group to the content type and returns the updated group list
:Parameters:
    :group: (uri) the uri of the group to which the content type should be linked
:Headers:
    :Accept:
         :application/vnd.ez.api.ContentTypeGroupRefList+xml:  if set the list is returned in xml format (see ContentTypeGroup_)
         :application/vnd.ez.api.ContentTypeGroupRefList+json:  if set the list is returned in json format (see ContentTypeGroup_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to add a group
    :403: If the content type is already assigned to the group

XML Example
'''''''''''

.. code:: http

    POST /content/types/32/groups?/content/typegroups/10
    Accept: application/vnd.ez.api.ContentTypeGroupRefList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.ContentTypeGroupRefList+xml
    Gontent-Length: xxx

.. code:: xml

    <ContentTypeGroupRefList>
      <ContentTypeGroupRef href="/content/typegroups/7" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
          <unlink href="/content/type/32/groups/7" method="DELETE"/>
      </ContentTypeGroupRefList>
      <ContentTypeGroupRef href="/content/typegroups/10" media-type="application/vnd.ez.api.ContentTypeGroup+xml">
          <unlink href="/content/type/32/groups/10" method="DELETE"/>
      </ContentTypeGroupRefList>

    </ContentTypeGroupRefList>

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

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          ContentTypeGroup_

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the given group is the last one
    :404: If the resource does not exist

XML Example
'''''''''''

.. code:: http

    DELETE /content/types/32/groups/7
    Accept: application/vnd.ez.api.ContentTypeGroupRefList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.ContentTypeGroupRefList+xml
    Gontent-Length: xxx

.. code:: xml

    <ContentTypeGroupRefList>
      <ContentTypeGroupRef href="/content/typegroups/10" media-type="application/vnd.ez.api.ContentTypeGroup+xml"/>
    </ContentTypeGroupRefList>


User Management
===============

Overview
--------

============================================= ===================== ===================== ===================== =======================
Resource                                      POST                  GET                   PUT                   DELETE
--------------------------------------------- --------------------- --------------------- --------------------- -----------------------
/user/groups                                  .                     load all topl. groups .                     .
/user/groups/root                             .                     redirect to root      .                     .
/user/groups/<path>                           .                     load user group       update user group     delete user group
/user/groups/<path>/users                     .                     load users of group   .                     .
/user/groups/<path>/subgroups                 create user group     load sub groups       .                     remove all sub groups
/user/groups/<path>/roles                     assign role to group  load roles of group   .                     .
/user/groups/<path>/roles/<ID>                .                     .                     .                     unassign role from group
/user/users                                   create user           list users            .                     .
/user/users/<ID>                              .                     load user             update user           delete user
/user/users/<ID>/groups                       .                     load groups of user   add to group          .
/user/users/<ID>/drafts                       .                     list all drafts owned .                     .
                                                                    by the user
/user/users/<ID>/roles                        assign role to user   load roles of group   .                     .
/user/users/<ID>/roles/<ID>                   .                     load roleassignment   .                     unassign role from user
/user/roles                                   create new role       load all roles        .                     .
/user/roles/<ID>                              .                     load role             update role           delete role
/user/roles/<ID>/policies                     create policy         load policies         .                     delete all policies from role
/user/roles/<ID>/policies/<ID>                .                     load policy           update policy         delete policy
/user/sessions                                create session        .                     .                     .
/user/sessions/<sessionID>                    .                     .                     .                     delete session
============================================= ===================== ===================== ===================== =======================


Managing Users and Groups
~~~~~~~~~~~~~~~~~~~~~~~~~

Get Root User Group
```````````````````
:Resource: /user/groups/root
:Method: GET
:Description: Redirects to the root user group
:Response:

.. code:: http

    HTTP/1.1 301 Moved Permanently
    Location: /user/groups/<rootPath>

Example see UserGroupExample_

Load User Group
```````````````
:Resource: /user/groups/<path>
:Method: GET
:Description: loads a user groups for the given <path>
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroup+xml:  if set the new user group is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroup+json:  if set the new user group is returned in json format (see UserGroup_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.UserGroupUpdate+(json|xml)
          ETag: "<Etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist

Example see UserGroupExample_

Load User Groups
````````````````
:Resource: /user/groups
:Method: GET
:Description: Load user groups for either an id or remoteId or role.
:Parameters:
    :roleId: lists user groups assigned to the given role
    :id: retieves the user group for the given Id
    :remoteId: retieves the user group for the given remoteId
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroupList+xml:  if set the user group list returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupList+json:  if set the user group list is returned in json format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+xml:  if set the link list of user groups is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+json:  if set the link list of user groups is returned in json format (see UserGroup_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :401: If the user has no permission to read user groups


Create User Group
`````````````````
:Resource: /user/groups/<path>/subgroups
:Method: POST
:Description: Creates a new user group under the given parent. To create a top level group use /user/groups/subgroups
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroup+xml:  if set the new user group is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroup+json:  if set the new user group is returned in json format (see UserGroup_)
    :Content-Type:
         :application/vnd.ez.api.UserGroupCreate+json: the UserGroupCreate_  schema encoded in json
         :application/vnd.ez.api.UserGroupCreate+xml: the UserGroupCreate_  schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /user/groups/<newpath>
          Accept-Patch:  application/vnd.ez.api.UserGroupUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this user group


.. _UserGroupExample:


XML Example
'''''''''''

Creating a top level group

.. code:: http

    GET /user/groups/1/5 HTTP/1.1
    Accept: application/vnd.ez.api.UserGroup+xml

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroup href="/user/groups/1/5" id="5" media-type="application/vnd.ez.api.UserGroup+xml" remoteId="remoteId-qwert001">
      <ContentType href="/content/types/5" media-type="application/vnd.ez.api.ContentType+xml" />
      <name>Users</name>
      <Versions href="/content/objects/4/versions" media-type="application/vnd.ez.api.VersionList+xml" />
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/5" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/4/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/user/users/13" media-type="application/vnd.ez.api.User+xml" />
      <publishDate>2011-02-31T16:00:00</publishDate>
      <lastModificationDate>2011-02-31T16:00:00</lastModificationDate>
      <mainLanguageCode>eng-UK</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
      <Content>
        <VersionInfo>
          <id>22</id>
          <versionNo>1</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2011-02-31T16:00:00</modificationDate>
          <Creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>2011-02-31T16:00:00</creationDate>
          <initialLanguageCode>eng-UK</initialLanguageCode>
          <Content href="/content/objects/4" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
        <Fields>
          <field>
            <id>1234</id>
            <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
            <languageCode>eng-UK</languageCode>
            <fieldValue>Users</fieldValue>
          </field>
          <field>
            <id>1235</id>
            <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
            <languageCode>eng-UK</languageCode>
            <fieldValue>Main Group</fieldValue>
          </field>
        </Fields>
        <Relations />
      </Content>
      <SubGroups href="/user/groups/1/5/subgroups" media-type="application/vnd.ez.api.UserGroupList+xml"/>
      <Users href="/user/groups/1/5/users" media-type="application/vnd.ez.api.UserList+xml"/>
      <Roles href="/user/groups/1/5/roles" media-type="application/vnd.ez.api.RoleList+xml"/>
    </UserGroup>

.. code:: http

    POST /user/groups/1/5/subgroups HTTP/1.1
    Accept: application/vnd.ez.api.UserGroup+xml
    Content-Type: application/vnd.ez.api.UserGroupCreate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroupCreate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <remoteId>remoteId-qwert098</remoteId>
      <fields>
        <field>
          <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>UserGroup</fieldValue>
        </field>
        <field>
          <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>This is the description of the user group</fieldValue>
        </field>
      </fields>
    </UserGroupCreate>

.. code:: http

    HTTP/1.1 201 Created
    Location: /user/groups/1/5/65
    Accept-Patch:  application/vnd.ez.api.UserGroupUpdate+(json|xml)
    ETag: "348506873565465"
    Content-Type: application/vnd.ez.api.UserGroup+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroup href="/user/groups/1/5/65" id="65" media-type="application/vnd.ez.api.UserGroup+xml" remoteId="remoteId-qwert543">
      <ContentType href="/content/types/5" media-type="application/vnd.ez.api.ContentType+xml" />
      <name>UserGroup</name>
      <Versions href="/content/objects/123/versions" media-type="application/vnd.ez.api.VersionList+xml" />
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/5/65" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/123/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/user/users/13" media-type="application/vnd.ez.api.User+xml" />
      <publishDate>2012-02-31T16:00:00</publishDate>
      <lastModificationDate>2012-02-31T16:00:00</lastModificationDate>
      <mainLanguageCode>eng-UK</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
      <Content>
        <VersionInfo>
          <id>123</id>
          <versionNo>2</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2012-02-31T16:00:00</modificationDate>
          <Creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>2012-02-31T16:00:00</creationDate>
          <initialLanguageCode>eng-UK</initialLanguageCode>
          <Content href="/content/objects/123" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
        <Fields>
          <field>
            <id>1234</id>
            <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
            <languageCode>eng-UK</languageCode>
            <fieldValue>UserGroup</fieldValue>
          </field>
          <field>
            <id>1235</id>
            <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
            <languageCode>eng-UK</languageCode>
            <fieldValue>This is the description of the user group</fieldValue>
          </field>
        </Fields>
        <Relations />
      </Content>
      <ParentUserGroup href="/user/groups/1/5" media-type="application/vnd.ez.api.UserGroup+xml" />
      <SubGroups href="/user/groups/1/5/65/subgroups" media-type="application/vnd.ez.api.UserGroupList+xml"/>
      <Users href="/user/groups/1/5/65/users" media-type="application/vnd.ez.api.UserList+xml"/>
      <Roles href="/user/groups/1/5/65/roles" media-type="application/vnd.ez.api.RoleList+xml"/>
    </UserGroup>




Update User Group
`````````````````
:Resource: /user/groups/<path>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates a user group
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroup+xml:  if set the new user group is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroup+json:  if set the new user group is returned in json format (see UserGroup_)
    :Content-Type:
         :application/vnd.ez.api.UserGroupUpdate+json: the UserGroupUpdate_  schema encoded in json
         :application/vnd.ez.api.UserGroupUpdate+xml: the UserGroupUpdate_  schema encoded in xml
    :If-Match: <etag> Causes to patch only if the specified etag is the current one. Otherwise a 412 is returned.
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.UserGroupUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the user group
    :412: If the current ETag does not match with the provided one in the If-Match header


XML Example
'''''''''''

.. code:: http

    POST /user/groups/1/5/65 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Accept: application/vnd.ez.api.UserGroup+xml
    If-Match: "348506873463455"
    Content-Type: application/vnd.ez.api.UserGroupUpdate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroupUpdate>
      <fields>
        <field>
          <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>This is another description</fieldValue>
        </field>
      </fields>
    </UserGroupUpdate>

.. code:: http

    HTTP/1.1 200 OK
    Accept-Patch:  application/vnd.ez.api.UserGroupUpdate+(json|xml)
    ETag: "348506873465777"
    Content-Type: application/vnd.ez.api.UserGroup+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroup href="/user/groups/1/5/65" id="65" media-type="application/vnd.ez.api.UserGroup+xml" remoteId="remoteId-qwert543">
      <ContentType href="/content/types/5" media-type="application/vnd.ez.api.ContentType+xml" />
      <name>UserGroup</name>
      <Versions href="/content/objects/123/versions" media-type="application/vnd.ez.api.VersionList+xml" />
      <Section href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/5/65" media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/123/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/user/users/13" media-type="application/vnd.ez.api.User+xml" />
      <publishDate>2012-02-31T16:00:00</publishDate>
      <lastModificationDate>2012-02-31T16:00:00</lastModificationDate>
      <mainLanguageCode>eng-UK</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
      <Content>
        <VersionInfo>
          <id>125</id>
          <versionNo>3</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2012-03-31T16:00:00</modificationDate>
          <Creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>2012-03-31T16:00:00</creationDate>
          <initialLanguageCode>eng-UK</initialLanguageCode>
          <Content href="/content/objects/123" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
        <Fields>
          <field>
            <id>1234</id>
            <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
            <languageCode>eng-UK</languageCode>
            <fieldValue>UserGroup</fieldValue>
          </field>
          <field>
            <id>1235</id>
            <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
            <languageCode>eng-UK</languageCode>
            <fieldValue>This is another description of the user group</fieldValue>
          </field>
        </Fields>
        <Relations />
      </Content>
      <ParentUserGroup href="/user/groups/1/5" media-type="application/vnd.ez.api.UserGroup+xml" />
      <SubGroups href="/user/groups/1/5/65/subgroups" media-type="application/vnd.ez.api.UserGroupList+xml"/>
      <Users href="/user/groups/1/5/65/users" media-type="application/vnd.ez.api.UserList+xml"/>
      <Roles href="/user/groups/1/5/65/roles" media-type="application/vnd.ez.api.RoleList+xml"/>
    </UserGroup>


Delete User Group
`````````````````
:Resource: /user/groups/<path>
:Method: DELETE
:Description: the given user group is deleted
:Response:

.. code:: xml

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type
    :403: If the user group is not empty

Load Users of Group
```````````````````
:Resource: /user/groups/<ID>/users
:Method: GET
:Description: loads the users of the group with the given <ID>
:Headers:
    :Accept:
         :application/vnd.ez.api.UserList+xml:  if set the user list returned in xml format (see User_)
         :application/vnd.ez.api.UserList+json:  if set the user list is returned in json format (see User_)
         :application/vnd.ez.api.UserRefList+xml:  if set the link list of users returned in xml format (see User_)
         :application/vnd.ez.api.UserRefList+json:  if set the link list of users is returned in json format (see User_)
:Parameters: :limit:  only <limit> items will be returned started by offset
             :offset: offset of the result set
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          User_

:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist


Move user Group
```````````````
:Resource: /user/groups/<path>
:Method: MOVE or POST with header X-HTTP-Method-Override: MOVE
:Description: moves the user group to another parent.
:Headers:
    :Destination: A parent group resource to which the location is moved
:Response:

.. code:: http

        HTTP/1.1 201 Created
        Location: /user/groups/<newPath>

:Error Codes:
    :401: If the user is not authorized to update the user group
    :403: If the new parenbt does not exist
    :404: If the user group does not exist

Load Subgroups
``````````````
:Resource: /user/groups/<ID>/subgroups
:Method: GET
:Description: Returns a list of the sub groups
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroupList+xml:  if set the user group list returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupList+json:  if set the user group list is returned in json format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+xml:  if set the link list of user groups is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+json:  if set the link list of user groups is returned in json format (see UserGroup_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_


:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user group does not exist

Create User
```````````
:Resource: /user/groups/<path>/users
:Method: POST
:Description: Creates a new user in the given group
:Headers:
    :Accept:
         :application/vnd.ez.api.User+xml:  if set the new user is returned in xml format (see User_)
         :application/vnd.ez.api.User+json:  if set the new user is returned in json format (see User_)
    :Content-Type:
         :application/vnd.ez.api.UserCreate+json: the UserCreate_  schema encoded in json
         :application/vnd.ez.api.UserCreate+xml: the UserCreate_  schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /user/users/<ID>
          Accept-Patch:  application/vnd.ez.api.UserUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          User_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this user
    :403: If a user with the same login already exists
    :404: If the group with the given ID does not exist

XML Example
'''''''''''

.. code:: http

    POST /user/groups/1/5/65/users HTTP/1.1
    Accept: application/vnd.ez.api.User+xml
    Content-Type: application/vnd.ez.api.UserCreate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserCreate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <remoteId>remoteId-qwert426</remoteId>
      <login>john</login>
      <email>john.doe@example.net</email>
      <password>john-does-password</password>
      <fields>
        <field>
          <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>John</fieldValue>
        </field>
        <field>
          <fieldDefinitionIdentifier>last_name</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>Doe</fieldValue>
        </field>
      </fields>
    </UserCreate>

.. code:: http

    HTTP/1.1 201 Created
    Location: /user/users/99
    Accept-Patch: application/vnd.ez.api.UserUpdate+xml
    ETag: "34567340896734095867"
    Content-Type: application/vnd.ez.api.User+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <User href="/user/users/99" id="99" media-type="application/vnd.ez.api.User+xml"
      remoteId="remoteId-qwert426">
      <ContentType href="/content/types/4" media-type="application/vnd.ez.api.ContentType+xml" />
      <name>John</name>
      <Versions href="/content/objects/79" media-type="application/vnd.ez.api.VersionList+xml" />
      <Section href="/content/section/3" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/5/65"
        media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/79/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
      <publishDate>2001-04-01T12:00:00</publishDate>
      <lastModificationDate>2001-04-01T12:00:00</lastModificationDate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
      <login>john</login>
      <email>john.doe@example.net</email>
      <enabled>true</enabled>
      <Content>
        <VersionInfo>
          <id>1243</id>
          <versionNo>1</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2001-04-01T12:00:00</modificationDate>
          <Creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>2001-04-01T12:00:00</creationDate>
          <initialLanguageCode>eng-UK</initialLanguageCode>
          <Content href="/content/objects/79" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
        <fields>
          <field>
            <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
            <languageCode>eng-US</languageCode>
            <fieldValue>John</fieldValue>
          </field>
          <field>
            <fieldDefinitionIdentifier>last_name</fieldDefinitionIdentifier>
            <languageCode>eng-US</languageCode>
            <fieldValue>Doe</fieldValue>
          </field>
        </fields>
      </Content>
      <Roles href="/user/users/99/roles" media-type="application/vnd.ez.api.RoleAssignmentList+xml" />
      <UserGroups href="/user/users/99/group" media-type="vns.ez.api.UserGroupRefList+xml" />
    </User>


List Users
``````````
:Resource: /user/users
:Method: GET
:Description: Load users either for a given remoteId or role
:Parameters:
    :roleId: lists users assigned to the given role
    :remoteId: retieves the user for the given remoteId
:Headers:
    :Accept:
         :application/vnd.ez.api.UserList+xml:  if set the user list returned in xml format (see User_)
         :application/vnd.ez.api.UserList+json:  if set the user list is returned in json format (see User_)
         :application/vnd.ez.api.UserRefList+xml:  if set the link list of users returned in xml format (see User_)
         :application/vnd.ez.api.UserRefList+json:  if set the link list of users is returned in json format (see User_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          User_

:Error Codes:
    :401: If the user has no permission to read users

Load User
`````````
:Resource: /user/users/<ID>
:Method: GET
:Description: loads the users of the group with the given <ID>
:Headers:
    :Accept:
         :application/vnd.ez.api.User+xml:  if set the new user is returned in xml format (see User_)
         :application/vnd.ez.api.User+json:  if set the new user is returned in json format (see User_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Location: /user/users/<ID>
          Accept-Patch:  application/vnd.ez.api.UserUpdate+(json|xml)
          ETag: "<Etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          User_

:Error Codes:
    :401: If the user has no permission to read users
    :404: If the user does not exist

Update User
```````````
:Resource: /user/users/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates a user
:Headers:
    :Accept:
         :application/vnd.ez.api.User+xml:  if set the new user  is returned in xml format (see User_)
         :application/vnd.ez.api.User+json:  if set the new user  is returned in json format (see User_)
    :Content-Type:
         :application/vnd.ez.api.UserUpdate+json: the UserUpdate_  schema encoded in json
         :application/vnd.ez.api.UserUpdate+xml: the UserUpdate_  schema encoded in xml
    :If-Match: <etag> Causes to patch only if the specified etag is the current one. Otherwise a 412 is returned.
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.UserUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          User_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the user
    :404: If the user does not exist
    :412: If the current ETag does not match with the provided one in the If-Match header

XML Example
'''''''''''

.. code:: http

    POST /user/users/99 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Accept: application/vnd.ez.api.User+xml
    Content-Type: application/vnd.ez.api.UserUpdate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserUpdate>
      <email>john.doe@mooglemail.com</email>
      <fields>
        <field>
          <fieldDefinitionIdentifier>signature</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>
          John Doe
          Example Systems
          john.doe@mooglemail.com
          skype: johndoe
          </fieldValue>
        </field>
      </fields>
    </UserUpdate>

.. code:: http

    HTTP/1.1 200 OK
    Accept-Patch:  application/vnd.ez.api.UserUpdate+(json|xml)
    ETag: "435908672409561"
    Content-Type: application/vnd.ez.api.User+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <User href="/user/users/99" id="99" media-type="application/vnd.ez.api.User+xml"
      remoteId="remoteId-qwert426">
      <ContentType href="/content/types/4" media-type="application/vnd.ez.api.ContentType+xml" />
      <name>John</name>
      <Versions href="/content/objects/79" media-type="application/vnd.ez.api.VersionList+xml" />
      <Section href="/content/section/3" media-type="application/vnd.ez.api.Section+xml" />
      <MainLocation href="/content/locations/1/5/65"
        media-type="application/vnd.ez.api.Location+xml" />
      <Locations href="/content/objects/79/locations" media-type="application/vnd.ez.api.LocationList+xml" />
      <Owner href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
      <publishDate>2001-04-01T12:00:00</publishDate>
      <lastModificationDate>2001-04-01T12:00:00</lastModificationDate>
      <mainLanguageCode>eng-US</mainLanguageCode>
      <alwaysAvailable>true</alwaysAvailable>
      <login>john</login>
      <email>john.doe@mooglemail.com</email>
      <enabled>true</enabled>
      <Content>
        <VersionInfo>
          <id>1243</id>
          <versionNo>1</versionNo>
          <status>PUBLISHED</status>
          <modificationDate>2001-04-01T12:00:00</modificationDate>
          <Creator href="/user/users/14" media-type="application/vnd.ez.api.User+xml" />
          <creationDate>2001-04-01T12:00:00</creationDate>
          <initialLanguageCode>eng-UK</initialLanguageCode>
          <Content href="/content/objects/79" media-type="application/vnd.ez.api.ContentInfo+xml" />
        </VersionInfo>
        <fields>
          <field>
            <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
            <languageCode>eng-US</languageCode>
            <fieldValue>John</fieldValue>
          </field>
          <field>
            <fieldDefinitionIdentifier>last_name</fieldDefinitionIdentifier>
            <languageCode>eng-US</languageCode>
            <fieldValue>Doe</fieldValue>
          </field>
        </fields>
        <field>
          <fieldDefinitionIdentifier>signature</fieldDefinitionIdentifier>
          <languageCode>eng-US</languageCode>
          <fieldValue>
          John Doe
          Example Systems
          john.doe@mooglemail.com
          skype: johndoe
          </fieldValue>
        </field>
      </Content>
      <Roles href="/user/users/99/roles" media-type="application/vnd.ez.api.RoleAssignmentList+xml" />
      <UserGroups href="/user/users/99/group" media-type="vns.ez.api.UserGroupRefList+xml" />
    </User>


Delete User
```````````
:Resource: /user/users/<ID>
:Method: DELETE
:Description: the given user is deleted
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this user
    :403: If the user is the same as the authenticated user
    :404: If the user does not exist

Load Groups Of User
```````````````````
:Resource: /user/users/<ID>/groups
:Method: GET
:Description: Returns a list of user groups the user belongs to. The returned list includes the resources for unassigning a user group if the user is in multiple groups.
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroupRefList+xml:  if set the link list of user groups is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+json:  if set the link list of user groups is returned in json format (see UserGroup_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :401: If the user has no permission to read user groups
    :404: If the user does not exist

XML Example
'''''''''''

.. code:: http

    GET /user/users/45/groups HTTP/1.1
    Accept: application/vnd.ez.api.UserGroupRefList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.UserGroupRefList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroupRefList href="/user/users/45/groups"
      media-type="application/vnd.ez.api.UserGroupRefList">
      <UserGroup href="/user/groups/1/5/34" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/34" method="DELETE" />
      </UserGroup>
      <UserGroup href="/user/groups/1/5/78" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/78" method="DELETE" />
      </UserGroup>
    </UserGroupRefList>


Assign User Group
`````````````````
:Resource: /user/users/<ID>/groups
:Method: POST
:Description: Assigns the user to a user group
:Parameters: :group: the new parent group resource of the user
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroupRefList+xml:  if set the link list of user groups is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+json:  if set the link list of user groups is returned in json format (see UserGroup_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :401: If the user is not authorized to assign user groups
    :403: - If the new user group does not exist
          - If the user is already in this group
    :404: If the user does not exist

XML Example
'''''''''''

.. code:: http

    POST /user/users/45/groups?/user/groups/1/5/88 HTTP/1.1
    Accept: application/vnd.ez.api.UserGroupRefList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.UserGroupRefList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroupRefList href="/user/users/45/groups"
      media-type="application/vnd.ez.api.UserGroupRefList">
      <UserGroup href="/user/groups/1/5/34" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/34" method="DELETE" />
      </UserGroup>
      <UserGroup href="/user/groups/1/5/78" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/78" method="DELETE" />
      </UserGroup>
      <UserGroup href="/user/groups/1/5/88" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/88" method="DELETE" />
      </UserGroup>
    </UserGroupRefList>


Unassign User Group
```````````````````
:Resource: /user/users/<ID>/groups/<ID>
:Method: DELETE
:Description: Unassigns the user from a user group
:Headers:
    :Accept:
         :application/vnd.ez.api.UserGroupRefList+xml:  if set the link list of user groups is returned in xml format (see UserGroup_)
         :application/vnd.ez.api.UserGroupRefList+json:  if set the link list of user groups is returned in json format (see UserGroup_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          UserGroup_

:Error Codes:
    :401: If the user is not authorized to unassign user groups
    :403: If the user is not in the given group
    :404: If the user does not exist

XML Example
'''''''''''

.. code:: http

    DELETE /user/users/45/groups/78 HTTP/1.1
    Accept: application/vnd.ez.api.UserGroupRefList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.UserGroupRefList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <UserGroupRefList href="/user/users/45/groups"
      media-type="application/vnd.ez.api.UserGroupRefList">
      <UserGroup href="/user/groups/1/5/34" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/34" method="DELETE" />
      </UserGroup>
      <UserGroup href="/user/groups/1/5/88" media-type="application/vnd.ez.api.UserGroup">
        <unassign href="/user/users/45/groups/88" method="DELETE" />
      </UserGroup>
    </UserGroupRefList>



Managing Roles and Policies
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create Role
```````````
:Resource: /user/roles
:Method: POST
:Description: Creates a new role
:Headers:
    :Accept:
         :application/vnd.ez.api.Role+xml:  if set the new user is returned in xml format (see Role_)
         :application/vnd.ez.api.Role+json:  if set the new user is returned in json format (see Role_)
    :Content-Type:
         :application/vnd.ez.api.RoleInput+json: the RoleInput_  schema encoded in json
         :application/vnd.ez.api.RoleInput+xml: the RoleInput_  schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /user/roles/<ID>
          Accept-Patch:  application/vnd.ez.api.RoleUpdate+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Role_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create this role

XML Example
'''''''''''

.. code:: http

    POST /user/roles HTTP/1.1
    Accept: application/vnd.ez.api.Role+xml
    Content-Type: application/vnd.ez.api.RoleInput+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <RoleInput>
      <identifier>NewRole</identifier>
    </RoleInput>

.. code:: http

    HTTP/1.1 201 Created
    Location: /user/roles/11
    Accept-Patch: application/vnd.ez.api.RoleUpdate+xml
    ETag: "465897639450694836"
    Content-Type: application/vnd.ez.api.Role+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Role href="/user/roles/11" media-type="application/vnd.ez.api.Role+xml">
      <identifier>NewRole</identifier>
      <Policies href="/user/roles/11/policies" media-type="application/vnd.ez.api.PolicyList+xml"/>
    </Role>



Load Roles
``````````
:Resource: /user/roles
:Method: GET
:Description: Returns a list of all roles
:Parameters:
    :identifier: Restricts the result to a list containing the role with the given identifier. If the role is not found an empty list is returned.
    :limit:    only <limit> items will be returned started by offset
    :offset:   offset of the result set
:Headers:
    :Accept:
         :application/vnd.ez.api.RoleList+xml:  if set the user list returned in xml format (see Role_)
         :application/vnd.ez.api.RoleList+json:  if set the user list is returned in json format (see Role_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
          ETag: "<Etag>"
.. parsed-literal::
          Role_

:Error Codes:
    :401: If the user has no permission to read roles

Load Role
`````````
:Resource: /user/roles/<ID>
:Method: GET
:Description: loads a role for the given <ID>
:Headers:
    :Accept:
         :application/vnd.ez.api.Role+xml:  if set the user list returned in xml format (see Role_)
         :application/vnd.ez.api.Role+json:  if set the user list is returned in json format (see Role_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.RoleInput+(json|xml)
          ETag: "<Etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>

.. parsed-literal::
          Role_

:Error Codes:
    :401: If the user has no permission to read roles
    :404: If the role does not exist

Update Role
```````````
:Resource: /user/roles/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: Updates a role
:Headers:
    :Accept:
         :application/vnd.ez.api.Role+xml:  if set the new user  is returned in xml format (see Role_)
         :application/vnd.ez.api.Role+json:  if set the new user  is returned in json format (see Role_)
    :Content-Type:
         :application/vnd.ez.api.RoleInput+json: the RoleInput  schema encoded in json
         :application/vnd.ez.api.RoleInput+xml: the RoleInput  schema encoded in xml
    :If-Match: <etag> Causes to patch only if the specified etag is the current one. Otherwise a 412 is returned.
:Response:

.. code:: xml

          HTTP/1.1 200 OK
          Accept-Patch:  application/vnd.ez.api.RoleInput+(json|xml)
          ETag: "<newEtag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Role_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the role
    :412: If the current ETag does not match with the provided one in the If-Match header

Delete Role
```````````
:Resource: /user/roles/<ID>
:Method: DELETE
:Description: the given role and all assignments to users or user groups are deleted
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this role

Load Roles for User or User Group
`````````````````````````````````
:Resource: - /user/groups/<path>/roles for user group
           - /user/users/<ID>/roles for user
:Method: GET
:Description: Returns a list of all roles assigned to the given user group
:Headers:
    :Accept:
         :application/vnd.ez.api.RoleAssignmentList+xml:  if set the role assignment list  is returned in xml format (see Role_)
         :application/vnd.ez.api.RoleAssignmentList+json:  if set the role assignment list  is returned in json format (see Role_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Role_

:Error Codes:
    :401: If the user has no permission to read roles

XML Example
'''''''''''

.. code:: http

    GET /user/groups/1/5/65/roles HTTP/1.1
    Accept: application/vnd.ez.api.RoleAssignmentList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.RoleAssignmentList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <RoleAssignmentList href="/user/groups/1/5/65/roles" media-type="application/vnd.ez.api.RoleAssignmentList+xml">
      <RoleAssignment href="/user/groups/1/5/65/roles/5" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <Role href="/user/roles/5" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
      <RoleAssignment href="/user/groups/1/5/65/roles/7" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <limitation identifier="Subtree">
          <values>
              <ref href="/content/locations/1/23/88" media-type="application/vnd.ez.api.Location+xml" />
              <ref href="/content/locations/1/32/67" media-type="application/vnd.ez.api.Location+xml" />
          </values>
        </limitation>
        <Role href="/user/roles/7" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
    </RoleAssignmentList>

Load Assignment
```````````````
:Resource: - /user/groups/<path>/roles/<ID> for user group
           - /user/users/<ID>/roles/<ID> for user
:Method: GET
:Description: Returns a roleassignment to the given user or user group
:Headers:
    :Accept:
         :application/vnd.ez.api.RoleAssignment+xml:  if set the role assignment list  is returned in xml format (see Role_)
         :application/vnd.ez.api.RoleAssignment+json:  if set the role assignment list  is returned in json format (see Role_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Role_

:Error Codes:
    :401: If the user has no permission to read roles



Assign Role to User or User Group
`````````````````````````````````
:Resource: - /user/groups/<path>/roles for user group
           - /user/users/<ID>/roles for user
:Method: POST
:Description: assign a role to a user or user group.
:Headers:
    :Accept:
         :application/vnd.ez.api.RoleAssignmentList+xml:  if set the updated role assignment list  is returned in xml format (see Role_)
         :application/vnd.ez.api.RoleAssignmentList+json:  if set the updated role assignment list  is returned in json format (see Role_)
    :Content-Type:
         :application/vnd.ez.api.RoleAssignInput+json: the RoleAssignInput_  schema encoded in json
         :application/vnd.ez.api.RoleAssignInput+xml: the RoleAssignInput_  schema encoded in xml
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Role_

:Error Codes:
    :401: If the user is not authorized to assign this role

XML Example
'''''''''''

.. code:: http

    POST /user/groups/1/5/65/roles HTTP/1.1
    Accept: application/vnd.ez.api.RoleAssignmentList+xml
    Content-Type:  application/vnd.ez.api.RoleAssignInput+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <RoleAssignInput>
      <Role href="/user/role/11" media-type="application/vnd.ez.api.RoleAssignInput+xml"/>
      <limitation identifier="Section">
          <values>
              <ref href="/content/sections/1" media-type="application/vnd.ez.api.Section+xml" />
              <ref href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
          </values>
      </limitation>
    </RoleAssignInput>

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.RoleAssignmentList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <RoleAssignmentList href="/user/groups/1/5/65/roles" media-type="application/vnd.ez.api.RoleAssignmentList+xml">
      <RoleAssignment href="/user/groups/1/5/65/roles/5" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <Role href="/user/roles/5" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
      <RoleAssignment href="/user/groups/1/5/65/roles/7" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <limitation identifier="Subtree">
          <values>
              <ref href="/content/locations/1/23/88" media-type="application/vnd.ez.api.Location+xml" />
              <ref href="/content/locations/1/32/67" media-type="application/vnd.ez.api.Location+xml" />
          </values>
        </limitation>
        <Role href="/user/roles/7" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
      <RoleAssignment href="/user/groups/1/5/65/roles/11" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <limitation identifier="Section">
          <values>
              <ref href="/content/sections/1" media-type="application/vnd.ez.api.Section+xml" />
              <ref href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
          </values>
        </limitation>
        <Role href="/user/roles/11" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
    </RoleAssignmentList>




Unassign Role from User or User Group
``````````````````````````````````````
:Resource: - /user/groups/<path>/roles/<ID> for user group
           - /user/users/<ID>/roles/<ID> for user
:Method: DELETE
:Description: the given role is removed from the user or user group
:Headers:
    :Accept:
         :application/vnd.ez.api.RoleAssignmentList+xml:  if set the updated role assignment list  is returned in xml format (see Role_)
         :application/vnd.ez.api.RoleAssignmentList+json:  if set the updated role assignment list  is returned in json format (see Role_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Role_

:Error Codes:
    :401: If the user is not authorized to delete this content type

XML Example
'''''''''''

.. code:: http

    DELETE /user/groups/1/5/65/roles/7 HTTP/1.1
    Accept: application/vnd.ez.api.RoleAssignmentList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.RoleAssignmentList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <RoleAssignmentList href="/user/groups/1/5/65/roles" media-type="application/vnd.ez.api.RoleAssignmentList+xml">
      <RoleAssignment href="/user/groups/1/5/65/roles/5" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <Role href="/user/roles/5" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
      <RoleAssignment href="/user/groups/1/5/65/roles/11" media-type="application/vnd.ez.api.RoleAssignment+xml">
        <limitation identifier="Section">
          <values>
              <ref href="/content/sections/1" media-type="application/vnd.ez.api.Section+xml" />
              <ref href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
          </values>
        </limitation>
        <Role href="/user/roles/11" media-type="application/vnd.ez.api.Role+xml"/>
      </RoleAssignment>
    </RoleAssignmentList>



Load Policies
`````````````
:Resource: /user/roles/<ID>/policies
:Method: GET
:Description: loads policies for the given role
:Headers:
    :Accept:
         :application/vnd.ez.api.PolicyList+xml:  if set the policy list  is returned in xml format (see Policy_)
         :application/vnd.ez.api.PolicyList+json:  if set the policy list  is returned in json format (see Policy_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Policy_


:Error Codes:
    :401: If the user has no permission to read roles
    :404: If the role does not exist


XML Example
'''''''''''

.. code:: http

    GET /user/roles/7/policies HTTP/1.1
    Accept: application/vnd.ez.api.PolicyList+xml

.. code:: http

    HTTP/1.1 200 OK
    Content-Type: application/vnd.ez.api.PolicyList+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <PolicyList href="/user/roles/11/policies" media-type="application/vnd.ez.api.PolicyList">
      <Policy href="/user/roles/11/policies/45" media-type="application/vnd.ez.api.Policy+xml">
        <id>45</id>
        <module>content</module>
        <function>create</function>
        <limitations>
          <limitation identifier="Class">
            <values>
              <ref href="/content/types/10" media-type="application/vnd.ez.api.ContentType+xml" />
              <ref href="/content/types/11" media-type="application/vnd.ez.api.ContentType+xml" />
              <ref href="/content/types/12" media-type="application/vnd.ez.api.ContentType+xml" />
            </values>
          </limitation>
          <limitation identifier="ParentClass">
            <values>
              <ref href="/content/types/4" media-type="application/vnd.ez.api.ContentType+xml" />
            </values>
          </limitation>
        </limitations>
      </Policy>
      <Policy href="/user/roles/11/policies/49" media-type="application/vnd.ez.api.Policy+xml">
        <id>49</id>
        <module>content</module>
        <function>read</function>
        <limitations>
          <limitation identifier="Section">
            <values>
              <ref href="/content/sections/1" media-type="application/vnd.ez.api.Section+xml" />
              <ref href="/content/sections/2" media-type="application/vnd.ez.api.Section+xml" />
              <ref href="/content/sections/4" media-type="application/vnd.ez.api.Section+xml" />
            </values>
          </limitation>
        </limitations>
      </Policy>
    </PolicyList>


Delete Policies
```````````````
:Resource: /user/roles/<ID>/policies
:Method: DELETE
:Description: all policies of the given role are deleted
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Error Codes:
    :401: If the user is not authorized to delete this content type

Load Policy
```````````
:Resource: /user/roles/<ID>/policies/<ID>
:Method: GET
:Description: loads a policy for the given module and function
:Headers:
    :Accept:
         :application/vnd.ez.api.Policy+xml:  if set the policy is returned in xml format (see Policy_)
         :application/vnd.ez.api.Policy+json:  if set the policy is returned in json format (see Policy_)
    :If-None-Match: <etag>
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch: application/vnd.ez.api.PolicyUpdate+(xml|json)
          ETag: "<etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Policy_

:Error Codes:
    :401: If the user has no permission to read roles
    :404: If the role or policy does not exist


XML Example
'''''''''''

.. code:: http

    GET /user/roles/7/policies/45 HTTP/1.1
    Accept: application/vnd.ez.api.Policy+xml

.. code:: http

    HTTP/1.1 200 OK
    Accept-Patch: application/vnd.ez.api.PolicyUpdate+(xml|json)
    ETag: "697850469873045967"
    Content-Type: application/vnd.ez.api.Policy+xml
    Content-Length: xxx

.. code:: xml

    <Policy href="/user/roles/11/policies/45" media-type="application/vnd.ez.api.Policy+xml">
      <id>45</id>
      <module>content</module>
      <function>create</function>
      <limitations>
        <limitation identifier="Class">
          <values>
            <ref href="/content/types/10" media-type="application/vnd.ez.api.ContentType+xml" />
            <ref href="/content/types/11" media-type="application/vnd.ez.api.ContentType+xml" />
            <ref href="/content/types/12" media-type="application/vnd.ez.api.ContentType+xml" />
          </values>
        </limitation>
        <limitation identifier="ParentClass">
          <values>
            <ref href="/content/types/4" media-type="application/vnd.ez.api.ContentType+xml" />
          </values>
        </limitation>
      </limitations>
    </Policy>



Create Policy
`````````````
:Resource: /user/roles/<ID>/policies/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: updates a policy
:Headers:
    :Accept:
         :application/vnd.ez.api.Policy+xml:  if set the updated policy is returned in xml format (see Policy_)
         :application/vnd.ez.api.Policy+json:  if set the updated policy is returned in json format (see Policy_)
    :Content-Type:
         :application/vnd.ez.api.PolicyCreate+xml:  if set the updated policy is returned in xml format (see Policy_)
         :application/vnd.ez.api.PolicyCreate+json:  if set the updated policy is returned in json format (see Policy_)

:Response:

.. code:: http

          HTTP/1.1 201 Created
          Location: /user/roles/<ID>/policies/<newId>
          Accept-Patch: application/vnd.ez.api.PolicyUpdate+(xml|json)
          ETag: "<new_etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Policy_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to create the policy
    :404: If the role does not exist


XML Example
'''''''''''

.. code:: http

    POST /user/roles/7/policies HTTP/1.1
    Content-Type: application/vnd.ez.api.PolicyCreate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <PolicyCreate>
      <module>content</module>
      <function>create</function>
      <limitations>
        <limitation identifier="Class">
          <values>
            <ref href="/content/types/13"/>
          </values>
        </limitation>
        <limitation identifier="ParentClass">
          <values>
            <ref href="/content/types/12"/>
          </values>
        </limitation>
      </limitations>
    </PolicyCreate>

.. code:: http

    HTTP/1.1 201 Created
    Location: /user/roles/7/policies/55
    Accept-Patch: application/vnd.ez.api.PolicyUpdate+(xml|json)
    ETag: "697850469873043234234"
    Content-Type: application/vnd.ez.api.Policy+xml
    Content-Length: xxx

.. code:: xml

    <Policy href="/user/roles/11/policies/55" media-type="application/vnd.ez.api.Policy+xml">
      <id>55</id>
      <module>content</module>
      <function>create</function>
      <limitations>
        <limitation identifier="Class">
          <values>
            <ref href="/content/types/13"/>
          </values>
        </limitation>
        <limitation identifier="ParentClass">
          <values>
            <ref href="/content/types/12"/>
          </values>
        </limitation>
      </limitations>
     </Policy>


Update Policy
`````````````
:Resource: /user/roles/<ID>/policies/<ID>
:Method: PATCH or POST with header X-HTTP-Method-Override: PATCH
:Description: updates a policy
:Headers:
    :Accept:
         :application/vnd.ez.api.Policy+xml:  if set the updated policy is returned in xml format (see Policy_)
         :application/vnd.ez.api.Policy+json:  if set the updated policy is returned in json format (see Policy_)
    :Content-Type:
         :application/vnd.ez.api.PolicyUpdate+xml:  if set the updated policy is returned in xml format (see Policy_)
         :application/vnd.ez.api.PolicyUpdate+json:  if set the updated policy is returned in json format (see Policy_)
    :If-Match: <etag> Causes to patch only if the specified etag is the current one. Otherwise a 412 is returned.
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Accept-Patch: application/vnd.ez.api.PolicyUpdate+(xml|json)
          ETag: "<new_etag>"
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Policy_

:Error Codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the user is not authorized to update the policy
    :404: If the role does not exist
    :412: If the current ETag does not match with the provided one in the If-Match header

XML Example
'''''''''''

.. code:: http

    POST /user/roles/7/policies/55 HTTP/1.1
    X-HTTP-Method-Override: PATCH
    Accept: application/vnd.ez.api.Policy+xml
    If-Match: "697850469873043236666"
    Content-Type: application/vnd.ez.api.PolicyUpdate+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <PolicyUpdate>
      <limitations>
        <limitation identifier="Class">
          <values>
            <ref href="/content/types/14"/>
          </values>
        </limitation>
        <limitation identifier="ParentClass">
          <values>
            <ref href="/content/types/10"/>
          </values>
        </limitation>
      </limitations>
    </PolicyUpdate>

.. code:: http

    HTTP/1.1 200 OK
    Accept-Patch: application/vnd.ez.api.PolicyUpdate+(xml|json)
    ETag: "697850469873043234234"
    Content-Type: application/vnd.ez.api.Policy+xml
    Content-Length: xxx

.. code:: xml

    <Policy href="/user/roles/11/policies/55" media-type="application/vnd.ez.api.Policy+xml">
      <id>55</id>
      <module>content</module>
      <function>create</function>
      <limitations>
        <limitation identifier="Class">
          <values>
            <ref href="/content/types/14"/>
          </values>
        </limitation>
        <limitation identifier="ParentClass">
          <values>
            <ref href="/content/types/10"/>
          </values>
        </limitation>
      </limitations>
     </Policy>


Delete Policy
`````````````
:Resource: /user/roles/<ID>/policies/<ID>
:Method: DELETE
:Response:

.. code:: http

        HTTP/1.1 204 No Content

:Description: the given policy is deleted
:Error Codes:
    :401: If the user is not authorized to delete this content type
    :404: If the role or policy does not exist


List Policies for user
``````````````````````
:Resource: /user/policies
:Method: GET
:Description: search all policies which are applied to a given user
:Parameters:
    :userId: the user id
:Headers:
    :Accept:
         :application/vnd.ez.api.PolicyList+xml:  if set the policy list  is returned in xml format (see Policy_)
         :application/vnd.ez.api.PolicyList+json:  if set the policy list  is returned in json format (see Policy_)
:Response:

.. code:: http

          HTTP/1.1 200 OK
          Content-Type: <depending on accept header>
          Content-Length: <length>
.. parsed-literal::
          Policy_


:Error Codes:
    :401: If the user has no permission to read roles


User sessions (login/logout)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Create session (login a User):
``````````````````````````````

:Resource:    /user/sessions
:Method:      POST
:Description: Performs a login for the user and returns the session and session cookie. The client will need to remember both session name/id and CSRF token as this is for security reasons not exposed via GET.
:Headers:
    :Accept:
         :application/vnd.ez.api.Session+xml: (see Session_)
         :application/vnd.ez.api.Session+json:  (see Session_)
    :Content-Type:
         :application/vnd.ez.api.SessionInput+xml: the SessionInput_ schema encoded in json
         :application/vnd.ez.api.SessionInput+json: the SessionInput_ schema encoded in json
:Response:


.. code:: http

          HTTP/1.1 201 Created
          Location: /user/sessions/<sessionID>
          Content-Type: <depending on accept header>
          Content-Length: <length>
          Set-Cookie: <sessionName> : <sessionID>  A unique session id
.. parsed-literal::
          Session_


:Error codes:
    :400: If the Input does not match the input schema definition, In this case the response contains an ErrorMessage_
    :401: If the authorization failed
    :303: If header contained a session cookie and same user was authorized, like 201 Created it will include a Location header
    :409: If header contained a session cookie but different user was authorized


XML Example
'''''''''''

.. code:: http

    POST /user/sessions HTTP/1.1
    Host: www.example.net
    Accept: application/vnd.ez.api.Session+xml
    Content-Type: application/vnd.ez.api.SessionInput+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <SessionInput>
      <login>admin</login>
      <password>secret</password>
    </SessionInput>

.. code:: http

    HTTP/1.1 201 Created
    Location: /user/sessions/go327ij2cirpo59pb6rrv2a4el2
    Set-Cookie: eZSSID : go327ij2cirpo59pb6rrv2a4el2; Domain=.example.net; Path=/; Expires=Wed, 13-Jan-2021 22:23:01 GMT; HttpOnly
    Content-Type: application/vnd.ez.api.Session+xml
    Content-Length: xxx

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <Session href="/user/sessions/sessionID" media-type="application/vnd.ez.api.Session+xml">
      <name>eZSSID</name>
      <identifier>go327ij2cirpo59pb6rrv2a4el2</identifier>
      <csrfToken>23lkneri34ijajedfw39orj3j93</csrfToken>
      <User href="/user/users/14" media-type="vnd.ez.api.User+xml"/>
    </Session>


JSON Example
''''''''''''

.. code:: http

    POST /user/sessions HTTP/1.1
    Host: www.example.net
    Accept: application/vnd.ez.api.Session+json
    Content-Type: application/vnd.ez.api.SessionInput+xml
    Content-Length: xxx

.. code:: json

    {
      "SessionInput": {
        "login": "admin",
        "password": "secret"
      }
    }

.. code:: http

    HTTP/1.1 201 Created
    Location: /user/sessions/go327ij2cirpo59pb6rrv2a4el2
    Set-Cookie: eZSSID : go327ij2cirpo59pb6rrv2a4el2; Domain=.example.net; Path=/; Expires=Wed, 13-Jan-2021 22:23:01 GMT; HttpOnly
    Content-Type: application/vnd.ez.api.Session+json
    Content-Length: xxx

.. code:: json

    {
      "Session": {
        "name": "eZSSID",
        "identifier": "go327ij2cirpo59pb6rrv2a4el2",
        "csrfToken": "23lkneri34ijajedfw39orj3j93",
        "User": {
          "_href": "/user/users/14",
          "_media-type": "application/vnd.ez.api.User+json"
        }
      }
    }


Delete session (logout a User):
```````````````````````````````

:Resource: /user/sessions/<sessionID>
:Method: DELETE
:Description: The user session is removed i.e. the user is logged out.
:Headers:
    :Cookie:
        <sessionName> : <sessionID>
    :X-CSRF-Token:
        <csrfToken> The <csrfToken> needed on all unsafe http methods with session.
:Response: 204
:Error Codes:
    :404: If the session does not exist


Example
'''''''

.. code:: http

    DELETE /user/sessions/go327ij2cirpo59pb6rrv2a4el2 HTTP/1.1
    Host: www.example.net
    Cookie: eZSSID : go327ij2cirpo59pb6rrv2a4el2
    X-CSRF-Token: 23lkneri34ijajedfw39orj3j93

.. code:: http

    HTTP/1.1 204 No Content
    Set-Cookie: eZSSID=deleted; Expires=Thu, 01-Jan-1970 00:00:01 GMT; Path=/; Domain=.example.net; HttpOnly



.. _InputOutput:

Input Output Specification
==========================

.. _Common:

Common Definitions
------------------

Common definition which are used from multiple schema definitions

.. code:: xml

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

      <xsd:complexType name="refValueList">
        <xsd:sequence>
          <xsd:element name="ref" type="ref" maxOccurs="unbounded" />
        </xsd:sequence>
      </xsd:complexType>

      <xsd:complexType name="controllerRef">
        <xsd:annotation>
          <xsd:documentation>
            A base schema for referencing controllers and
            methods
          </xsd:documentation>
        </xsd:annotation>
        <xsd:attribute name="href" type="xsd:string" />
        <xsd:attribute name="method" type="xsd:string" />
      </xsd:complexType>

      <xsd:complexType name="valueType" mixed="true">
        <xsd:sequence>
          <xsd:element name="value" type="valueType" minOccurs="0" maxOccurs="unbounded"/>
        </xsd:sequence>
        <xsd:attribute name="key" type="xsd:string" />
      </xsd:complexType>

      <xsd:complexType name="fieldValueType" mixed="true">
        <xsd:sequence>
          <xsd:element name="value" type="valueType" minOccurs="0" maxOccurs="unbounded"></xsd:element>
        </xsd:sequence>
      </xsd:complexType>

      <xsd:complexType name="fieldInputValueType">
        <xsd:annotation>
          <xsd:documentation>
            Schema for field inputs in content create and
            update structures
          </xsd:documentation>
        </xsd:annotation>
        <xsd:all>
          <xsd:element name="fieldDefinitionIdentifier" type="xsd:string" />
          <xsd:element name="languageCode" type="xsd:string" />
          <xsd:element name="fieldValue" type="fieldValueType" />
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

.. _Root:

Root Resources
--------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.Root">
        <xsd:all>
          <xsd:element name="content" type="ref" />
          <xsd:element name="contentTypes" type="ref" />
          <xsd:element name="users" type="ref"/>
          <xsd:element name="roles" type="ref"/>
          <xsd:element name="rootLocation" type="ref"/>
          <xsd:element name="rootUserGroup" type="ref"/>
          <xsd:element name="rootMediaFolder" type="ref"/>
          <xsd:element name="trash" type="ref"/>
          <xsd:element name="sections" type="ref"/>
          <xsd:element name="views" type="ref"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="Root" type="vnd.ez.api.Root"/>
    </xsd:schema>

.. _Content:

Content XML Schema
------------------

.. code:: xml

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
      <xsd:complexType name="contentBaseType">
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
      <xsd:complexType name="vnd.ez.api.ContentInfo">
        <xsd:complexContent>
          <xsd:extension base="contentBaseType">
            <xsd:all>
              <xsd:element name="CurrentVersion" type="ref" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.Content">
        <xsd:complexContent>
          <xsd:extension base="contentBaseType">
            <xsd:all>
              <xsd:element name="CurrentVersion" type="embeddedVersionType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="ContentInfo" type="vnd.ez.api.ContentInfo" />
      <xsd:element name="Content" type="vnd.ez.api.Content" />
    </xsd:schema>


.. _Relation:
.. _RelationList:
.. _RelationCreate:

Relation XML Schema
-------------------

.. code:: xml

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

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
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
          <xsd:element name="names" type="multiLanguageValuesType"/>
          <xsd:element name="Content" type="ref" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="VersionInfo" type="versionInfoType"/>
    </xsd:schema>



Version
~~~~~~~

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="VersionInfo.xsd" />
      <xsd:include schemaLocation="Relation.xsd" />
      <xsd:complexType name="fieldOutputValueType">
        <xsd:all>
          <xsd:element name="id" type="xsd:integer" />
          <xsd:element name="fieldDefinitionIdentifier" type="xsd:string" />
          <xsd:element name="languageCode" type="xsd:string" />
          <xsd:element name="value" type="fieldValueType" />
        </xsd:all>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.Version">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="VersionInfo" type="versionInfoType" />
              <xsd:element name="Fields" minOccurs="0">
                <xsd:complexType>
                  <xsd:sequence>
                    <xsd:element name="field" type="fieldOutputValueType"
                      minOccurs="1" maxOccurs="unbounded" />
                  </xsd:sequence>
                </xsd:complexType>
              </xsd:element>
              <xsd:element name="Relations" minOccurs="0">
                <xsd:complexType>
                  <xsd:complexContent>
                    <xsd:extension base="ref">
                      <xsd:sequence>
                        <xsd:element name="Relation" type="vnd.ez.api.Relation"
                          minOccurs="0" maxOccurs="unbounded" />
                      </xsd:sequence>
                    </xsd:extension>
                  </xsd:complexContent>
                </xsd:complexType>
              </xsd:element>
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Version" type="vnd.ez.api.Version"></xsd:element>
    </xsd:schema>

.. _VersionList:

VersionList XML Schema
----------------------

.. code:: xml


    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="Version.xsd" />
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="versionListItemType">
        <xsd:all>
          <xsd:element name="Version" type="ref"></xsd:element>
          <xsd:element name="VersionInfo" type="versionInfoType"></xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.VersionList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="VersionItem" type="versionListItemType"/>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="VersionList" type="vnd.ez.api.VersionList"></xsd:element>
    </xsd:schema>


.. _VersionUpdate:

VersionUpdate XML Schema
------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="versionUpdateType">
        <xsd:all>
          <xsd:element name="User" type="ref" minOccurs="0" />
          <xsd:element name="modificationDate" type="xsd:dateTime"
          <xsd:element name="initialLanguageCode" type="xsd:string"
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

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
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

.. code:: xml

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


.. _View:

View XML Schema
---------------


.. code:: xml

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

      <xsd:complexType name="facetBuilderType">
        <xsd:sequence>
          <xsd:element name="criterion" type="criterionType"
            minOccurs="0" maxOccurs="1" />
        </xsd:sequence>
        <xsd:attribute name="name" type="xsd:string" />
        <xsd:attribute name="global" type="xsd:boolean" />
        <xsd:attribute name="limit" type="xsd:int" />
        <xsd:attribute name="minCount" type="xsd:int" />
      </xsd:complexType>


      <xsd:simpleType name="dateRangeFacetSelector">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="CREATED" />
          <xsd:enumeration value="MODIFIED" />
          <xsd:enumeration value="PUBLISHED" />
        </xsd:restriction>
      </xsd:simpleType>


      <xsd:complexType name="dateRangeType">
        <xsd:attribute name="from" type="xsd:dateTime" />
        <xsd:attribute name="to" type="xsd:dateTime" />
      </xsd:complexType>

      <xsd:complexType name="dateRangeFacetBuilderType">
        <xsd:complexContent>
          <xsd:extension base="facetBuilderType">
            <xsd:sequence>
              <xsd:element name="unboundedFrom" type="xsd:dateTime"
                minOccurs="0" maxOccurs="1" />
              <xsd:element name="range" type="dateRangeType"
                minOccurs="0" maxOccurs="unbounded" />
              <xsd:element name="unboundedTo" type="xsd:dateTime"
                minOccurs="0" maxOccurs="1" />
            </xsd:sequence>
            <xsd:attribute name="select" type="dateRangeFacetSelector"
              default="PUBLISHED" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:simpleType name="fieldFacetSortType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="TERM_ASC" />
          <xsd:enumeration value="TERM_DESC" />
          <xsd:enumeration value="COUNT_ASC" />
          <xsd:enumeration value="COUNT_DESC" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:complexType name="fieldFacetBuilderType">
        <xsd:complexContent>
          <xsd:extension base="facetBuilderType">
            <xsd:sequence>
              <xsd:element name="fieldPath" type="xsd:string"
                minOccurs="1" maxOccurs="unbounded" />
              <xsd:element name="regExpFilter" type="xsd:string"
                minOccurs="0" maxOccurs="1" />
            </xsd:sequence>
            <xsd:attribute name="sort" type="fieldFacetSortType" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="fieldRangeType">
        <xsd:attribute name="from" type="xsd:anySimpleType" />
        <xsd:attribute name="to" type="xsd:anySimpleType" />
      </xsd:complexType>

      <xsd:complexType name="fieldRangeFacetBuilderType">
        <xsd:complexContent>
          <xsd:extension base="facetBuilderType">
            <xsd:sequence>
              <xsd:element name="unboundedFrom" type="xsd:anySimpleType"
                minOccurs="0" maxOccurs="1" />
              <xsd:element name="range" type="fieldRangeType"
                minOccurs="0" maxOccurs="unbounded" />
              <xsd:element name="unboundedTo" type="xsd:anySimpleType"
                minOccurs="0" maxOccurs="1" />
            </xsd:sequence>
            <xsd:attribute name="fieldPath" type="xsd:string" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="locationFacetBuilderType">
        <xsd:complexContent>
          <xsd:extension base="facetBuilderType">
            <xsd:sequence>
              <xsd:element name="location" type="ref" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:simpleType name="userFacetSelector">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="OWNER" />
          <xsd:enumeration value="CREATTOR" />
          <xsd:enumeration value="MODIFIER" />
          <xsd:enumeration value="GROUP" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:complexType name="userFacetBuilderType">
        <xsd:complexContent>
          <xsd:extension base="facetBuilderType">
            <xsd:attribute name="select" type="userFacetSelector"
              default="OWNER" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="facetBuilderListType">
        <xsd:sequence>
          <xsd:choice>
            <xsd:element name="contentTypeFacetBuilder" type="facetBuilderType" />
            <xsd:element name="criterionFacetBuilder" type="facetBuilderType" />
            <xsd:element name="dateRangeFacetBulder" type="dateRangeFacetBuilderType" />
            <xsd:element name="fieldFacetBuilder" type="fieldFacetBuilderType"></xsd:element>
            <xsd:element name="fieldRangeFacetBuilder" type="fieldRangeFacetBuilderType"></xsd:element>
            <xsd:element name="locationFacetBuilder" type="locationFacetBuilderType" />
            <xsd:element name="sectionFacetBuilder" type="facetBuilderType" />
            <xsd:element name="termFacetBuilder" type="facetBuilderType" />
            <xsd:element name="userFacetBuilder" type="userFacetBuilderType" />
          </xsd:choice>
        </xsd:sequence>
      </xsd:complexType>

      <xsd:complexType name="queryType">
        <xsd:all>
          <xsd:element name="Criterion" type="criterionType" />
          <xsd:element name="limit" type="xsd:int" />
          <xsd:element name="offset" type="xsd:int" />
          <xsd:element name="FacetBuilders" type="facetBuilderListType" />
          <xsd:element name="SortClauses" type="sortClauseType" />
          <xsd:element name="spellcheck" type="xsd:boolean" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="facetType">
        <xsd:attribute name="name" type="xsd:string" />
      </xsd:complexType>

      <xsd:complexType name="contentTypeFacetEntryType">
        <xsd:all>
          <xsd:element name="contentType" type="ref" />
          <xsd:element name="count" type="xsd:int" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="contentTypeFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="contentTypeFacetEntry" type="contentTypeFacetEntryType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="sectionFacetEntryType">
        <xsd:all>
          <xsd:element name="section" type="ref" />
          <xsd:element name="count" type="xsd:int" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="sectionFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="sectionFacetEntry" type="sectionFacetEntryType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="locationFacetEntryType">
        <xsd:all>
          <xsd:element name="location" type="ref" />
          <xsd:element name="count" type="xsd:int" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="locationFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="locationFacetEntry" type="locationFacetEntryType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="userFacetEntryType">
        <xsd:all>
          <xsd:element name="user" type="ref" />
          <xsd:element name="count" type="xsd:int" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="userFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="userFacetEntry" type="userFacetEntryType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>


      <xsd:complexType name="criterionFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:all>
              <xsd:element name="criterion" type="criterionType" />
              <xsd:element name="count" type="xsd:int" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="rangeFacetEntryType">
        <xsd:all>
          <xsd:element name="from" type="xsd:anySimpleType" />
          <xsd:element name="to" type="xsd:anySimpleType" />
          <xsd:element name="totalCount" type="xsd:int" />
          <xsd:element name="min" type="xsd:int" />
          <xsd:element name="max" type="xsd:int" />
          <xsd:element name="mean" type="xsd:int" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="dateRangeFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="dateRangeFacetEntry" type="rangeFacetEntryType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="fieldRangeFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="fieldRangeFacetEntry" type="rangeFacetEntryType" />
            </xsd:sequence>
            <xsd:attribute name="totalCount" type="xsd:int" />
            <xsd:attribute name="otherCount" type="xsd:int" />
            <xsd:attribute name="missingCount" type="xsd:int" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="termFacetEntryType">
        <xsd:all>
          <xsd:element name="term" type="xsd:string" />
          <xsd:element name="count" type="xsd:int" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="fieldFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="fieldFacetEntry" type="termFacetEntryType" />
            </xsd:sequence>
            <xsd:attribute name="totalCount" type="xsd:int" />
            <xsd:attribute name="otherCount" type="xsd:int" />
            <xsd:attribute name="missingCount" type="xsd:int" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="termFacetType">
        <xsd:complexContent>
          <xsd:extension base="facetType">
            <xsd:sequence>
              <xsd:element name="termFacetEntry" type="termFacetEntryType" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="facetTyoe">
        <xsd:choice>
          <xsd:element name="sectionFacet" type="sectionFacetType" />
          <xsd:element name="locationFacet" type="locationFacetType" />
          <xsd:element name="userFacet" type="userFacetType" />
          <xsd:element name="contentTypeFacet" type="contentTypeFacetType" />
          <xsd:element name="fieldFacet" type="fieldFacetType" />
          <xsd:element name="fieldRangeFacet" type="fieldRangeFacetType" />
          <xsd:element name="dateRangeFacet" type="dateRangeFacetType" />
          <xsd:element name="criterionFacet" type="criterionFacetType" />
          <xsd:element name="termFacet" type="termFacetType" />
        </xsd:choice>
      </xsd:complexType>

      <xsd:complexType name="searchHitType">
        <xsd:all>
          <xsd:element name="value" type="xsd:anyType" />
          <xsd:element name="hightlight" type="xsd:string" />
        </xsd:all>
        <xsd:attribute name="score" type="xsd:float" />
        <xsd:attribute name="index" type="xsd:string" />
      </xsd:complexType>

      <xsd:complexType name="searchHitListType">
        <xsd:sequence>
          <xsd:element name="searchHit" type="searchHitType" />
        </xsd:sequence>
      </xsd:complexType>

      <xsd:complexType name="facetListType">
        <xsd:sequence>
          <xsd:element name="facet" type="facetType" />
        </xsd:sequence>
      </xsd:complexType>

      <xsd:complexType name="resultType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="searchHits" type="searchHitListType" />
              <xsd:element name="facets" type="facetListType" />
              <xsd:element name="spellcorrection" type="criterionType" />
            </xsd:all>
            <xsd:attribute name="count" type="xsd:int" />
            <xsd:attribute name="time" type="xsd:int" />
            <xsd:attribute name="timedOut" type="xsd:boolean" />
            <xsd:attribute name="maxScore" type="xsd:float" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="viewInputType">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="public" type="xsd:boolean" default="false" />
          <xsd:element name="Query" type="queryType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="viewType">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="identifier" type="xsd:string" />
              <xsd:element name="User" type="ref" />
              <xsd:element name="public" type="xsd:boolean" />
              <xsd:element name="Query" type="queryType" />
              <xsd:element name="Result" type="resultType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="ViewInput" type="viewInputType" />
      <xsd:element name="FacetBuilder" type="facetBuilderListType" />
      <xsd:element name="View" type="viewType" />
    </xsd:schema>

.. _LocationCreate:

LocationCreate XML Schema
-------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.LocationCreate">
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
      <xsd:element name="LocationCreate" type="vnd.ez.api.LocationCreate" />
    </xsd:schema>



.. _LocationUpdate:

LocationUpdate XML Schema
-------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.LocationUpdate">
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
      <xsd:element name="LocationUpdate" type="vnd.ez.api.LocationUpdate" />
    </xsd:schema>




.. _Location:

Location XML Schema
-------------------


.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.Location">
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
              <xsd:element name="Children" type="ref" />
              <xsd:element name="Content" type="ref" />
              <xsd:element name="sortField" type="sortFieldType" />
              <xsd:element name="sortOrder" type="sortOrderType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.LocationList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Location" type="ref" minOccurs="0"
                maxOccurs="unbounded"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Location" type="vnd.ez.api.Location" />
      <xsd:element name="LocationList" type="vnd.ez.api.LocationList" />
    </xsd:schema>


.. _Trash:

Trash XML Schema
----------------


.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="Location.xsd" />

      <xsd:complexType name="vnd.ez.api.TrashItem">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:string">
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

      <xsd:complexType name="vnd.ez.api.Trash">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="trashItem" type="vnd.ez.api.TrashItem"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="trash" type="vnd.ez.api.Trash" />
    </xsd:schema>





.. _UrlAlias:

UrlAlias XML Schema
-------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:simpleType name="urlAliasType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="LOCATION" />
          <xsd:enumeration value="RESOURCE" />
          <xsd:enumeration value="VIRTUAL" />
        </xsd:restriction>
      </xsd:simpleType>
      <xsd:complexType name="vnd.ez.api.UrlAlias">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:choice>
                <xsd:element name="location" type="ref" />
                <xsd:element name="resource" type="xsd:string" />
              </xsd:choice>
              <xsd:element name="path" type="xsd:string" />
              <xsd:element name="languageCodes" type="xsd:string" />
              <xsd:element name="alwaysAvailable" type="xsd:boolean" />
              <xsd:element name="isHistory" type="xsd:boolean" />
              <xsd:element name="forward" type="xsd:boolean" />
              <xsd:element name="custom" type="xsd:boolean" />
            </xsd:sequence>
            <xsd:attribute name="id" type="xsd:string" />
            <xsd:attribute name="type" type="urlAliasType" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UrlAliasList">
        <xsd:sequence>
          <xsd:element name="UrlAlias" type="vnd.ez.api.UrlAlias"
            minOccurs="0" maxOccurs="unbounded" />
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UrlAliasRefList">
        <xsd:sequence>
          <xsd:element name="UrlAlias" type="ref"
            minOccurs="0" maxOccurs="unbounded" />
        </xsd:sequence>
      </xsd:complexType>
       <xsd:complexType name="vnd.ez.api.UrlAliasCreate">
        <xsd:sequence>
          <xsd:choice>
            <xsd:element name="location" type="ref" />
            <xsd:element name="resource" type="xsd:string" />
          </xsd:choice>
          <xsd:element name="resource" type="xsd:string" />
          <xsd:element name="path" type="xsd:string" />
          <xsd:element name="languageCode" type="xsd:string" />
          <xsd:element name="alwaysAvailable" type="xsd:boolean"
            default="false" />
          <xsd:element name="forward" type="xsd:boolean"
            default="false" />
        </xsd:sequence>
        <xsd:attribute name="type" type="urlAliasType" />
      </xsd:complexType>
    </xsd:schema>


.. _UrlWildcard:

UrlWildcard XML Schema
----------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.UrlWildcard">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="sourceUrl" type="xsd:string" />
              <xsd:element name="destinationUrl" type="xsd:string" />
              <xsd:element name="forward" type="xsd:boolean" />
            </xsd:all>
            <xsd:attribute name="id" type="xsd:string" />
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UrlWildcardList">
        <xsd:sequence>
          <xsd:element name="UrlWildcard" type="vnd.ez.api.UrlWildcard"
            minOccurs="0" maxOccurs="unbounded" />
        </xsd:sequence>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UrlWildcardCreate">
        <xsd:all>
          <xsd:element name="sourceUrl" type="xsd:string" />
          <xsd:element name="destinationUrl" type="xsd:string" />
          <xsd:element name="forward" type="xsd:boolean" />
        </xsd:all>
      </xsd:complexType>
    </xsd:schema>


.. _Section:

Section XML Schema
------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">

      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.Section">
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
      <xsd:complexType name="vnd.ez.api.SectionList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Section" type="vnd.ez.api.Section" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.SectionInput">
        <xsd:all>
            <xsd:element name="identifier" type="xsd:string" minOccurs="0"/>
            <xsd:element name="name" type="xsd:string" minOccurs="0"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="Section" type="vnd.ez.api.Section"></xsd:element>
      <xsd:element name="SectionList" type="vnd.ez.api.SectionList"></xsd:element>
      <xsd:element name="SectionInput" type="vnd.ez.api.SectionInput"></xsd:element>
    </xsd:schema>




.. _Session:

Session XML Schema
------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.Session">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="name" type="xsd:int"/>
              <xsd:element name="identifier" type="xsd:string"/>
              <xsd:element name="csrfToken" type="xsd:string"/>
              <xsd:element name="User" type="ref" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Session" type="vnd.ez.api.Session"></xsd:element>
    </xsd:schema>


.. _SessionInput:

SessionInput XML Schema
-----------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:complexType name="vnd.ez.api.SessionInput">
        <xsd:complexContent>
          <xsd:all>
            <xsd:element name="login" type="xsd:string"/>
            <xsd:element name="password" type="xsd:string" />
          </xsd:all>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="SessionInput" type="vnd.ez.api.SessionInput"></xsd:element>
    </xsd:schema>


.. _ObjectStateGroup:

ObjectStateGroup XML Schema
---------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.ObjectStateGroup">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:int">
              </xsd:element>
              <xsd:element name="identifier" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Readable string identifier of a group
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="defaultLanguageCode" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    the default language code
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="languageCodes" type="xsd:string"
                minOccurs="0" maxOccurs="1">
                <xsd:annotation>
                  <xsd:documentation>
                    Comma separated List of language codes
                    present in names and descriptions
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="names" type="multiLanguageValuesType" />
              <xsd:element name="descriptions" type="multiLanguageValuesType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ObjectStateGroupList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="ObjectStateGroup" type="vnd.ez.api.ObjectStateGroup"
                maxOccurs="unbounded"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ObjectStateGroupCreate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                Readable string identifier of a group
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultLanguageCode" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                the default language code
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType" />
          <xsd:element name="descriptions" type="multiLanguageValuesType" minOccurs="0"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ObjectStateGroupUpdate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Readable string identifier of a group
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultLanguageCode" type="xsd:string" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                the default language code
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType" minOccurs="0"/>
          <xsd:element name="descriptions" type="multiLanguageValuesType" minOccurs="0" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ObjectStateGroupCreate" type="vnd.ez.api.ObjectStateGroupCreate" />
      <xsd:element name="ObjectStateGroupUpdate" type="vnd.ez.api.ObjectStateGroupUpdate" />
      <xsd:element name="ObjectStateGroup" type="vnd.ez.api.ObjectStateGroup" />
      <xsd:element name="ObjectStateGroupList" type="vnd.ez.api.ObjectStateGroupList" />
    </xsd:schema>

.. _ObjectState:

ObjectStates XML Schema
-----------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.ObjectState">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:int">
              </xsd:element>
              <xsd:element name="identifier" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Readable string identifier of an object state
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="priority" type="xsd:int">
              </xsd:element>
              <xsd:element name="ObjectStateGroup" type="ref">
              </xsd:element>
              <xsd:element name="defaultLanguageCode" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    the default language code
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="languageCodes" type="xsd:string"
                minOccurs="0" maxOccurs="1">
                <xsd:annotation>
                  <xsd:documentation>
                    Comma separated List of language codes
                    present in names and descriptions
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="names" type="multiLanguageValuesType" />
              <xsd:element name="descriptions" type="multiLanguageValuesType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ObjectStateList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="ObjectState" type="vnd.ez.api.ObjectState"
                maxOccurs="unbounded"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ObjectStateCreate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                Readable string identifier of an object state
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="priority" type="xsd:int">
          </xsd:element>
          <xsd:element name="defaultLanguageCode" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                the default language code
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType" />
          <xsd:element name="descriptions" type="multiLanguageValuesType" />
        </xsd:all>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ObjectStateUpdate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Readable string identifier of an object state
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="priority" type="xsd:int"  minOccurs="0">
          </xsd:element>
          <xsd:element name="defaultLanguageCode" type="xsd:string" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                the default language code
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType"  minOccurs="0"/>
          <xsd:element name="descriptions" type="multiLanguageValuesType"  minOccurs="0"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ObjectStateCreate" type="vnd.ez.api.ObjectStateCreate" />
      <xsd:element name="ObjectStateUpdate" type="vnd.ez.api.ObjectStateUpdate" />
      <xsd:element name="ObjectState" type="vnd.ez.api.ObjectState" />
      <xsd:element name="ObjectStateList" type="vnd.ez.api.ObjectStateList" />
    </xsd:schema>

.. _ContentObjectStates:

ContentObjectStates XML Schema
------------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.ContentObjectStates">
        <xsd:sequence>
          <xsd:element name="ObjectState" type="ref" minOccurs="0" maxOccurs="unbounded"></xsd:element>
        </xsd:sequence>
      </xsd:complexType>
    </xsd:schema>



.. _ContentTypeGroup:
.. _ContentTypeGroupInput:

ContentTypeGroup XML Schema
---------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="ContentType.xsd" />
      <xsd:complexType name="vnd.ez.api.ContentTypeGroup">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:int">
              </xsd:element>
              <xsd:element name="identifier" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Readable string identifier of a group
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="created" type="xsd:dateTime">
                <xsd:annotation>
                  <xsd:documentation>
                    Created date
                              </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="modified" type="xsd:dateTime">
                <xsd:annotation>
                  <xsd:documentation>
                    Modified date
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="Creator" type="ref">
                <xsd:annotation>
                  <xsd:documentation>
                    Creator user
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="Modifier" type="ref">
                <xsd:annotation>
                  <xsd:documentation>
                    Modifier user
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="ContentTypes" type="vnd.ez.api.ContentTypeInfoList" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentTypeGroupList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="ContentTypeGroup" type="vnd.ez.api.ContentTypeGroup"
                maxOccurs="unbounded"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentTypeGroupRefList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="ContentTypeGroupRef" maxOccurs="unbounded">
                <xsd:complexType>
                  <xsd:complexContent>
                    <xsd:extension base="ref">
                      <xsd:all>
                        <xsd:element name="unlink" type="controllerRef"
                          minOccurs="0" />
                      </xsd:all>
                    </xsd:extension>
                  </xsd:complexContent>
                </xsd:complexType>
              </xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentTypeGroupInput">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string" />
          <xsd:element name="User" type="ref" minOccurs="0" />
          <xsd:element name="modificationDate" type="xsd:dateTime"
            minOccurs="0" />
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ContentTypeGroupInput" type="vnd.ez.api.ContentTypeGroupInput" />
      <xsd:element name="ContentTypeGroup" type="vnd.ez.api.ContentTypeGroup" />
      <xsd:element name="ContentTypeGroupList" type="vnd.ez.api.ContentTypeGroupList" />
      <xsd:element name="ContentTypeGroupRefList" type="vnd.ez.api.ContentTypeGroupRefList" />
    </xsd:schema>




.. _ContentType:

ContentType XML Schema
----------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="FieldDefinition.xsd" />

      <xsd:complexType name="vnd.ez.api.ContentTypeInfo">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="id" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    Content type ID
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="status" type="contentTypeStatus">
                <xsd:annotation>
                  <xsd:documentation>
                    The status of the content type.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="identifier" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    String identifier of a content type
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="names" type="multiLanguageValuesType" />
              <xsd:element name="descriptions" type="multiLanguageValuesType" />
              <xsd:element name="creationDate" type="xsd:dateTime">
                <xsd:annotation>
                  <xsd:documentation>
                    Creation date of the content type
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="modificationDate" type="xsd:dateTime">
                <xsd:annotation>
                  <xsd:documentation>
                    Last modification date of the content
                    type
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="Creator" type="ref">
                <xsd:annotation>
                  <xsd:documentation>
                    The user which created the content type
                  </xsd:documentation>
                </xsd:annotation>

              </xsd:element>
              <xsd:element name="Modifier" type="ref">
                <xsd:annotation>
                  <xsd:documentation>
                    The userwhich last modified the content
                    type
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="remoteId" type="xsd:string"
                minOccurs="0">
                <xsd:annotation>
                  <xsd:documentation>
                    Unique remote ID of the content type
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="urlAliasSchema" type="xsd:string"
                minOccurs="0">
                <xsd:annotation>
                  <xsd:documentation>
                    URL alias schema
                    If nothing is provided,
                    nameSchema will be used
                    instead.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="nameSchema" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Name schema.
                    Can be composed of
                    FieldDefinition
                    identifier place
                    holders.These place
                    holders must comply this
                    pattern :
                    &lt;field_definition_identifier&gt;.
                    An OR condition can
                    be used :
                    &lt;field_def|other_field_def&gt;
                    In this
                    example, field_def will be used if available. If not,
                    other_field_def will be used for content name generation
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="isContainer" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    Determines if the type is a container
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="mainLanguageCode" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Main language
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="defaultAlwaysAvailable" type="xsd:boolean"
                default="true">
                <xsd:annotation>
                  <xsd:documentation>
                    if an instance of acontent type is
                    created the always available
                    flag is set by default this
                    this value.
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="defaultSortField" type="sortFieldType">
                <xsd:annotation>
                  <xsd:documentation>
                    Specifies which property the child
                    locations should be sorted on by
                    default when created
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="defaultSortOrder" type="sortOrderType">
                <xsd:annotation>
                  <xsd:documentation>
                    Specifies whether the sort order should
                    be ascending or descending by
                    default when created
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentTypeInfoList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="ContentTypeInfo" type="vnd.ez.api.ContentTypeInfo"
                maxOccurs="unbounded" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentType">
        <xsd:complexContent>
          <xsd:extension base="vnd.ez.api.ContentTypeInfo">
            <xsd:sequence>
              <xsd:element name="FieldDefinitions" type="vnd.ez.api.FieldDefinitionList" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.ContentTypeList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="ContentType" type="vnd.ez.api.ContentType" maxOccurs="unbounded"/>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:element name="ContentTypeInfo" type="vnd.ez.api.ContentTypeInfo"></xsd:element>
      <xsd:element name="ContentTypeInfoList" type="vnd.ez.api.ContentTypeInfoList"></xsd:element>
      <xsd:element name="ContentType" type="vnd.ez.api.ContentType"></xsd:element>
      <xsd:element name="ContentTypeList" type="vnd.ez.api.ContentTypeList"></xsd:element>

    </xsd:schema>


.. _ContentTypeCreate:

ContentTypeCreate XML Schema
----------------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="FieldDefinitionCreate.xsd" />

      <xsd:complexType name="vnd.ez.api.ContentTypeCreate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string"
            minOccurs="0" maxOccurs="1">
            <xsd:annotation>
              <xsd:documentation>
                String identifier of a content type
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType" />
          <xsd:element name="descriptions" type="multiLanguageValuesType" />
          <xsd:element name="modificationDate" type="xsd:dateTime">
            <xsd:annotation>
              <xsd:documentation>
                If set this date is used as modification
                date
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="User" type="ref">
            <xsd:annotation>
              <xsd:documentation>
                The user under which this creation should
                be done
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Unique remote ID of the content type
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="urlAliasSchema" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                URL alias schema
                If nothing is provided,
                nameSchema will be used
                instead.
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="nameSchema" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                Name schema.
                Can be composed of
                FieldDefinition identifier place
                holders.These place
                holders
                must comply this pattern :
                &lt;field_definition_identifier&gt;.
                An OR condition can
                be
                used :
                &lt;field_def|other_field_def&gt;
                In this
                example,
                field_def will be used if available. If not,
                other_field_def
                will be used for content name generation
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isContainer" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                Determines if the type is a container
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="mainLanguageCode" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                Main language
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultAlwaysAvailable" type="xsd:boolean"
            default="true">
            <xsd:annotation>
              <xsd:documentation>
                if an instance of acontent type is
                created
                the always available
                flag is set by default this
                this value.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultSortField" type="sortFieldType">
            <xsd:annotation>
              <xsd:documentation>
                Specifies which property the child
                locations should be sorted on by
                default when created
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultSortOrder" type="sortOrderType">
            <xsd:annotation>
              <xsd:documentation>
                Specifies whether the sort order should
                be
                ascending or descending by
                default when created
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="FieldDefinitions">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="FieldDefinition" type="vnd.ez.api.FieldDefinitionCreate"></xsd:element>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ContentTypeCreate" type="vnd.ez.api.ContentTypeCreate"></xsd:element>
    </xsd:schema>

.. _ContentTypeUpdate:

ContentTypeUpdate XML Schema
----------------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.ContentTypeUpdate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                String identifier of a content type
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType"
            minOccurs="0" />
          <xsd:element name="descriptions" type="multiLanguageValuesType"
            minOccurs="0" />
          <xsd:element name="modificationDate" type="xsd:dateTime"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                If set this date is used as modification
                date
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="User" type="ref" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                The user under which this update should be
                done
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Unique remote ID of the content type
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="urlAliasSchema" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                URL alias schema
                If nothing is provided,
                nameSchema will be used
                instead.
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="nameSchema" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Name schema.
                Can be composed of
                FieldDefinition identifier place
                holders.These place
                holders
                must comply this pattern :
                &lt;field_definition_identifier&gt;.
                An OR condition can
                be
                used :
                &lt;field_def|other_field_def&gt;
                In this
                example,
                field_def will be used if available. If not,
                other_field_def
                will be used for content name generation
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isContainer" type="xsd:boolean"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Determines if the type is a container
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="mainLanguageCode" type="xsd:string"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Main language
                  </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultAlwaysAvailable" type="xsd:boolean"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                if an instance of acontent type is
                created
                the always available
                flag is set by default this
                this value.
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultSortField" type="sortFieldType"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Specifies which property the child
                locations should be sorted on by
                default when created
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultSortOrder" type="sortOrderType"
            minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                Specifies whether the sort order should
                be
                ascending or descending by
                default when created
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ContentTypeUpdate" type="vnd.ez.api.ContentTypeUpdate"></xsd:element>
    </xsd:schema>



.. _FieldDefinition:

FieldDefinition XML Schema
--------------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.FieldDefinition">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    the unique id of this field definition
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="identifier" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Readable string identifier of a field
                    definition
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="fieldGroup" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    Field group name
                    </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="position" type="xsd:int">
                <xsd:annotation>
                  <xsd:documentation>
                    the position of the field definition in
                    the content typr
                    </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="fieldType" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    String identifier of the field type
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="isTranslatable" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    If the field type is translatable
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="isRequired" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    Is the field required
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="isInfoCollector" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    the flag if this attribute is used for
                    information collection
                    </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="defaultValue" type="fieldValueType">
                <xsd:annotation>
                  <xsd:documentation>
                    Default value of the field
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="fieldSettings" type="xsd:anyType">
                <xsd:annotation>
                  <xsd:documentation>
                    Settings of the field
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="validatorConfiguration" type="xsd:anyType">
                <xsd:annotation>
                  <xsd:documentation>
                    Validator configuration of the field
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="isSearchable" type="xsd:boolean">
                <xsd:annotation>
                  <xsd:documentation>
                    Indicates if th the content is
                    searchable by this attribute
                    </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="names" type="multiLanguageValuesType" />
              <xsd:element name="descriptions" type="multiLanguageValuesType" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.FieldDefinitionList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="FieldDefinition" type="vnd.ez.api.FieldDefinition"
                minOccurs="1" maxOccurs="unbounded" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="FieldDefinitionList" type="vnd.ez.api.FieldDefinitionList" />
      <xsd:element name="FieldDefinition" type="vnd.ez.api.FieldDefinition" />
    </xsd:schema>


.. _FieldDefinitionCreate:

FieldDefinitionCreate XML Schema
--------------------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>

    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.FieldDefinitionCreate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                Readable string identifier of a field
                definition
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="fieldType" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                the field type for this definition
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="fieldGroup" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                Field group name
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="position" type="xsd:int">
            <xsd:annotation>
              <xsd:documentation>
                the position of the field definition in
                the content typr
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isTranslatable" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                If the field type is translatable
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isRequired" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                Is the field required
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isInfoCollector" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                the flag if this attribute is used for
                information collection
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultValue" type="fieldValueType">
            <xsd:annotation>
              <xsd:documentation>
                Default value of the field
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="fieldSettings" type="xsd:anyType">
            <xsd:annotation>
              <xsd:documentation>
                Settings of the field
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="validatorConfiguration" type="xsd:anyType">
            <xsd:annotation>
              <xsd:documentation>
                Validator configuration of the field
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isSearchable" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                Indicates if th the content is
                searchable by this attribute
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType" />
          <xsd:element name="descriptions" type="multiLanguageValuesType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:element name="FieldDefinitionInput" type="vnd.ez.api.FieldDefinitionCreate" />
    </xsd:schema>


.. _FieldDefinitionUpdate:

FieldDefinitionUpdate XML Schema
--------------------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:complexType name="vnd.ez.api.FieldDefinitionUpdate">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string" minOccurs="0">
            <xsd:annotation>
              <xsd:documentation>
                If set the identifier of a field
                definition is changed
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="fieldGroup" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                If set the field group is changed
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="position" type="xsd:int">
            <xsd:annotation>
              <xsd:documentation>
                If set the the position of the field definition in
                the content typr is changed
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isTranslatable" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                If set the translatable flag is set to this value
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isRequired" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                If set the required flag is set to this value
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isInfoCollector" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                If set the info collection flag is set to this value
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="defaultValue" type="fieldValueType">
            <xsd:annotation>
              <xsd:documentation>
                If set the default value of the field is changed
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="fieldSettings" type="xsd:anyType">
            <xsd:annotation>
              <xsd:documentation>
                If set the settings of the field are changed
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="validatorConfiguration" type="xsd:anyType">
            <xsd:annotation>
              <xsd:documentation>
                If set the validatorConfiguration of the field is changed
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="isSearchable" type="xsd:boolean">
            <xsd:annotation>
              <xsd:documentation>
                If set the searchable flag is set to this value
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
          <xsd:element name="names" type="multiLanguageValuesType" />
          <xsd:element name="descriptions" type="multiLanguageValuesType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:element name="FieldDefinitionInput" type="vnd.ez.api.FieldDefinitionUpdate" />
    </xsd:schema>


.. _UserGroupCreate:

UserGroupCreate XML Schema
--------------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.UserGroupCreate">
        <xsd:all>
          <xsd:element name="ContentType" type="ref" minOccurs="0" />
          <xsd:element name="mainLanguageCode" type="xsd:string" />
          <xsd:element name="Section" type="ref" minOccurs="0"/>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="fields">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="field" type="fieldInputValueType" maxOccurs="unbounded"/>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="UserGroupCreate" type="vnd.ez.api.UserGroupCreate"></xsd:element>
    </xsd:schema>

.. _UserGroupUpdate:

UserGroupUpdate XML Schema
--------------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.UserGroupUpdate">
        <xsd:all>
          <xsd:element name="mainLanguageCode" type="xsd:string" minOccurs="0"/>
          <xsd:element name="Section" type="ref" minOccurs="0"/>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="fields">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="field" type="fieldInputValueType" maxOccurs="unbounded"/>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="UserGroupUpdate" type="vnd.ez.api.UserGroupUpdate"></xsd:element>
    </xsd:schema>

.. _UserGroup:

UserGroup XML Schema
--------------------
.. code:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="Content.xsd" />
      <xsd:include schemaLocation="Version.xsd" />
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.UserGroup">
        <xsd:complexContent>
          <xsd:extension base="contentInfoType">
            <xsd:sequence>
              <xsd:element name="Content" type="vnd.ez.api.Version+xml" />
              <xsd:element name="ParentUserGroup" type="ref" />
              <xsd:element name="Subgroups" type="ref" />
              <xsd:element name="Users" type="ref" />
              <xsd:element name="Roles" type="ref" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UserGroupList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:eleemnt name="User" type="vnd.ez.api.UserGroup"
                maxOccurs="unbounded" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UserGroupRefList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:eleemnt name="UserGroup" minOccurs="1" maxOccurs="unbounded">
                <xsd:complexType>
                  <xsd:all>
                    <xsd:element name="unassign" type="controllerRef" minOccurs="0"/>
                  </xsd:all>
                </xsd:complexType>
              </xsd:eleemnt>
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="UserGroupRefList" type="vnd.ez.api.UserGroupRefList" />
      <xsd:element name="UserGroupList" type="vnd.ez.api.UserGroupList" />
      <xsd:element name="UserGroup" type="vnd.ez.api.UserGroup" />
    </xsd:schema>


.. _UserCreate:

UserCreate XML Schema
---------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.UserCreate">
        <xsd:all>
          <xsd:element name="ContentType" type="ref" minOccurs="0" />
          <xsd:element name="mainLanguageCode" type="xsd:string" />
          <xsd:element name="Section" type="ref" minOccurs="0"/>
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="login" type="xsd:string"/>
          <xsd:element name="email" type="xsd:string" />
          <xsd:element name="enabled" type="xsd:boolean" default="true" minOccurs="0"  />
          <xsd:element name="password" type="xsd:string"/>
          <xsd:element name="fields">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="field" type="fieldInputValueType" maxOccurs="unbounded"/>
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="UserCreate" type="vnd.ez.api.UserCreate"></xsd:element>
    </xsd:schema>


.. _UserUpdate:

UserUpdate XML Schema
---------------------
.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.UserUpdate">
        <xsd:all>
          <xsd:element name="mainLanguageCode" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="Section" type="ref" minOccurs="0" />
          <xsd:element name="remoteId" type="xsd:string"
            minOccurs="0" />
          <xsd:element name="login" type="xsd:string" minOccurs="0" />
          <xsd:element name="email" type="xsd:string" minOccurs="0" />
          <xsd:element name="enabled" type="xsd:boolean" minOccurs="0"  />
          <xsd:element name="password" type="xsd:string" minOccurs="0"  />
          <xsd:element name="fields">
            <xsd:complexType>
              <xsd:sequence>
                <xsd:element name="field" type="fieldInputValueType"
                  maxOccurs="unbounded" />
              </xsd:sequence>
            </xsd:complexType>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="UserUpdate" type="vnd.ez.api.UserUpdate"></xsd:element>
    </xsd:schema>

.. _User:

User XML Schema
---------------
.. code:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="Content.xsd" />
      <xsd:include schemaLocation="Version.xsd" />
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.User">
        <xsd:complexContent>
          <xsd:extension base="contentInfoType">
            <xsd:all>
              <xsd:element name="login" type="xsd:string" />
              <xsd:element name="email" type="xsd:string" />
              <xsd:element name="enabled" type="xsd:boolean" />
              <xsd:element name="Content" type="vnd.ez.api.Version+xml" />
              <xsd:element name="Roles" type="ref" />
              <xsd:element name="UserGroups" type="ref" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UserList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:eleemnt name="User" type="vnd.ez.api.User"
                maxOccurs="unbounded" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:complexType name="vnd.ez.api.UserRefList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:eleemnt name="User" type="ref"
                maxOccurs="unbounded" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="UserRefList" type="vnd.ez.api.UserRefList" />
      <xsd:element name="UserList" type="vnd.ez.api.UserList" />
      <xsd:element name="User" type="vnd.ez.api.User" />
    </xsd:schema>



.. _Limitation:

Limitation XML Schema
---------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:simpleType name="limitationIdentifierType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="Class" />
          <xsd:enumeration value="Language" />
          <xsd:enumeration value="Node" />
          <xsd:enumeration value="Owner" />
          <xsd:enumeration value="ParentOwner" />
          <xsd:enumeration value="ParentClass" />
          <xsd:enumeration value="ParentDepth" />
          <xsd:enumeration value="Section" />
          <xsd:enumeration value="Siteaccess" />
          <xsd:enumeration value="State" />
          <xsd:enumeration value="Subtree" />
          <xsd:enumeration value="Group" />
          <xsd:enumeration value="ParentGroup" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:simpleType name="roleLimitationIdentifierType">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="Section" />
          <xsd:enumeration value="Subtree" />
        </xsd:restriction>
      </xsd:simpleType>

      <xsd:complexType name="roleLimitationType">
        <xsd:all>
          <xsd:element name="values" type="stringList" />
        </xsd:all>
        <xsd:attribute name="identifier" type="roleLimitationIdentifierType" />
      </xsd:complexType>

      <xsd:complexType name="limitationType">
        <xsd:choice>
          <xsd:element name="values" type="valueType"</xsd:element>
          <xsd:element name="refs" type="refValueList" />
        </xsd:choice>
        <xsd:attribute name="identifier" type="limitationIdentifierType" />
      </xsd:complexType>

      <xsd:complexType name="limitationListType">
        <xsd:sequence>
          <xsd:element name="limitation" type="limitationType"
            maxOccurs="unbounded" />
        </xsd:sequence>
      </xsd:complexType>

    </xsd:schema>


.. _Policy:
.. _PolicyCreate:
.. _PolicyUpdate:

Policy XML Schema
-----------------

.. code:: xml


    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="Limitation.xsd" />

      <xsd:complexType name="vnd.ez.api.Policy">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="id" type="xsd:string" />
              <xsd:element name="module" type="xsd:string" />
              <xsd:element name="function" type="xsd:string" />
              <xsd:element name="limitations" type="limitationListType"></xsd:element>
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.PolityCreate">
        <xsd:all>
          <xsd:element name="module" type="xsd:string" />
          <xsd:element name="function" type="xsd:string" />
          <xsd:element name="limitations" type="limitationListType"></xsd:element>
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.PolityUpdate">
        <xsd:all>
          <xsd:element name="limitations" type="limitationListType"></xsd:element>
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.PolicyList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Policy" type="vnd.ez.api.Policy"
                maxOccurs="unbounded" />
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>
      <xsd:element name="Policy" type="vnd.ez.api.Policy"/>
      <xsd:element name="PolicyList" type="vnd.ez.api.PolicyList"/>
      <xsd:element name="PolicyCreate" type="vnd.ez.api.PolityCreate"/>
      <xsd:element name="PolicyUpdate" type="vnd.ez.api.PolityUpdate"/>
    </xsd:schema>


.. _Role:
.. _RoleAssignInput:
.. _RoleInput:

Role XML Schema
---------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />
      <xsd:include schemaLocation="Limitation.xsd" />

      <xsd:complexType name="vnd.ez.api.RoleInput">
        <xsd:all>
          <xsd:element name="identifier" type="xsd:string">
            <xsd:annotation>
              <xsd:documentation>
                String identifier of the role
              </xsd:documentation>
            </xsd:annotation>
          </xsd:element>
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.Role">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="identifier" type="xsd:string">
                <xsd:annotation>
                  <xsd:documentation>
                    String identifier of the role
                  </xsd:documentation>
                </xsd:annotation>
              </xsd:element>
              <xsd:element name="Policies" type="ref" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.RoleList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:sequence>
              <xsd:element name="Role" type="vnd.ez.api.Role"></xsd:element>
            </xsd:sequence>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.RoleAssignInput">
        <xsd:all>
          <xsd:element name="Role" type="ref" />
          <xsd:element name="limitation" type="roleLimitationType" />
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.RoleAssignment">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="limitation" type="roleLimitationType" />
              <xsd:element name="Role" type="ref"/>
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:complexType name="vnd.ez.api.RoleAssignmentList">
        <xsd:complexContent>
          <xsd:extension base="ref">
            <xsd:all>
              <xsd:element name="RoleAssignment" type="vnd.ez.api.RoleAssignment" />
            </xsd:all>
          </xsd:extension>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:element name="RoleInput" type="vnd.ez.api.RoleInput"/>
      <xsd:element name="Role" type="vnd.ez.api.Role"/>
      <xsd:element name="RoleAssignInput" type="vnd.ez.api.RoleAssignInput"/>
      <xsd:element name="RoleAssignmentList" type="vnd.ez.api.RoleAssignmentList"/>
    </xsd:schema>


.. _ErrorMessage:

ErrorMessage XML Schema
-----------------------

.. code:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <xsd:schema version="1.0" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
      xmlns="http://ez.no/API/Values" targetNamespace="http://ez.no/API/Values">
      <xsd:include schemaLocation="CommonDefinitions.xsd" />

      <xsd:complexType name="vnd.ez.api.ErrorMessage">
        <xsd:all>
          <xsd:element name="errorCode" type="xsd:string"></xsd:element>
          <xsd:element name="errorMessage" type="xsd:string"></xsd:element>
          <xsd:element name="errorDescription" type="xsd:string"></xsd:element>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="ErrorMessage" type="vnd.ez.api.ErrorMessage"></xsd:element>
    </xsd:schema>

