# Content download

> Author: bd@ez.no

## Summary
This feature covers downloading of Content binary files over HTTP.

It includes:
- generation of a download link from a BinaryFile / Media Field,
- permissions checking (the user must be allowed to read the Content)
- streaming of the file over HTTP

## Components

### Route

Path: `/content/download/{contentId}/{fieldIdentifier}/{filename}`

Example: `/content/download/68/file/My-file.pdf`

#### Arguments

- contentId

  ID of the Content the field is part of

- fieldIdentifier

  Field Definition identifier of the Binary / Media Field.

- filename

  Name of the file to send for download. Can be any valid file name.

#### Query parameters

- version (optional)

  The version number the file must be downloaded for. Requires the versionview permission.
  If not specified, the published version is used.

- inLanguage (optional)

  The language the file should be downloaded in.
  If not specified, the most prioritized language for the siteaccess will be used.

The controller action will load the content based on the content id, and identify the field using the identifier. The
binary file referenced by the Field Value will then be streamed, using the active IO Service.

It *should* also support HTTP caching, by making sure the proper headers are sent.

It *could* support resuming (a *must* for media files).

### Download link generation

#### PHP/Twig
The [Route Reference](https://doc.ez.no/display/EZP/RouteReference) mechanism will be used for this:

```twig
  {% set routeReference = ez_route( 'ez_content_download', {'content': content, 'fieldIdentifier': 'file' } ) %}
  <a href="{{ path( routeReference ) }}">{{ binary_file_field.fileName }}</a>
```

##### Arguments

The arguments are the same than the `ez_content_download` route.

The only difference is that instead of providing the `contentId`, the route reference expects a `content`, as an API
Content Value Object.

#### REST

> Story: EZP-24468

For various reasons (cache handling, layering), REST uses a special download URL, based on the fieldId:

```
/content/download/{contentId}/{fieldId}
```

Based on the fieldId, and independently from the siteaccess, it will use the language switcher's mechanisms, and redirect
to the relevant `ez_content_download` route.

This URL, with the HTTP post, is available via the `url` property of BinaryFile and Media fields.

## Backward compatibility

> Status: TODO

Since it is common practice to copy/save file download links, it is possible that a legacy link will be used on occasions.
This can be covered by adding a route that matches the legacy route, and redirects to the new route:

```
/content/download/123/45678/version/6/file/bc.pdf
```

would be redirected to

```
/content/download/123/file_field/bc.pdf?version=6
```


## Options

### IgorwFileServeBundle

> Status: considered, but not done
> https://github.com/igorw/IgorwFileServeBundle

A package meant to replace the BinaryResponse we currently use. Supports server-side mechanism such as X-SendFile, but
seems to be stalled a bit.
