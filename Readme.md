# eZ Publish 5.x Kernel
[![Build Status](https://travis-ci.org/ezsystems/ezpublish-kernel.png?branch=master)](https://travis-ci.org/ezsystems/ezpublish-kernel)

Welcome to the new eZ Publish 5.x Kernel, this code repository contains several layers of API's and implementation of them.
However it does not contain all parts that make up the eZ Publish 5 install, for the full eZ Publish 5 package including
bundles, Legacy Stack, install doc and more; please see our [ezpublish-community](https://github.com/ezsystems/ezpublish-community) repository.

## Legacy Stack (LS)

Legacy Stack: Legacy kernel (4.x) + extensions

eZ Publish 5.x is a bottom up rewrite of eZ Publish, so a conservative approach where taken on backwards compatibility
by bundling both Legacy Stack (4.x) and 5.x Stack together in one integrated package (ref ezpublish-community repository above).

In addition to the BC reason, the second reason is that eZ Publish 5.x does not yet provide own UI's, editor and admin
gui is for the time being still provided by Legacy Stack.

The legacy integrations are done in many parts of the systems, making it possible to use both kernels in the same request,
hence being able to do a smooth transition from existing 4.x installation to 5.x installation going forward.

However for performance reasons we recommend trying to use either legacy with "legacy\_mode" turned on or pure 5.x Stack
on a siteaccess case by case basis. This will still make sure cache and other integrations work together (something that
is not the case if you point Apache directly to eZ Publish Legacy), but will avoid duplicate lookups ("fallbacks").


## 5.x Stack

5.x Stack: 5.x kernel + Bundles (former extensions)

### Bundles
The highest level in the eZ Publish 5 architecture are bundles that builds on top of everything bellow, this is where
most eZ Publish 5 Bundles  will be written. They will exist in separate git repositories, and optionally
defined as dependencies in your project composer.json file (see ezpublish-community repository).

### 5.x Kernel

#### Kernel Bundles: REST, Core & Legacy
These bundles are important parts of the eZ Publish 5.x kernel.

* Core Bundle: Provide additional features to a standard Symfony2 distribution like multilingual UrlAlias routing,
  siteaccess matching, permissions and helpers for Public API use.
* Legacy Bundle: Integrations with Legacy kernel, like fallbacks and code reuse across 5.x/Legacy Stack.
* REST Bundle: Integration of REST API to 5.x (Symfony) Stack

You can find these in [eZ/Bundle](eZ/Bundle/) and their lower level parts in:
* [eZ/Publish/Core/REST](eZ/Publish/Core/REST/)  *The REST API implementation*
* [eZ/Publish/Core/MVC](eZ/Publish/Core/MVC/)  *MVC implementation that integrate with Symfony and Legacy*


#### Public API
Public API currently provides access to the Content Repository of eZ Publish, exposing Content, Locations
(former Nodes), Sections, Content Types (former Content Classes), User Groups, Users and Roles.
It also provides a new clear interface for plugging in custom field types (former Datatypes).

Public API is built on top of a set of SPI's abstracting storage/file/\* functionality.
By using Public API your code will be forward compatible to future releases based on enhanced, more scalable and more
performant storage engines. It is also fully backwards compatible by using the included "Legacy" storage engine, which
stores data in the way legacy kernel is used to finding it.

Important parts of this layer is:
* [eZ/Publish/API](eZ/Publish/API/)  *Public API Interfaces*
* [eZ/Publish/Core/Repository](eZ/Publish/Core/Repository/)  *Public API Repository implementation*

#### (Private) SPI(Service Provider Interface)

Service Provider Interfaces are interfaces that can contain one or several implementations, in some cases Public API
will only be able to use one at a time; Persistence (database), IO (file system). In other cases it expects several
implementations; FieldTypes (former DataTypes), Limitations (permissions system).

SPI layer is currently considered "private" as it will still undergo changes, it will be made "final" by the time we
have a fully working NoSQL implementation of Persistence and scalable IO storage implementation like S3.
Meaning you can make your own implementation if you want, but we don't guarantee that it will work across versions.

Currently SPI consists of:
* [eZ/Publish/SPI](eZ/Publish/SPI/)  *Service provider interfaces*
* [eZ/Publish/Core/Persistence/Legacy](eZ/Publish/Core/Persistence/Legacy/)  *Legacy Storage-Engine (Persistence-handler)*
* [eZ/Publish/Core/Persistence/InMemory](eZ/Publish/Core/Persistence/InMemory/)  *InMemory Storage-Engine (for unit testing)*
* [eZ/Publish/Core/IO](eZ/Publish/Core/IO/)  *IO (file) Handlers; Legacy, Dispatcher and InMemory (for unit testing)*


## Dependencies
* **Composer**: Just run `curl -s http://getcomposer.org/installer | php` to get **composer.phar**
* **PHPUnit 3.6+**
* **PHP 5 Modules**: php5\_sqlite
* **Database**: sqlite3 if not installed by above stage

## How to run tests
* Clone this repo
* Install dependencies with **Composer**: `php composer.phar install --prefer-dist`
* Copy config.php-DEVELOPMENT to config.php
* Execute `phpunit -vc phpunit*.xml` with one of:
  * phpunit.xml  *unit test xml configuration*
  * phpunit-integration-legacy.xml  *integration test xml configuration for running integration tests with Legacy Storage engine*

## Issue tracker
Submitting bugs, improvements and stories is possible on https://jira.ez.no/browse/EZP

## Contributing
eZ Publish 5.x is a fully open source, community-driven project, and code contributions are simply done via github pull requests.

Short:
* Remember to first create a issue in our issue tracker and refer to it in commits and pull requests headers, example:
  "Fixed EZP-20104: ContentController should return error status when content is not found"
* If you want to contribute implementation specification proposals, place them in [doc/](doc/) folder.
* Keep different changes in different commits in case cherry-pick is preferred instead of a merge later.
  * A Pull Request should only cover one issue
  * A commit should not contain code changes at the some time as doing coding standards/whitespace/typo fixes
* TDD: Write/Change the test(s) for the change you do and commit it before you do the actual code change
  * If a bug affects Public API, write or enhance a integration test to make sure the bug is covered.
  * Unit tests should only use mocks/stubs and never test the full stack like integrations tests does.
* Please test/check your commits before pushing even if we have automated checks in place on pull requests:
  * Run unit tests and integration test before commits
  * Make sure you follow our [coding standards](https://github.com/ezsystems/ezcs)

For further information please have a look at the [related guidance page](http://share.ez.no/get-involved/develop). You will, amongst other, learn how to make pull-requests. More on this here : ["How to contribute to eZ Publish using GIT"](http://share.ez.no/learn/ez-publish/how-to-contribute-to-ez-publish-using-git).

## Discussing/Exchanging
A dedicated forum has been set-up to discuss all PHP API-related topics : http://share.ez.no/forums/new-php-api

## Copyright & license
eZ Systems AS & GPL 2.0
