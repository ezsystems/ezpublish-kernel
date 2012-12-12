# eZ Publish 5.x Kernel
[![Build Status](https://travis-ci.org/ezsystems/ezp-next.png?branch=master)](https://travis-ci.org/ezsystems/ezp-next)

Welcome to the new eZ Publish 5.x Kernel, this code repository contains several layers of API's and implementation of them.
However it does not contain all parts that make up the eZ Publish 5 install, for the full eZ Publish 5 package including
bundles, Legacy Stack, install doc and more; please see our [ezpublish5](https://github.com/ezsystems/ezpublish5) repository.

## Legacy Stack (LS)

Legacy Stack: Legacy kernel (4.x) + extensions

eZ Publish 5.x is a bottom up rewrite of eZ Publish, so a conservative approach where taken on backwards compatibility
by bundling both Legacy Stack (4.x) and 5.x Stack together in one integrated package (ref ezpublish5 repository above).

In addition to the BC reason, the second reason is that eZ Publish 5.x does not yet provide own UI's, editor and admin
gui is for the time being still provided by Legacy Stack.

The legacy integrations are done in many parts of the systems, making it possible to use both kernels in the same request,
hence being able to do a smooth transition from existing 4.x installation to 5.x installation going forward.

However for performance reasons we recommend trying to use either legacy with "legacy_mode" turned on or pure 5.x Stack
on a siteaccess case by case basis. This will still make sure cache and other integrations work together (something that
is not the case if you point Apache directly to eZ Publish Legacy), but will avoid duplicate lookups ("fallbacks").


## 5.x Stack

5.x Stack: 5.x kernel + Bundles (former extensions)

### Bundles
The highest level in the eZ Publish 5 architecture are bundles that builds on top of everything bellow, this is where
most eZ Publish 5 Bundles  will be written. They will exist in separate git repositories, and optionally
defined as dependencies in your project composer.json file (see ezpublish5 repository).

### 5.x Kernel

#### Kernel Bundles: REST, Core & Legacy
These bundles are important parts of the eZ Publish 5.x kernel.

* Core Bundle: Provide additional features to a standard Symfony2 distribution like multilingual UrlAlias routing,
  siteaccess matching, permissions and helpers for Public API use.
* Legacy Bundle: Integrations with Legacy kernel, like fallbacks and code reuse across 5.x/Legacy Stack.
* REST Bundle: Integration of REST API to 5.x (Symfony) Stack



#### Public API
Public API currently provides access to the Content Repository of eZ Publish, exposing Content, Locations
(former Nodes), Sections, Content Types (former Content Classes), User Groups, Users and Roles.
It also provides a new clear interface for plugging in custom field types (former Datatypes).

Public API is built on top of a set of SPI's abstracting storage/file/* functionality.
By using Public API your code will be forward compatible to future releases based on enhanced, more scalable and more
performant storage engines. It is also fully backwards compatible by using the included "Legacy" storage engine, which
stores data in the way legacy kernel is used to finding it.

#### (Private) SPI(Service Provider Interface)

Service Provider Interfaces are interfaces that can contain one or several implementations, in some cases Public API
will only be able to use one at a time; Persistence (database), IO (file system). In other cases it expects several
implementations; FieldTypes (former DataTypes), Limitations (permissions system).

SPI layer is currently considered "private" as it will still undergo changes, it will be made "final" by the time we
have a fully working NoSQL implementation of Persistence and scalable IO storage implementation like S3.

##Directory Layout
* [eZ/Publish/API](/ezsystems/ezp-next/tree/master/eZ/Publish/API/)  *Public API Interface (interfaces eZ Publish implements)*
* [eZ/Publish/API/Repository/Examples](/ezsystems/ezp-next/tree/master/eZ/Publish/API/Repository/Examples/)  *Examples of Public API use*

* [eZ/Publish/SPI](/ezsystems/ezp-next/tree/master/eZ/Publish/SPI/)  *Service provider interfaces (interfaces extensions can implement)*

* [eZ/Publish/Core/Repository](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Repository/)  *Public API Repository implementation*
* [eZ/Publish/Core/Persistence/Legacy](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Persistence/Legacy/)  *Legacy Storage-Engine aka Persistence-handler*
* [eZ/Publish/Core/Persistence/InMemory](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Persistence/InMemory/)  *InMemory  Storage-Engine aka Persistence-handler (for unit testing)*
* [eZ/Publish/Core/IO/LegacyHandler.php](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/IO/)  *Legacy IO (file) Handler*
* [eZ/Publish/Core/IO/InMemoryHandler.php](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/IO/)  *InMemory IO handler (for unit testing)*

* [eZ/Publish/Core/MVC/Symfony](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/MVC/Symfony/)  *MVC components that integrate with Symfony*
* [eZ/Publish/Core/MVC/Legacy](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/MVC/Legacy/)  *eZ Publish Legacy components integration*
* [eZ/Bundle](/ezsystems/ezp-next/tree/master/eZ/Bundle/)  *Bundles that wrap eZ Publish components into Symfony*

* [doc/](/ezsystems/ezp-next/tree/master/doc/)  *doc folder for specifications and bc doc*
* phpunit.xml  *unit test xml configuration*
* phpunit-integration-legacy.xml  *integration test xml configuration for running integration tests with Legacy Storage engine*


##Dependencies
* **Composer**: Just run `curl -s http://getcomposer.org/installer | php` to get **composer.phar**
* **PHPUnit 3.6+**
* **PHP 5 Modules**: php5_sqlite
* **Database**: sqlite3 if not installed by above stage

##How to run tests
* Clone this repo
* Install dependencies with **Composer**: `php composer.phar install`
* Copy config.php-DEVELOPMENT to config.php
* Execute `phpunit -vc phpunit*.xml`

##Bug tracker
Submitting bug reports is possible on https://jira.ez.no/browse/EZP

##Contributing
eZ Publish 5.x is a fully open source, community-driven project. If you''d like to contribute, please have a look at the [related guidance page](http://share.ez.no/get-involved/develop). You will, amongst other, learn how to make pull-requests. More on this here : ["How to contribute to eZ Publish using GIT"](http://share.ez.no/learn/ez-publish/how-to-contribute-to-ez-publish-using-git).

##Discussing/Exchanging##
A dedicated forum has been set-up to discuss all PHP API-related topics : http://share.ez.no/forums/new-php-api

##Copyright & license
eZ Systems AS & GPL 2.0
