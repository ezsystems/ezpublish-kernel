# eZ Publish legacy extension support in bundles

> Audience: integrators, extension developers
> Author: Bertrand Dunogier <bertrand.dunogier@ez.no>
> Created: 22/01/2014
> JIRA story: https://jira.ez.no/browse/EZP-22210
> Topics: BC, Extensibility

## Use-cases
### Custom extension
The developer of a custom extension, like a fieldtype, wants to make his extension available via composer, and add preliminary new stack support to it. He creates a new bundle for his extension, and copies all of its contents to `Resources/ezpublish-legacy`.
Anyone can install his legacy extension by requiring it from composer.json. The custom install script will link it into his legacy extensions folder, and enable it when the bundle is enabled.

### Website project configuration
A project's maintainer wants to gather as much as possible of his project elements into one place.
Any setting that isn't mapped by the semantical configuration can be overridden using the `Resources/ezpublish-legacy` folder, using the standard `settings/override` and `settings/siteaccess folder`. Custom legacy templates can also be created here, for instance to override a couple backoffice elements.

### Dual-kernel extension
A developer needs a custom fieldtype.
Since there is no backoffice yet, a couple legacy elements are still required (datatype class, edit/view templates, settings.
Using a Bundle, the developer can have both the new stack and legacy code in the same structure, and make sure both evolve at the same rythm:
- `Acme/Bundle/AcmeBundle/ezpublish_legacy` (default) contains the legacy datatype elements
- `Acme/Bundle/AcmeBundle/eZ/FieldType` contains the new stack implementation

## Summary
Make it possible to ship legacy extensions in a Symfony 2 bundle.

### Benefits
- development can be made without actually going inside the ezpublish_legacy folder
- versioning and deployment is easier, since this folder can be created in the project's bundle
- makes up for the non-injection/mapping of a huge part of the legacy configuration by making it visible from the new stack structure
- legacy extensions can very easily be bundled as eZ Publish 5 bundles without changing a single line of code
- the legacy (backoffice) counterpart of new stack extensions can be bundled together with the new stack code, and automatically installed using composer

## Technical approach
- a symfony script, executed on post-update by composer, symlinks (works on windows with PHP > 5.3 as well) the legacy folder to
  `ezpublish_legacy/extensions`, using the lowercased name of the bundle as the symlink name (configurable):
  `extension/ezdemo -> ../../vendor/ezsystems/demobundle/EzSystems/DemoBundle/ezpublish-legacy`
- the extension is injected into `site.ini/ExtensionSettings/ActiveExtensions` when the container is compiled.

## Implementation
- The bundle's Extension must implement the `EzPublishLegacyExtension` interface
- The symfony script scans all registered bundles, and if the bundle's extension implements `EzPublishLegacyExtension`,
  it symlinks the folders contained in the `ezpublish_legacy` bundle's folder to `ezpublish_legacy/extensions`.

## Open questions
- What settings can *not* be overridden this way ?

