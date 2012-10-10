#API
Welcome to the new eZ Publish API, this code repository contains several layers of API, but this document will for the most part focus on the Public API.

##What is the Public API
The public API will give you an easy access to the content repository of eZ Publish. The content repository is the core component which manages content, locations (former Nodes), sections, content types (former Content Classes), user groups, users and roles. It also provides a new clear interface for plugging in custom field types (former Datatypes).

The public API is built on top of a layered architecture including a new persistence layer for abstracting the storage functionality. By using the public API your applications will be forward compatible to future releases based on enhanced, more scalable and more performant storage engines. Applications based on the public API are also fully backwards compatible by using the included storage engine based on the current kernel and database model ("Legacy" storage engine).

## Alpha Notice
The API is still very much work in progress, and so is documentation, hence why this is currently labeled as a Developer Preview. But expect both parts to get into Beta shape as we close in on the launch of Etna, and fully stable as the core of Kilimanjaro (Q4 2012). But contribution is very open today, go for it !

##Directory Layout
* [eZ/Publish/API](/ezsystems/ezp-next/tree/master/eZ/Publish/API/)  *Public API Interface (interfaces eZ Publish implements)*
* [eZ/Publish/API/Repository](/ezsystems/ezp-next/tree/master/eZ/Publish/API/Repository/)  *Public API Repository Interfaces*
* [eZ/Publish/API/Repository/Examples](/ezsystems/ezp-next/tree/master/eZ/Publish/API/Repository/Examples/)  *Examples of Public API use*

* [eZ/Publish/SPI](/ezsystems/ezp-next/tree/master/eZ/Publish/SPI/)  *Service provider interfaces (interfaces extensions can implement)*
* [eZ/Publish/SPI/Persistence](/ezsystems/ezp-next/tree/master/eZ/Publish/SPI/Persistence/)  *Persistence Interface (private api for now)*
* [eZ/Publish/SPI/IO](/ezsystems/ezp-next/tree/master/eZ/Publish/SPI/IO/)  *Io (file) Interface (private api for now)*

* [eZ/Publish/Core/Base](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Base/)  *Common code needed by Core parts bellow*
* [eZ/Publish/Core/Repository](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Repository/)  *Public API Repository implementation*
* [eZ/Publish/Core/Persistence/Legacy](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Persistence/Legacy/)  *Legacy Storage-Engine aka Persistence-handler*
* [eZ/Publish/Core/Persistence/InMemory](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/Persistence/InMemory/)  *InMemory  Storage-Engine aka Persistence-handler (for unit testing)*
* [eZ/Publish/Core/IO/LegacyHandler.php](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/IO/)  *Legacy Io (file) Handler*
* [eZ/Publish/Core/IO/InMemoryHandler.php](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/IO/)  *InMemory Io handler (for unit testing)*

* [eZ/Publish/Core/MVC/Symfony](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/MVC/Symfony/)  *MVC components that integrate with Symfony*
* [eZ/Publish/Core/MVC/Legacy](/ezsystems/ezp-next/tree/master/eZ/Publish/Core/MVC/Legacy/)  *eZ Publish Legacy components integration*
* [eZ/Bundle](/ezsystems/ezp-next/tree/master/eZ/Bundle/)  *Bundles that wrap eZ Publish components into Symfony*

* [design/](/ezsystems/ezp-next/tree/master/design/)  *Early uml class diagrams*
* [doc/](/ezsystems/ezp-next/tree/master/doc/)  *Placeholder for bc doc and other doc that can not be on wiki or inline*
* config.php-DEVELOPMENT  *Default config file for development use*
* phpunit.xml  *PHPUnit 3.6+ xml configuration*
* Readme.md  *This text*
* bootstrap.php  *System Bootstrap*

##Dependencies
* **Composer**: Just run `curl -s http://getcomposer.org/installer | php` to get **composer.phar**
* **PHPUnit 3.6**
* **PHP 5 Modules**: php5_sqlite
* **Database**: sqlite3 if not installed by above stage
* **eZ Publish 4 (aka *legacy*)**: Get the latest sources from [eZ Publish 4](https://github.com/ezsystems/ezpublish/).
  Rather clone it or directly [download the zip package from Github](https://github.com/ezsystems/ezpublish/zipball/master).

##How to get started
* Clone this repo
* Install dependencies with **Composer**: `php composer.phar install`
* Copy config.php-DEVELOPMENT to config.php
* Modify `config.php` and adjust the path to eZ Publish Legacy root
* Execute `php index.php` (cli) to verify that it manages to output some text
* Execute `phpunit` to see current status of missing tests / implementations

##Bug tracker
Submitting bug reports is possible on http://issues.ez.no/ezpublish (pick the "ezp-next" component in the right column when reporting).

##Contributing
eZ Publish API is a fully open source, community-driven project. If you''d like to contribute, please have a look at the [related guidance page](http://share.ez.no/get-involved/develop). You will, amongst other, learn how to make pull-requests. More on this here : ["How to contribute to eZ Publish using GIT"](http://share.ez.no/learn/ez-publish/how-to-contribute-to-ez-publish-using-git).

##Discussing/Exchanging##
A dedicated forum has been set-up to discuss all PHP API-related topics : http://share.ez.no/forums/new-php-api

##Copyright & license
eZ Systems AS & GPL 2.0
