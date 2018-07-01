# eZ Platform Kernel
[![Build Status](https://img.shields.io/travis/ezsystems/ezpublish-kernel.svg?style=flat-square&branch=master)](https://travis-ci.org/ezsystems/ezpublish-kernel)
[![Downloads](https://img.shields.io/packagist/dt/ezsystems/ezpublish-kernel.svg?style=flat-square)](https://packagist.org/packages/ezsystems/ezpublish-kernel)
[![Latest version](https://img.shields.io/github/release/ezsystems/ezpublish-kernel.svg?style=flat-square)](https://github.com/ezsystems/ezpublish-kernel/releases)
[![License](https://img.shields.io/github/license/ezsystems/ezpublish-kernel.svg?style=flat-square)](LICENSE)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0885c0ce-4b9f-4b89-aa9c-e8f9f7a315e0/big.png)](https://insight.sensiolabs.com/projects/0885c0ce-4b9f-4b89-aa9c-e8f9f7a315e0)

Welcome to the *eZ Platform Kernel* (also known as *eZ Publish 6.x and higher kernel*). The kernel is the heart of *eZ Platform*, a modern
CMS built on top of the Symfony (Full Stack) Framework. Containing an advanced Content Model, it allows you to structure any kind of content or content-like data in a future-proof Content Repository. It also aims to provide additional features for the MVC layer (Symfony) to increase your productivity.

This code repository contains several layers of (and implementations of) APIs. *Kernel* refers to this being the core,
as opposed to the *Full Stack* which has bundles, user interfaces, and installers, all configured to make a complete application.

*In other words this repo is for core development; for fixes, features and documentation of the Platform kernel itself.*


## What is eZ Platform?

eZ Platform is a bottom-up rewrite of eZ Publish, so a conservative approach was taken on backwards compatibility. The first steps were taken by introducing it in 2012 as *eZ Publish Platform 5.0*. This bundled a *Legacy Stack* ("4.x") & a *Platform Stack* (formerly "5.x", or "new stack") together into one distribution. Starting in 2015 with *eZ Platform*, this is no longer the case. The *Platform* has matured to become its own modern, self-sufficient CMS/CMF, and it can be used to meet your needs without having to also deal with any *Legacy* elements.

### Getting a Full Install (*Full Stack*)

Reflecting what is described above there are several options to get a full install of this Kernel, see:

- [eZ Platform](https://github.com/ezsystems/ezplatform): For a clean install of eZ Platform, a modern Symfony CMS.
 - [eZ Platform demo](https://github.com/ezsystems/ezplatform-demo): A demo website of eZ Platform, as an example for how to get started.
- [eZ Studio](https://github.com/ezsystems/ezstudio) A commercial product extending eZ Platform to provide features and services aimed at Editors, Editorial teams, and larger organizations.


## Overview of the Kernel

eZ Platform aims to be a set of reusable components, with a mix of decoupled and specific bundles putting it all together.
From a high level point of view, it contains a Front End / UI Layer, Mid/MVC layer and a Backend *(Repository)*. All layers contain further sub-layers consisting of smaller components.

This git repository contains the main parts of the MVC and Backend layers, with underlying components planned to be provided
as separate *(sub-tree split)* packages for re-usability. As is the case with [Solr Bundle](https://github.com/ezsystems/ezplatform-solr-search-engine).


### Current Organization

In the doc folder you'll find [Specifications](doc/specifications/) for most features, including the REST API.

MVC layer:
- [eZ/Bundle](eZ/Bundle/) is where you'll find bundles are that are important to expose the functionality of the Backend and MVC layer to Symfony.
- [eZ/Publish/Core/MVC](eZ/Publish/Core/MVC/) contains the parts that make up the different components extending Symfony.
- [eZ/Publish/Core/Pagination](eZ/Publish/Core/Pagination/) is a component extending PagerFanta for pagination of eZ Platform search queries.
- [eZ/Publish/Core/REST](eZ/Publish/Core/REST/) is a component providing REST server and *prototype* of a REST Client.

Backend:
- [eZ/Publish/API](eZ/Publish/API/) is where you'll find the definition of the stable interfaces for the PHP *Public* API, mainly Content *Repository API*.
- [eZ/Publish/SPI](eZ/Publish/SPI/)  SPI's are *Service Provider Interfaces*, *not yet frozen*.
- [eZ/Publish/Core](eZ/Publish/Core/) is where you'll find implementations of both APIs and SPIs; the naming aims to map to name of the interface they implement. For example, `Core\Persistence\Legacy` being implementation of `SPI\Persistence`.


### Testing Locally

This kernel contains a comprehensive set of unit, functional, and integration tests. At the time of writing, 9k unit tests, 8k integration tests, and several functional tests.

**Dependencies**
* **PHP 5 Modules**: php5\_intl php5\_xsl php5\_gd php5\_sqlite *(aka `pdo\_sqlite`)*
* **Database**: sqlite3, optionally: mysql/postgres *if so make sure to have relevant pdo modules installed*

For Contributing to this Bundle, you should make sure to run both unit and integration tests.

1. Set up this repository locally:

    ```bash
    # Note: Change the line below to the ssh format of your fork to create topic branches to propose as pull requests
    git clone https://github.com/ezsystems/ezpublish-kernel.git
    cd ezpublish-kernel
    composer install
    ```
2. Run unit tests:

    At this point you should be able to run unit tests:
    ```bash
    php -d memory_limit=-1 vendor/bin/phpunit
    ```

3. Run integration tests:

    ```bash
    # If you want to test against mysql or postgres instead of sqlite, define one of these with reference to an empty test db:
    # export DATABASE="mysql://root@localhost/$DB_NAME"
    # export DATABASE="pgsql://postgres@localhost/$DB_NAME"
    php -d memory_limit=-1 vendor/bin/phpunit -c phpunit-integration-legacy.xml
    ```

    To run integration tests against Solr, see https://github.com/ezsystems/ezplatform-solr-search-engine.

This should produce a result similar to this: [travis](https://travis-ci.org/ezsystems/ezpublish-kernel). If it doesn't, double-check [.travis.yml](.travis.yml) for up-to-date info on how travis is set up.

## Issue Tracker
Submitting bugs, improvements, and stories is possible on https://jira.ez.no/browse/EZP.
If you discover a security issue, please see how to responsibly report such issues on https://doc.ez.no/Security.

## Contributing
eZ Publish 5.x is a fully open source, community-driven project, and code contributions are simply done via GitHub pull requests.

Good manners:
* Remember to first create an issue in our issue tracker and refer to it in commits and pull request headers. For example:
  "Fix EZP-20104: ContentController should return error status when content is not found"
  or
  "Implement EZP-201xx: Add support for X in Y"
* If you want to contribute implementation-specification proposals, place them in the [doc/](doc/) folder.
* Keep different changes in different commits in case cherry-pick is preferred instead of a merge later.
  * A Pull Request should only cover one issue.
  * A commit should not contain code changes at the same time as doing coding standards/whitespace/typo fixes.
* TDD: Write/Change the test(s) for the change you make and commit it before you do the actual code change.
  * If a bug affects the Public API, write or enhance an integration test to make sure the bug is covered.
  * Unit tests should only use mocks/stubs and never test the full stack like integration tests do.
* Please test/check your commits before pushing even if we have automated checks in place on pull requests:
  * Run unit tests and integration tests before commits
  * Make sure you follow our [coding standards](https://github.com/ezsystems/ezcs)

For further information, please have a look at the [related guidance page](http://share.ez.no/get-involved/develop). You will, among other things, learn how to make pull requests. More on this here: ["How to contribute to eZ Publish using GIT"](http://share.ez.no/learn/ez-publish/how-to-contribute-to-ez-publish-using-git).

## Discussing/Exchanging
A dedicated forum has been set up to discuss all PHP API-related topics: http://share.ez.no/forums/new-php-api

## Copyright & License
Copyright (c) eZ Systems AS. For copyright and license details see provided LICENSE file.
