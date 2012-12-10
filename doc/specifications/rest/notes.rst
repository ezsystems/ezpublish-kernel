===============
REST API review
===============

The following document contains a review of the eZ REST API specification as of
January 31st 2012. The document is structured in two main parts: The
chapter Suggestions_ contains general suggestions, which apply to the whole API
or large parts of it. The second part, `Resources & Operations`_ contains a
list of additional findings, grouped by resource URIs. The latter findings
occurred during the review and has no right to completeness. Not all resources
and actions have been investigated in full detail, due to timing constraints.
However, the findings seem to be representative for the API and should be seen
as a reference for all resources.

The general impression of the API is quite good. The resource structuring, and
the resulting URL structure, looks decent and most of the used methods and
structs are fine. However, we have a few fundamental concerns. These are
explained in following and reasonable solution approaches are discussed. Please
not, that these solutions are not ready for use, yet, but rather need
additional investigation.

-----------
Suggestions
-----------

In following, you find suggestions, which affect the whole REST API.

HATEOAS
=======

The current version of the API specification already provides an advanced means
of locating resources through their HTTP URL and using HTTP method semantics to
provide actions on these resources. Getting this straight (see further
examination) allows us to rate your API already on level 2 of the `Richardson
Maturity Model`__. In order to achieve level 3 (maximum), only the usage of
hypermedia controls (links) is missing. We highly recommend to add this feature
to your API, for several reasons:

- The API will available through millions of servers in the future and should
  therefore be top-notch in order to fulfill all imaginable requirements.
- Using hypermedia controls allows you to shift resource URIs around later,
  which helps to stay backwards compatible even if changes become necessary.
- The latter point is especially viable if you might need to move certain
  resources to dedicated servers to redirect them to a different system.

The idea of hypermedia controls is basically to provide the client with hyper
links to related resources and operations, together with the resource
representation itself. For example, the resource of a content object should
provide links to its version list, relations and so on::

    http://…/content/objects/23
        versions   ->       http://…/content/object/23/versions
        current_version ->  http://…/content/object/23/version/5
        relations  ->       http://…/content/object/23/version/5/relations

Having the possible sub-ordinates and related resources linked directly allows
a client to auto-discover these operations (if modelled consistent) and avoids
clients to rely on manually built links, which might break in the future.

In order to use hypermedia controls, you need to realize the following steps:

1. Replace all IDs with links to the corresponding resource
2. Add links to sub-ordinate and related resources to the representations

The representation of such links is crucial to their usability. It should
include:

- The link target
- The link relation type
- The hyper media type of the related resource

A common way to achieve this is to re-use the ``<link>`` element of the Atom_
specification, which already provides a convenient interface to this
information. Another way would be to create a custom link format and re-use the
XLink__ specification attributes. Both approaches facilitate the consumer of
the API by the usage of existing standards.

If you insist of using the JSON format for the API, it is recommended that you
adjust one of these standards to your REST format. For further information see
`JSON vs. XML`_.

__ http://martinfowler.com/articles/richardsonMaturityModel.html
__ http://www.w3.org/TR/xlink11/


JSON vs. XML
============

JSON is a fully valid format to be used for REST APIs and we acknowledge, that
it especially eases the use of a REST API in the AJAX environments. Still, the
usage of an XML format as your resource representation is better suited for
REST in general, due to the following reasons. We can therefore only recommend
to use XML instead of, or at least in addition to, JSON.

Attributes
----------

JSON structures are uni-dimensional, since they do not support the annotation
of data with meta data. XML has this facility built in, using XML attributes.
This allows you to easily add information to to your elements, which affect the
usage of the API and do not represent data transmitted with it.

This is, for example, crucial for the usage of hypermedia controls (HATEOAS_),
since links need to be annotated with a relation type.

Namespaces
----------

There is no way to namespace elements in JSON. This avoids to mix own data with
3rd party formats transparently to the client, which might be needed for future
API additions or the re-use of existing data formats. For example, the use of
Atom and XLink (see HATEOAS_) is not possible in JSON. A result from this is,
that developers cannot easily recognize common elements in different APIs.

Evolution
---------

It is way harder to evolve a JSON data format in the future, than it is for
XML. For example, to allow multiple elements in the future in a place where
only a single element was allowed in the past.

For example::

    <article>
        <title>Blog entry</title>
        <body>…</body>
        <category>php</category>
    </article>

can easily become::

    <article>
        <title>Blog entry</title>
        <body>…</body>
        <category>php</category>
        <category>open source</category>
    </article>

without breaking old client implementations.

In addition, the support for `Namespaces`_ supports these evolution
possibilities of XML data formats drastically.

Format complexity
-----------------

Due to the lack in support for attributes and namespaces, JSON representations
become extremely more complex than XML representations. To illustrate this, here
is an example of the HAL resource embedding approach, as mentioned in `Resource
embedding`_.

XML::

    <resource href="/orders">
      <link rel="next" href="/orders?page=2" />
      <link rel="search" href="/orders?id={order_id}" />
      <resource rel="order" href="/orders/123">
        <link rel="customer" href="/customer/bob" title="Bob Jones <bob@jones.com>" />
        <resource rel="basket" href="/orders/123/basket">
          <item>
            <sku>ABC123</sku>
            <quantity>2</sku>
            <price>9.50</price>
          </item>
          <item>
            <sku>GFZ111</sku>
            <quantity>1</quantity>
            <price>11.00</price>
          </item>
        </resource>
        <total>30.00</total>
        <currency>USD</currency>
        <status>shipped</status>
        <placed>2011-01-16</placed>
      </resource>
    </resource>

JSON::

    {
      "_links": {
        "self": { "href": "/orders" },
        "next": { "href": "/orders?page=2" },
        "search": { "href": "/orders?id={order_id}" }
      },
      "_embedded": {
        "order": [
          {
            "_links": {
              "self": { "href": "/orders/123" },
              "customer": { "href": "/customer/bob", "title": "Bob Jones <bob@jones.com>" }
            },
            "total": 30.00,
            "currency": "USD",
            "status": "shipped",
            "placed": "2011-01-16",
            "_embedded": {
              "basket": {
                "_links": {
                  "self": { "href": "/orders/123/basket" }
                },
                "items": [
                  {
                    "sku": "ABC123",
                    "quantity": 2,
                    "price": 9.50
                  },{
                    "sku": "GFZ111",
                    "quantity": 1,
                    "price": 11
                  }
                ]
              }
            }
          }
        }
      }

Drawbacks
---------

Of course, the usage of XML in favor of JSON has some drawbacks. For example,
the processing of XML data structures is quite more complex in most languages.
However, this drawback can easily be abolished, by providing a slim SDK layer
in the most important languages (PHP and JavaScript). The PHP SDK is already
planned to be implemented for testing purposes anyway.

Accept header
-------------

In case you decide to provide multiple different representation formats for
your resources, we recommend to use the ``Accept`` header in order to determine
which representation is required by a certain client.

Resource embedding
==================

One essential violation against the basic principles of REST can be found all
over the specification of your API: The duplication of resources, by making a
resource available through multiple URIs. There is a huge chain of negative
effects from this violation.

Impact of resource duplication
------------------------------

Clients can no more detect if 2 resources are the same. Therefore, it is
impossible to cache the resource representation. Additionally, using an
identity map to ensure object consistency on the client side is impossible.
This also affects caching solutions like Varnish, which can have 2 different
representations of the same resource cached and therefore deliver inconsistent
data to the client.

We therefore highly recommend to stick to the rule of having a single resource
identified by a singe URI. The additional API calls (and HTTP requests) needed
to look up specific resource through links (see HATEOAS_) can easily be
compensated by HTTP level caching and SDK level caching.

Embedding theory
----------------

However, we understand the need to provide the data of related resources with a
single request (e.g. the current object version and its fields when requesting
``/content/objects/<ID>``) and to duplicate related resources in some places
(e.g. ``/content/objects/locations`` and ``/content/locations/<ID>``). But,
instead of simply duplicating the resource, we recommend to use the embedding
of related resources.

Embedding of resources in this case means: Instead of providing the
raw data of related resources, in scope of the requested resource, the data is
clearly annotated to be part of another resource, which is identified by a
different URI than the requested one.

This mitigates some of the problems named above: Clients recognize, that the
additional data is originally located at a different URI. They can therefore
replace the embedded with an identified (and potentially modified) version on
there side. In addition, a client knows immediately where to go in order to
perform operations on the embedded resource.

Embedding practical
-------------------

There already exists an approach to resource embedding in REST APIs, which is
called HAL_. However, this approach has 2 essential problems: First, it
provides a general purpose hyper media format, which violates the aspect of
having each API provide its own, simple format. Secondly, the approach has
still some issues in its realization of links.

Still, HAL_ can deal as in inspiration basis for modelling a custom resource
embedding format, since it already provides translations between XML and JSON,
which might be necessary, if both formats are to be supported (see `JSON vs.
XML`_).

To visualize the idea of resource embedding, we have made up a simplified
example for an XML representation of a resource which is located under
``…/content/objects/23``:

.. include:: examples/content_objects_23.xml
   :literal:

As can be seen, the represented resource itself is of type ``ContentInfo``,
which acknowledges the fact, that the requested URI only represents the version
independent meta information of a content object. However, it is assumed that
the standard use case is not to retrieve only this meta information, but the
"current version" of the object in addition, as well as all of its fields.

In pure REST, the "current version" would only be a link to the corresponding
resource (here: ``…/content/objects/23/versions/5``), which must be requested
by the client, in order to retrieve its state. This link is realized in the
example, using Atom_ style and the corresponding namespace. However, instead of
only giving the link, the corresponding resource representation is included as
a sub-ordinate of the link (``<VersionInfo embedded="true">``). In addition,
the representation is marked embedded, which gives a clear indicator to the
client, that the encapsulated data is typically found at a different URI.

The second embedding in the example shows the embedding of version fields into
the version meta data.

SDK transparency
----------------

Practically, the embedding of related resources could be switched on/off by the
client through a URL parameter (e.g. ``embed=true``). In addition, it can be
clearly communicated, that embedding behavior might change, so that the client
state machine can react accordingly. For example, like this::

    IF link->hasChildren()
        resource = parseResource( link->firstChild(), link->type )
    ELSE
        resource = parseResource( get( link->href ), link->type )
    END IF

This approach also enables you to first go with a completely clean API in a
first step (without embedding resources at all or just very few) and to
dynamically add or remove embeds, which occur to be needed or result in issues.

Warning
-------

Even with this technique of embedding there will exist essential caching
issues: Additional resource representations must be invalidated, if their
embedded resources are updated. This can easily result in a situation which
requires to purge the full cache for most updates. On the client side, caching
can only work on an object basis and not on HTTP level anymore.

We can therefore only recommend to use a completely duplication free REST API,
to enable safe and simple caching on HTTP level. This is one of the main
benefits of REST. The additional HTTP requests needed to retrieve related and
sub-ordinate resources can easily be handled by an SDK. Making this use client
side caching can mitigate the performance issues to a high degree.

However, if there is still a need for embedding resource, we highly recommend
to keep this to a minimum. In fact, for a first version, one can prototype the
idea described in this chapter in only 1 or 2 places. One can then subsequently
add additional places, where problems occur, or remove the whole embedding
concept again.

Caching mechanisms
==================

It is highly recommended to take care for caching mechanisms on the client, as
well as on the server side. Therefore,

Custom HTTP methods
===================

In a few cases, it is desirable to send custom HTTP request verbs to your API,
which are namely

- ``PATCH`` to perform partial updates
- ``PUBLISH`` to publish a draft of a content object version

We agree that this is a nice way of handling these actions, especially since
there is already an `RFC for the PATCH method`__.

However, there are some issues in some browser implementations, to not support
custom HTTP verbs, as summarized in `this blog article`__. Basically, all
recent browsers seem to support the standard HTTP methods, but especially
Internet Explorer does not support custom verbs.

To ship around this issue, we recommend to allow these methods to be
alternatively issued as ``POST`` request and a specific header, which indicates
the original method. For example::

    X-eZ-Method: PATCH

It should be clearly documented, that using these methods is only feasible for
use in the browser and that all other implementations (e.g. accessing the API
from PHP) must use the correct HTTP verbs.

There is also a `blog post by Roy Fielding on the relative view on POST`__.

__ http://tools.ietf.org/html/rfc5789
__ http://annevankesteren.nl/2007/10/http-method-support
__ http://roy.gbiv.com/untangled/2009/it-is-okay-to-use-post

Error codes
===========

We were missing some error codes from your list of general error codes, which
are sensible to be issued under certain circumstances:

400 Bad Request
  Should be issued if the request is malformed (missing parameter, incorrect
  payload, etc.)
401 Unauthorized
  Raised if user authorization is missing.
403 Forbidden
  Issued if authentication can not fix the access issue.

Furthermore you should also consider the following status codes as good
practice:

- 301 Moved Permanently
- 304 Not Modified
- 307 Temporary Redirect

HTTP headers
============

We recommend especially the obey to the following HTTP headers:

- Allow (determining the allowed methods on the resource)
- Accept
- Accept-Language
- Various Cache-Control headers
- Content-Type
- ETag
- If-*
- Location

Especially the ``Location`` header is viable for new resource created by POST.


Media types
===========

In order to identify the media types used by the REST API (for request and
response payloads), you should define a clear naming scheme and use it in your
application. This helps users to easily know what type of data structure they
can expect and need to send. In addition, it helps you versioning your API.

A common standard seems to be::

   application/vnd.no.ez.public-api.Fields+xml

The first part here specifies that the media type belongs to a certain vendor
(``vnd``) and is non-standard. After that follows a reverse domain lookup of
the vendor (``no.ez``) and the application (``public-api``). Finally, the
resource type is identified (here: ``Fields`` for a list of content fields).
Finally, the representation for the resource state is indicated (``+xml``).
For JSON encoding of the representation, simply replace ``+xml`` with
``+json``.

In order to version your API you can easily, using the following syntax::

   application/vnd.no.ez.public-api.Fields+xml;v=1.2

This allows you to stay backwards compatible with old clients, while evolving
your media types over time.

Clients should therefore send these media type identifiers in their ``Accept``
and ``Content-Type`` headers, the same applies for the server in its response
(``Content-Type``) and especially to ``400`` responses, to indicate the correct
media type.

Documentation
=============

A good REST API requires a fair amount of documentation, in order to be
successfully used. We therefore recommend three ways of documentation to be
combined:

Reference
---------

This documentation should have three sections:

1. Concepts
   explaining the realized concepts, like the basic structure, the encoding
   formats, how links (and embedding?) works, best practices, and so on.
2. Resources
   examining the resources used, the URL scheme and methods supported on each
   resource. This should reference the 3. section heavily.
3. Media Types
   mentioning all occurring media types, referencing the corresponding
   resources, which uses them.

Tutorials
---------

Explanations on how to achieve important use cases with the REST API.

HTML Representation
-------------------

We also recommend to implement HTML representation in your REST API itself.
This allows users to simply access the API using their browser and inspecting
it manually. This representation should:

- Visualize the state of the resource in HTML
- Display possible methods (already available in the ``Allow`` header)
- Transform links to clickable HTML links
- Reference the media type and resources sections in Reference_

--------------
Authentication
--------------

We consider your proposed 3 authentication types (basic, oauth2, session) a
good choice, since they allow the usage of the API in various use cases.
However, the ``basic`` authentication is especially insecure and should only be
used in HTTPs secured environments. It might therefore make sense to add digest
authentication in addition, since this one does not send the password in clear
text. You should also have a look at the CouchDB authentication abstraction,
since this one basically provides your selected methods, too.

- /user/sessions should be /users/<$userId>/sessions
- Providing user info as response to the session login violates rest

  - 2 URLs represent the same resource

----------------------
Resources & Operations
----------------------

Content
=======

- ``/content/objects``

  - Method ``GET``

    - Should typically only reference objects
    - `Resource embedding`_ should be considered

  - ``POST``

    - Duplicate usage for

      - Create new content object
      - Copy a content object

    - Copying should rather be a dedicated operation

      - Could be inspired by the ``COPY`` operation in WebDAV
      - Mixing of locations and content objects is confusing

        - Maybe rather "copy a location"?
        - Would also fit better with WebDAV copy

- ``/content/objects/views``

  - Why isn't this in ``/content/views``?
  - Might be very confusing to see this as a subordinate of
    ``/content/objects``
  - ``POST``

    - Why is there no ``POST`` allowed?
    - Expected as the default for creating a new view
    - Why should a user know the ID upfront?

  - ``GET``

    - Missing content type
    - Can / is the list of views restricted somehow?

  - ``PUT``

    - Should work on ``/content/views/<ID>`` instead

  - Cleaner structure would be

    - ``/content/views`` list of views (links)
    - ``/content/views/<ID>`` the view query structure
    - ``/content/views/<ID>/results`` list of links to results

      - Results could be embedded using `Resource embedding`_

- ``/content/objects/view/<ID>``

  - ``POST``

    - Are views materialized on the server?
    - If yes, an empty ``POST`` to a view could trigger a (stale) update

  - ``GET``

    - Should typically only reference objects or versions

      - Could be embedded using `Resource embedding`_

    - See above, the query would be expected here

      - Use ``…/views/<ID>/results`` instead

- ``/content/objects/<ID>``

  - ``/content/locations/<locationId>/content``

    - Is a duplicate
    - For such duplicates, `Resource embedding`_ style embedding could be an option

  - Get results in different formats

    - The file extension is not a nice option

      - duplicates resource representations

    - Rather use the `Accept header`_ instead

  - ``GET``

    - Inconsistency to ``PUT``

      - ``PUT`` works on ``ContentInfo`` (basically)
      - ``GET`` returns a specific version

    - Should rather

      - Return ``ContentInfo``

        - This is also updateable through PUT

      - Embed the "current version" using `Resource embedding`_

        - Maybe embed further down (fields, etc.)

  - ``DELETE``

    - Maybe a ``TRASH`` method would make more sense?

      - Clear distinguishing

    - On trash, this method should return ``301``

      - With ``Location`` header to the new location

- ``/content/objects/<ID>/translations``

  - Looks fine

- ``/content/objects/<ID>/languages/<language_code>``

  - This is duplication of resources

  - Instead only one syntax should be kept:

    - ``/content/objects/<ID>?languages=<language_code>,...``

  - This can also work for ``DELETE`` method

- ``/content/objects/<ID>/currentversion``

  - Duplication of resources
  - Is already refered to a ``/content/objects/<ID>``

- ``/content/objects/<ID>/versions``

  - ``POST``

    - Maybe the ``COPY`` method (borrowed from WebDAV) would make more sense?

  - ``GET``

    - VersionInfo returned should contain links to

      - ContentInfo
      - Version

  - ``/content/objects/<ID>/versions/<versionNo>/info``

    - This is unclear: VersionInfo is already listed
    - Is this really necessary?

- ``/content/objects/<ID>/locations``

  - ``GET``

    - This should usually only be links to the location resources
    - Resource embedding would be possible

  - ``POST``

    - Allowing to create a new location here sounds strange

      - No idea if location is really available

    - Better:

      - Use location links only in ``GET``
      - Do not allow ``POST`` at all

  - ``DELETE``

    - Makes more sense
    - But is the use case really there?

      - How many locations will a content object have?
      - How often will all of them be deleted?
      - What effort does deleting them in dedicated requests give?

- ``/content/objects/<ID>/mainlocation``

  - Resource duplication
  - Should be linked in ``/content/objects/<ID>``

    - Maybe `Resource embedding`_

- ``/user/users/<ID>/drafts``

  - Duplicates resources
  - Should therefore only contain links
  - If really necessary, use `Resource embedding`_
  - Another option is to handle this purely via search

- ``/content/objects/<ID>/section``

  - ``GET``

    - Should not return section, but section link

      - Duplication of resources ``/content/sections/<ID>``

    - Maybe can use `Resource embedding`_

- ``/content/objects/<ID>/relations``

  - ``GET``

    - Should return link to "current version" relations

      - Duplication of resources

    - Maybe use `Resource embedding`_

- ``/content/objects/<ID>/versions/<no>/relations``

  - ``GET``

    - Should return links to ``/content/objects/<ID>/versions/<no>/relations``

      - Duplication of resources

    - Maybe use `Resource embedding`_

- ``/content/locations``

  - ``PUT``

    - See `Media Types`_ for issues with LocationInput
    - Putting to the base for new locations is not optimal

      - New location is always a sub-ordinate of it's parent

    - What about this structure?

      - ``/content/locations``

        - Search view on locations
        - Default search only refers to root location
        - Should be links (and maybe use `Resource embedding`_)

      - ``/content/locations/<location path>``

        - Location details
        - Locations are trees anyway
        - Would allow:

          - ``POST`` on ``/content/locations/some/location/children``
            to create a new child

      - ``/content/locations/<ID>/parent``

        - Should be a link (`Resource embedding`_?)

- ``/content/trash/items``

  - Why not ``/content/trash``?

Content Types
=============

- ``/content/typegroups``

  - ``GET``

    - Should be links to type groups (`Resource embedding`_?)
    - Additional link to ``/content/types`` (to list types)

- ``/content/typegroups/<ID>/types``

  - ``GET``

    - Should be links to type groups (`Resource embedding`_?)

  - ``PUT``

    - Should actually post a link to an existing type

  - ``DELETE``

    - Should correspondingly delete the link

  - ``POST``

    - Should not create a type (for this use ``/content/types``) but only
      assign a type to a group

- ``/content/types``

  - ``POST``

    - Maybe use the WebDAV ``COPY`` method instead?
    - Why is there no way to create a brand new type?
      - This should work here!

- ``/content/types/<ID>``

  - Field definitions should not be part of response

    - Actually, only a link to ``/content/types/<ID>/fieldDefinitions`` should
      be there
    - Maybe use `Resource embedding`_?

      - Would need nested-embedding

- ``/content/types/<ID>/fieldDefinitions``

  - Should be a list of links to field definitions

- ``/content/types/<ID>/groups``

  - Duplication!

Resource representations
========================

There are still some issues with the resource representations. We summarized
some examples. Please ensure to verify the resource representations in further
detail, especially in respect to the Public API.

- ContentInfo

  - Is missing a link to all available versions
  - Should rather use links instead of IDs

    - contentId (should be SELF)
    - ownerId
    - sectionId
    - mainLocationId
    - currentVersionNo

      - Reference to version
      - And version number in addition?

  - contentType

    - should be a link

  - mainLanguageCode

    - is not of format ``date-time``

  - …

- LocationInput

  - ``contentId`` and ``parentId`` are not parameters, but payload


.. _`RFC 5789`: http://tools.ietf.org/html/rfc5789
.. _HAL: http://stateless.co/hal_specification.html
.. _Atom: http://tools.ietf.org/html/rfc4287


..
   Local Variables:
   mode: rst
   fill-column: 79
   End:
   vim: et syn=rst tw=79
