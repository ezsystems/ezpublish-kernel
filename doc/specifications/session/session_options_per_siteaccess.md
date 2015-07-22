# Session options per SiteAccess

## Description
Symfony offers the possibility to change many session options, such as:

* cookie_domain
* cookie_path
* cookie_lifetime
* cookie_secure
* cookie_httponly

The problem is that, prior to eZ Publish 5.3, one could define these options at the application level only
(i.e. in Symfony `framework` configuration).
The consequence is that it makes it impossible to define them per SiteAccess, which can be annoying when working
on a multi-site application, with URI-based SiteAccess matching.

## Solution
Per SiteAccess session options have been defined, removing this limitation.

## Usage
Example:

*ezpublish.yml*
```yaml
ezpublish:
    system:
        my_siteaccess:
            session:
                name: my_session_name
                cookie_domain: mydomain.com
                cookie_path: /foo
                cookie_lifetime: 86400
                cookie_secure: false
                cookie_httponly: true
```

> **Note**:
> Session options are all optional.
> If not defined, it will fallback to those defined in Symfony `framework` configuration,
> which themselves fallback to default PHP ones (from `php.ini`).

## Deprecations & Upgrading
`ezpublish.system.<siteAccessName>.session_name` has been deprecated for defining session name.
You now need to use `ezpublish.system.<siteAccessName>.session.name`.

*Before*:
```yaml
ezpublish:
    system:
        my_siteaccess:
            session_name: SomeSessionName
```

*After*:
```yaml
ezpublish:
    system:
        my_siteaccess:
            session:
                name: SomeSessionName
```
