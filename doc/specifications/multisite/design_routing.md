# Multi site routing

## Introduction
Goal of this document is to design how to make it possible to have **multiple *content pools* inside a single content repository**.
This would give the possibility to build several websites which content is actually a *subtree* in the content repository.

This feature was already present in **eZ Publish 3.x/4.x** through the `PathPrefix`, `RootNode` and `IndexPage` settings.
The initial idea was to define a prefix to *hide* from the start of the URLAlias.
The main issue was that it was URI based, so if the URLAlias changed, the setting was obsolete.
It was also possible to define prefixes to exclude via the `PathPrefixExclude` setting.

This document explains how this feature is implemented in eZ Publish 5.1+.

## Concept
The main idea is to be able to define a **root location** for a given site, by its `locationId`.
This will will define in one go the *homepage* and the *path prefix* for the site.

It will still be possible to define exclusion for the *path prefix*, giving the possibility to make links to content outside the site subtree (e.g. *Media*, shared content between sites...).


## Configuration
End user configuration would look like:

```yaml
ezpublish:
    system:
        my_siteaccess:
            content:
                tree_root:
                    # Root locationId. Default is top locationId
                    location_id: 123
                    # Every URL aliases starting with those prefixes will be considered 
                    # being outside of the subtree starting at root_location.
                    # Default value is an empty array.
                    # Prefixes are not case sensitive.
                    excluded_uri_prefixes:
                        - media
                        - users
```

## Routing & link generation
> **Note:** Only concerns the `UrlAliasRouter`

Defining a root location for the content subtree only affects routing and link generation.
In this regard, the root location will transparently add/remove a prefix to the requested URL alias.

> When generating a link to a location which is outside the content subtree, the URLAlias will be left as is.  
> **A warning will be logged** as this is most likely due to a developer or content editor mistake.
>
> Note that no warning will be logged if the URLAlias is affected by one of the `excluded_uri_prefixes`.

It is also possible to exclude some URI prefixes from this behavior (see `excluded_uri_prefixes`), 
a typical use case being 2 websites sharing the same product catalog.

### Example
Given the following content subtree:

```
.
├── website1
│   └── category1
│       └── my-cool-article
├── website2
│    └── the-truth-is-out-there
└── products
    ├── product1
    └── product2
```

* Without defining a root location,
    * `my-cool-article` will be accessible with `/website1/category1/my-cool-article` URI.
    * Links to `my-cool-article` will point to `/website1/category1/my-cool-article`.
* Defining `website1` as root location, 
    * `my-cool-article` will be accessible with `/category1/my-cool-article` URI.
    * Links to `my-cool-article` will point to `/category1/my-cool-article`.
    * Without defining `products` in `excluded_uri_prefixes`,
        * `product1` won't be accessible, as it's not under `website1` subtree.
    * Defining `products` in `excluded_uri_prefixes`,
        * `product1` will be accessible with `/products/product1` URI.

