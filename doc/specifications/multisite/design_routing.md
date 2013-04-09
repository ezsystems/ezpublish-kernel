# Multi site routing

## Introduction
Goal of this document is to design how to make it possible to have **multiple *content pools* inside a single content repository**.
This would give the possibility to build several websites which content is actually a *subtree* in the content repository.

This feature was already present in **eZ Publish 3.x/4.x** through the `PathPrefix`, `RootNode` and `IndexPage` settings.
The initial idea was to define a prefix to *hide* from the start of the URLAlias.
The main issue was that it was URI based, so if the URLAlias changed, the setting was obsolete.

This document explains how this feature is implemented in eZ Publish 5.1+.


## Configuration


## Routing


## Link generation