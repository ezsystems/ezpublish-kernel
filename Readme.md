# eZ Platform Kernel
[![Build Status](https://img.shields.io/travis/ezsystems/ezpublish-kernel.svg?style=flat-square&branch=master)](https://travis-ci.org/ezsystems/ezpublish-kernel)
[![Downloads](https://img.shields.io/packagist/dt/ezsystems/ezpublish-kernel.svg?style=flat-square)](https://packagist.org/packages/ezsystems/ezpublish-kernel)
[![Latest version](https://img.shields.io/github/release/ezsystems/ezpublish-kernel.svg?style=flat-square)](https://github.com/ezsystems/ezpublish-kernel/releases)
[![License](https://img.shields.io/github/license/ezsystems/ezpublish-kernel.svg?style=flat-square)](LICENSE)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0885c0ce-4b9f-4b89-aa9c-e8f9f7a315e0/big.png)](https://insight.sensiolabs.com/projects/0885c0ce-4b9f-4b89-aa9c-e8f9f7a315e0)

Welcome to the *eZ Platform Kernel*. It is the heart of *eZ Platform*, a modern CMS built on top of the Symfony (Full Stack) Framework.
It contains an advanced Content Model, allowing you to structure any kind of content or content-like data in a future-proof Content Repository. eZ Platform Kernel also aims to provide additional features for the MVC layer (Symfony) to increase your productivity.

This code repository contains several layers of (and implementations of) APIs. *Kernel* refers to this being the core,
as opposed to the *Full Stack* which has bundles, user interfaces, and installers, all configured to make a complete application.

*This repository is for core development; for fixes, features and documentation of the eZ Platform Kernel itself.*


## What is eZ Platform?

*eZ Platform* is modern, self-sufficient CMS/CMF, and it can be used to meet needs of developers and editorial teams. It has been in development since 2011.
Current eZ Platform is the next generation of the product (previously named eZ Publish). It is built on top of the Symfony framework (Full Stack).

### Getting a Full Installation (*Full Stack*)

Reflecting what is described above there are several options to get a full install of this Kernel:

- [eZ Platform](https://github.com/ezsystems/ezplatform): For a clean install of eZ Platform, a modern Symfony CMS.
- [eZ Platform demo](https://github.com/ezsystems/ezplatform-demo): A demo website of eZ Platform, as an example for how to get started.
- [eZ Platform Enterprise Edition](https://github.com/ezsystems/ezplatform-ee): A commercial distribution of eZ Platform that provides additional features and services aimed at editors, editorial teams, and larger organizations.


## Overview of the Kernel

eZ Platform aims to be a set of reusable components, with a mix of decoupled and specific bundles putting it all together.
From a high level point of view, it contains a Front End / UI Layer, Mid/MVC layer and a Backend *(Repository)*. All layers contain further sub-layers consisting of smaller components.

This git repository contains the main parts of the MVC and Backend layers, with underlying components planned to be provided
as separate *(sub-tree split)* packages for re-usability. As is the case with [Solr Bundle](https://github.com/ezsystems/ezplatform-solr-search-engine).


### Current Organization

In the doc folder, you'll find [Specifications](doc/specifications/) for most features.

MVC layer:
- [eZ/Bundle](eZ/Bundle) - the bundles that are important to expose the functionality of the Backend and MVC layer to Symfony.
- [eZ/Publish/Core/MVC](eZ/Publish/Core/MVC) - the parts that make up the different components extending Symfony.
- [eZ/Publish/Core/Pagination](eZ/Publish/Core/Pagination) - a component extending PagerFanta for pagination of eZ Platform search queries.

Backend:
- [eZ/Publish/API](eZ/Publish/API/) - the definition of stable interfaces for the PHP *Public* API, mainly Content *Repository API*.
- [eZ/Publish/SPI/Persistence](eZ/Publish/SPI/Persistence/) - a layer which indeed is not frozen yet, meaning it might change in between releases. Those are persistence interfaces for Storage Engine. They can't be frozen yet, because we wouldn't be able to add features to the API (or rather it would be difficult). We need to improve this layer so we can actually give SPI BC promise on it.
- [eZ/Publish/SPI](eZ/Publish/SPI/)* - (anything other than Persistence) is frozen and has a Backward Compatibility promise of Service Provider Interface, meaning no breaking changes both from consumption and implementation POV.
- [eZ/Publish/Core](eZ/Publish/Core/) - implementations of both APIs and SPIs; the naming aims to map to name of the interface they implement. For example, `Core\Persistence\Legacy` being implementation of `SPI\Persistence`.


### Testing Locally

This kernel contains a comprehensive set of unit, functional, and integration tests. At the time of writing, 9k unit tests, 8k integration tests, and several functional tests.

**Dependencies**
* **PHP 7 Modules**: php7\_intl php7\_xsl php7\_gd php7\_sqlite *(aka `pdo\_sqlite`)*
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

    To run integration tests against Solr, see [Solr Search Engine Bundle for eZ Platform](https://github.com/ezsystems/ezplatform-solr-search-engine).

This should produce a result similar to this: [travis](https://travis-ci.org/ezsystems/ezpublish-kernel). If it doesn't, double-check [.travis.yml](.travis.yml) for up-to-date information on how travis is set up.

## Issue Tracker
Submitting bugs, improvements, and stories is possible in [https://jira.ez.no/browse/EZP](https://jira.ez.no/browse/EZP).
If you discover a security issue, please see how to responsibly report such issues in ["Reporting security issues in eZ Systems products"](https://doc.ezplatform.com/en/latest/guide/reporting_issues/#reporting-security-issues-in-ez-systems-products).

## Contributing
eZ Platform is an open source project, with code contributions made via GitHub pull requests by eZ Systems and the eZ Community.

Good manners:
* Remember to first create an issue in our issue tracker and refer to it in commits and pull request headers. For example:
  "EZP-20104: Fixed ContentController to return error status when content is not found"
  or
  "EZP-20105: Added support for X in Y"
* If you want to contribute implementation-specification proposals, place them in the [doc/](doc/) folder.
* Keep different changes in different commits in case cherry-pick is preferred instead of a merge later.
  * A pull request should only cover one issue.
  * A single commit should not contain code changes along with coding standards/whitespace/typo fixes.
* TDD: Write/Change the test(s) for your fix and commit it before you do the actual code change.
  * If a bug affects the Public API, write or enhance an integration test to make sure the bug is covered.
  * Unit tests should only use mocks/stubs and never test the full stack like integration tests do.
* Please test/check your commits before pushing even if we have automated checks in pull requests:
  * Run unit tests and integration tests before commits
  * Make sure you follow our [coding standards](https://github.com/ezsystems/ezplatform-code-style) by executing `composer fix-cs` before committing your changes to PHP files.

For further information, please have a look at the [related guidance page](https://doc.ezplatform.com/en/latest/community_resources/contributing). You will, among other things, learn how to make pull requests. More on this here: ["Contributing through git"](https://doc.ezplatform.com/en/latest/community_resources/documentation/#contributing-through-git).

## Discussing/Exchanging
A dedicated forum has been set up to discuss all PHP API-related topics: [eZ Community](https://login.ez.no/register?return=https://discuss.ezplatform.com).

## Copyright & License
Copyright (c) eZ Systems AS. For copyright and license details see provided LICENSE file.
