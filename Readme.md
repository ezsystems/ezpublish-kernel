# eZ Platform Kernel / eZ Publish 5.x Platform Kernel
[![Build Status](https://travis-ci.org/ezsystems/ezpublish-kernel.png?branch=master)](https://travis-ci.org/ezsystems/ezpublish-kernel)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0885c0ce-4b9f-4b89-aa9c-e8f9f7a315e0/big.png)](https://insight.sensiolabs.com/projects/0885c0ce-4b9f-4b89-aa9c-e8f9f7a315e0)

Welcome to the *eZ Platform Kernel* (also known as *eZ Publish 5.x Platform Kernel*), the hearth of *eZ Platform*, a modern
CMS built on top of Symfony (Full Stack) Framework.
Containing an advance Content Model it allows you to structure any kind of content, or content-like data in a future-proof Content Repository.
It furthermore aims to provide additional features for the MVC layer (Symfony), to increase your productivity.

This code repository contains several layers of API's and implementation of them. *Kernel* refers to this being the core,
and not the *Full Stack* with bundles, user interfaces and installers all configured to make a complete application.

*In other words this repo is for core development, for fixes, features and documentation of the Platform kernel itself.*


## eZ Platform?

eZ Platform is a bottom up rewrite of eZ Publish, so a conservative approach was taken on backwards compatibility
by first introducing it in 2012 as *eZ Publish Platform 5.0*, which bundled a *Legacy* ("4.x") Stack & a *Platform* (formerly 5.x, or "new")
Stack together. Starting 2015 with *eZ Platform* this is no longer the case, the *Platform* has matured to become its own
modern, self sufficient CMS/CMF, and can be used to solve your needs without having to also deal with *Legacy*.

### Getting a full install (*Full Stack*)

Reflecting what is described above there are several options to get a full install of this Kernel, see:

- [eZ Platform](https://github.com/ezsystems/ezplatform): For the new eZ Platform install.
- eZ Studio: *Coming soon* A commercial product extending eZ Platform to provide features and services aimed at Editors, Editorial teams and larger organizations.
- [eZ Publish 5.x](https://github.com/ezsystems/ezpublish-community): If you are upgrading from eZ Publish 4.x or 5.x and still require *legacy* functionality.


## Overview of the Kernel

eZ Platform is aimed at being a set of reusable components, with a mix of decoupled and specific bundles putting it all together.
From a high level point of view, it contains a Front End / UI Layer, Mid/MVC layer and a Backend, all layers containing further
sub-layers consisting of smaller components.

This repository contains the main parts of the MVC and Backend layers, with underlying components planned to be provided
as separate *(sub-tree split)* packages for re-usability.


### Current organization

In the doc folder you'll find [Specifications](doc/specifications/) for most features, including the REST API.

MVC layer:
- [eZ/Bundle](eZ/Bundle/) is where you'll find bundles are that are important to expose the functionality of the Backend and MVC layer to Symfony.
- [eZ/Publish/Core/MVC](eZ/Publish/Core/MVC/) contains the parts that will make up the different components extending Symfony.
- [eZ/Publish/Core/Pagination](eZ/Publish/Core/Pagination/) component extending PagerFanta for pagination of eZ Platform search queries.
- [eZ/Publish/Core/REST](eZ/Publish/Core/REST/) component providing REST server and *prototype* of a REST Client.

Backend:
- [eZ/Publish/API](eZ/Publish/API/) is where you'll find the definition of the stable interfaces for the PHP *Public* API, mainly Content *Repository API*.
- [eZ/Publish/SPI](eZ/Publish/SPI/)  SPI's are *Service Provider Interfaces*, *not yet frozen*.
- [eZ/Publish/Core](eZ/Publish/Core/) is where you'll find implementations of both API and SPI's; the naming aims to map to name of interface they implement, example Search/<implementation>.


## How to run tests

### Dependencies
* **PHP 5 Modules**: php5\_intl php5\_xsl php5\_gd php5\_sqlite
* **Database**: sqlite3

You can also run tests (slower) on mysql or postgres, see [.travis.yml](.travis.yml) for how.

### Installation
* Clone this repo `git clone https://github.com/ezsystems/ezpublish-kernel.git`
* Enter directory `cd ezpublish-kernel`
* Get [Composer](http://getcomposer.org/download/) using curl `curl -s http://getcomposer.org/installer | php`
* Install dev dependencies: `php composer.phar install --prefer-dist --dev`
* Execute `php vendor/bin/phpunit -vc phpunit*.xml` with one of the phpunit configuration files, mainly:
  * phpunit.xml  *unit test xml configuration*, default one if -c is omitted from the phpunit command.
  * phpunit-integration-legacy.xml  *integration test xml configuration for running integration tests with Legacy Storage engine*

This should produce similar result as [travis](https://travis-ci.org/ezsystems/ezpublish-kernel).
If it doesn't, double check [.travis.yml](.travis.yml) for up-to-date info on how travis is setup.


## Issue tracker
Submitting bugs, improvements and stories is possible on https://jira.ez.no/browse/EZP.
If you discover a security issue, please see how to responsibly report such issues on https://doc.ez.no/Security.

## Contributing
eZ Publish 5.x is a fully open source, community-driven project, and code contributions are simply done via github pull requests.

Good manners:
* Remember to first create an issue in our issue tracker and refer to it in commits and pull requests headers, example:
  "Fix EZP-20104: ContentController should return error status when content is not found"
  or
  "Implement EZP-201xx: Add support for X in Y"
* If you want to contribute implementation specification proposals, place them in [doc/](doc/) folder.
* Keep different changes in different commits in case cherry-pick is preferred instead of a merge later.
  * A Pull Request should only cover one issue.
  * A commit should not contain code changes at the same time as doing coding standards/whitespace/typo fixes.
* TDD: Write/Change the test(s) for the change you do and commit it before you do the actual code change.
  * If a bug affects Public API, write or enhance a integration test to make sure the bug is covered.
  * Unit tests should only use mocks/stubs and never test the full stack like integrations tests do.
* Please test/check your commits before pushing even if we have automated checks in place on pull requests:
  * Run unit tests and integration test before commits
  * Make sure you follow our [coding standards](https://github.com/ezsystems/ezcs)

For further information please have a look at the [related guidance page](http://share.ez.no/get-involved/develop). You will, amongst other, learn how to make pull-requests. More on this here: ["How to contribute to eZ Publish using GIT"](http://share.ez.no/learn/ez-publish/how-to-contribute-to-ez-publish-using-git).

## Discussing/Exchanging
A dedicated forum has been set-up to discuss all PHP API-related topics : http://share.ez.no/forums/new-php-api

## Copyright & license
Copyright eZ Systems AS, for copyright and license details see provided LICENSE file.
