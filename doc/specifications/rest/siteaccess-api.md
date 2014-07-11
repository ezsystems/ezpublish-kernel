# Siteaccess REST API

## Summary
| Resource                        | GET                                   |POST|PUT|DELETE|
|---------------------------------|---------------------------------------|----|---|------|
|`/siteaccess`                    |List API resources                     |N/A |N/A|N/A   |
|`/siteaccess/siteaccesses`       |List all siteaccesses                  |N/A |N/A|N/A   |
|`/siteaccess/siteaccesses/<name>`|Details about a siteaccess             |N/A |N/A|N/A   |
|`/siteaccess/groups`             |List available siteaccess groups       |N/A |N/A|N/A   |
|`/siteaccess/groups/<name>`      |Details about a siteaccess group       |N/A |N/A|N/A   |

## List API resources
> Resource: `/siteaccess`
> Verb: GET
> Content-Type: `application/vnd.ez.api.SiteaccessRoot+(json|xml)`

### Description
Lists all resources of the Siteaccess API.

### Example
#### Request
```
GET /api/ezp/v2/siteaccess HTTP/1.1
Host: api.example.net
Accept: application/vnd.ez.api.SiteaccessRoot+xml
```
#### Response
```
HTTP/1.1 200 OK
Content-Type: application/vnd.ez.api.SiteaccessRoot+xml
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SiteaccessRoot media-type="application/vnd.ez.api.SiteaccessRoot+xml">
    <siteaccessList media-type="application/vnd.ez.api.SiteaccessList+xml" href="/api/ezp/v2/siteaccess/siteaccesses" />
    <siteaccessGroupList media-type="application/vnd.ez.api.SiteaccessGroupList+xml" href="/api/ezp/v2/siteaccess/groups" />
</SiteaccessRoot>
```

## List all siteaccesses
> Resource: `/siteaccess/siteaccesses`
> Verb: GET
> Content-Type: `application/vnd.ez.api.SiteaccessList+(json|xml)

### Description
Lists all resources of the Siteaccess API.

### Example

#### Request
```
GET /api/ezp/v2/siteaccess/siteaccesses HTTP/1.1
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
<SiteaccessList media-type="application/vnd.ez.api.SiteaccessList+xml" href="/api/ezp/v2/siteaccess/siteaccesses">
  <siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ez/v2/siteaccess/siteaccesses/site_en" />
  <siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ez/v2/siteaccess/siteaccesses/site_fr" />
  <siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ez/v2/siteaccess/siteaccesses/site_admin" />
</SiteaccessRoot>
```

## Details about a siteaccess
> Resource: `/siteaccess/siteaccesses/<name>`
> Verb: GET
> Content-Type: `application/vnd.ez.api.Siteaccess+(json|xml)`

### Description
Details about the siteaccess `<name>`

### Example

#### Request
```
GET /api/ezp/v2/siteaccess/siteaccesses/site_en HTTP/1.1
Host: api.example.net
Accept: application/vnd.ez.api.Siteaccess+xml
```

#### Response
```
HTTP/1.1 200 OK
Content-Type: application/vnd.ez.api.Siteaccess+xml
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ezp/v2/siteaccess/siteaccesses/site_en">
  <name>site_en</name>
  <matchingType></matchingType>
  <uri></uri>
</Siteaccess>
```

## List available siteaccess groups
> Resource: `/siteaccess/groups`
> Verb: GET
> Content-Type: `application/vnd.ez.api.SiteaccessGroupList+(json|xml)`

### Description
Lists available siteaccess groups.

### Example
#### Request
```
GET /api/ezp/v2/siteaccess/groups HTTP/1.1
Host: api.example.net
Accept: application/vnd.ez.api.SiteaccessGroupList+xml
```
#### Response
```
HTTP/1.1 200 OK
Content-Type: application/vnd.ez.api.SiteaccessGroupList+xml
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SiteaccessGroupList media-type="application/vnd.ez.api.SiteaccessGroupList+xml" href="/api/ezp/v2/siteaccess/groups" >
    <siteaccessGroup media-type="application/vnd.ez.api.SiteaccessGroup+xml" href="/api/ezp/v2/siteaccess/groups/frontend" />
    <siteaccessGroup media-type="application/vnd.ez.api.SiteaccessGroup+xml" href="/api/ezp/v2/siteaccess/groups/backend" />
</SiteaccessRoot>
```

## Details about a siteaccess group
> Resource: `/siteaccess/groups/<name>`
> Verb: GET
> Content-Type: `application/vnd.ez.api.SiteaccessGroup+(json|xml)`

### Description
Details about a siteaccess groups.

### Example
#### Request
```
GET /api/ezp/v2/siteaccess/groups/frontend HTTP/1.1
Host: api.example.net
Accept: application/vnd.ez.api.SiteaccessGroup+xml
```
#### Response
```
HTTP/1.1 200 OK
Content-Type: application/vnd.ez.api.SiteaccessGroup+xml
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<SiteaccessGroup media-type="application/vnd.ez.api.SiteaccessGroup+xml" href="/api/ezp/v2/siteaccess/groups/frontend" >
    <name>frontend</name>
    <siteaccesList media-type="application/vnd.ez.api.SiteaccessList+xml">
      <siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ez/v2/siteaccess/siteaccesses/site_en" />
      <siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ez/v2/siteaccess/siteaccesses/site_fr" />
      <siteaccess media-type="application/vnd.ez.api.Siteaccess+xml" href="/api/ez/v2/siteaccess/siteaccesses/site_admin" />
    </siteaccessList>
</SiteaccessRoot>
```

## Open questions
- Why would we include something the matcher ? What can it be used for ?
- How are Compound matchers handled in reverse ?
- The details view could be replaced with a filter on the siteaccess list: /siteaccess/siteaccesses?name=site_en. This would avoid the details view, could be referenced from the group list, and would require way less requests.
