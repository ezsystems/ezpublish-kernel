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

Path: `/content/download/{contentId}/{fieldId}/{filename}`

Example: `/content/download/68/64567/My-file.pdf`

#### Arguments

- contentId

  ID of the Content the field is part of

- fieldId

  Field ID of the Binary / Media Field.

- filename

  Name of the file to send for download. Can be any valid file name, but defaults to the Field's value.

#### Query parameters

- version (optional)

  The version number the file must be downloaded for. Requires the versionview permission.
  If not specified, the published version is used.

The controller action will load the content based on the content and field id. The
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

The arguments are the `Content` and the Field Definition Identifier.

#### REST
An extra attribute will be added to Fields of BinaryFile/Media type: `downloadUri`. It will contain the download uri for
the Field's contents.

## Backward compatibility

> Not implemented yet

Since it is common practice to copy/save file download links, it is possible that a legacy link will be used on occasions.
This can be covered by adding a route that matches the legacy route, and redirects to the new route:

```
/content/download/123/45678/version/6/file/bc.pdf
```

would be redirected to

```
/content/download/123/45678/bc.pdf?version=6
```
