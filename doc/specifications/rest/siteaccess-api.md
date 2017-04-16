# Siteaccess REST API

## List all siteaccesses

> Resource: `/site/siteaccess`
> Verb: `GET`
> Content-Type: `application/vnd.ez.api.SiteaccessList+(json|xml)`

### Example

#### Request
```
GET /api/ezp/v2/site/siteaccess HTTP/1.1
Host: api.example.net
Accept: application/vnd.ez.api.SiteaccessList+xml
```

#### Response
```
HTTP/1.1 200 OK
Content-Type: application/vnd.ez.api.SiteaccessList+xml
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SiteaccessList media-type="application/vnd.ez.api.SiteaccessList+xml" href="/api/ezp/v2/site/siteaccess">
  <siteaccess>
    <name>site_en</name>
    <uriPrefix>/en/</uriPrefix>
  </siteaccess>
  <siteaccess>
    <name>site_fr</name>
    <uriPrefix>/en/</uriPrefix>
  </siteaccess>
  <siteaccess>
    <name>site_admin</name>
    <uriPrefix>http://example.com/admin/</uriPrefix>
  </siteaccess>
</SiteaccessList>
```

### Permissions
Only siteaccess for which the user has the user/login policy will be shown.
