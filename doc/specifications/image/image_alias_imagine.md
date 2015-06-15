# Image alias generation using Imagine

## Description
Image alias generation is now using [LiipImagineBundle](https://github.com/liip/LiipImagineBundle), with underlying
[Imagine library from avalanche123](http://imagine.readthedocs.org/en/latest/).

This bundle allows to use either GD, Imagick or Gmagick PHP extensions and allows to define flexible filters in PHP.

Image variations are managed by the `IOService` and are completely independent from `ezimage` FieldType.
They are generated only once and cleared on demand (e.g. content removal).

## Configuration
Image variation (aka "Image alias") definition follows the same format as before, in `ezpublish.yml` or any imported
semantic configuration file.

```yaml
# Example
ezpublish:
    system:
        my_siteaccess:
            image_variations:
                small:
                    reference: null
                    filters:
                        - { name: geometry/scaledownonly, params: [100, 160] }
                medium:
                    reference: null
                    filters:
                        - { name: geometry/scaledownonly, params: [200, 290] }
                listitem:
                    reference: null
                    filters:
                        - { name: geometry/scaledownonly, params: [130, 190] }
                articleimage:
                    reference: null
                    filters:
                        - { name: geometry/scalewidth, params: [770] }
```

> **Important:** Each variation name **must be unique**. It may contain `_` or `-` or numbers, but no space.

* `reference`: Name of a reference variation to base the variation on.
  If `null` (or `~`, which means `null` un YAML), the variation will take the original image for reference.
  It can be any available variation configured in `ezpublish` namespace, or a `filter_set` defined in `liip_imagine` namespace.

* `filters`: array of filter definitions (hashes containing `name` and `params` keys). See possible values below.

### Available filters
In addition to [filters exposed by LiipImagineBundle](https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/configuration.rst),
the following are available:

| Filter name                  | Parameters                               | Description                                                                                        |
|:----------------------------:|:----------------------------------------:|:--------------------------------------------------------------------------------------------------:|
| geometry/scaledownonly       | [width, height]                          | Generates a thumbnail that will not exceed width/height.                                           |
| geometry/scalewidthdownonly  | [width]                                  | Generates a thumbnail that will not exceed width.                                                  |
| geometry/scaleheightdownonly | [height]                                 | Generates a thumbnail that will not exceed height.                                                 |
| geometry/scalewidth          | [width]                                  | Alters image width.   Proportion will be kept.                                                     |
| geometry/scaleheight         | [height]                                 | Alters image height.  Proportion will be kept.                                                     |
| geometry/scale               | [width, height]                          | Alters image size, not exceeding provided width and height.  Proportion will be kept.              |
| geometry/scaleexact          | [width, height]                          | Alters image size to fit exactly provided width and height.  Proportion will not be kept.          |
| geometry/scalepercent        | [widthPercent, heightPercent]            | Scales width and height with provided percent values.  Proportion will not be kept.                |
| geometry/crop                | [width, height, startX, startY]          | Crops the image.  Result will have provided width/height, starting at provided startX/startY       |
| border                       | [thickBorderX, thickBorderY, color=#000] | Adds a border around the image. Thickness is defined in px. Color is "#000" by default.            |
| filter/noise                 | [radius=0]                               | Smooths the contours of an image (`imagick`/`gmagick` only). `radius` is in pixel.                 |
| filter/swirl                 | [degrees=60]                             | Swirls the pixels of the center of an image (`imagick`/`gmagick` only). `degrees` defaults to 60°. |
| resize                       | {size: [width, height]}                  | Simple resize filter (provided by LiipImagineBundle).                                              |
| colorspace/gray              | N/A                                      | Converts an image to grayscale.                                                                    |

> *Tip:* It is possible to combine filters from the list above to [the ones provided in LiipImagineBundle](https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/filters.rst)
and custom ones.

### Discarded filters
The following filters have been discarded due to incompatibility:

* `flatten`. Obsolete, images are automatically flattened.
* `bordercolor`
* `border/width`
* `colorspace/transparent`
* `colorspace`

### Custom filters
Please refer to [LiipImagineBundle documentation on custom filters](https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/filters.rst#load-your-custom-filters).
[Imagine library documentation](http://imagine.readthedocs.org/en/latest/) may also be useful ;-).

### Post-Processors
LiipImagineBundle supports [post-processors on image aliases](https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/filters.rst#post-processors).
It is possible to specify them in image alias configuration:

```yaml
ezpublish:
    system:
        my_siteaccess:
            image_variations:
                articleimage:
                    reference: null
                    filters:
                        - { name: geometry/scalewidth, params: [770] }
                    post_processors:
                        jpegoptim: {}
```

Please refer to [post-processors documentation in LiipImagineBundle](https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/filters.rst#post-processors) for details.

### Drivers
LiipImagineBundle supports GD (default), Imagick and GMagick PHP extensions and only work on image blobs (no command line tool is needed).
See the [bundle's documentation to learn more on that topic](https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/configuration.rst).

## Upgrade
* Instantiate `LiipImagineBundle` in your kernel class
* If you were using ImageMagick, please install [Imagick](http://php.net/imagick) or [Gmagick](http://php.net/gmagick) PHP extensions
  and activate the driver in `liip_imagine`:

  ```yaml
  # ezpublish.yml or config.yml
  liip_imagine:
      # Driver can either "imagick" or "gmagick", depending on the PHP extension you're using.
      driver: imagick
  ```
