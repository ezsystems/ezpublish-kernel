#!/usr/bin/env sh
echo 'Translation extraction';
cd ../../..;
# Extract string for default locale
echo '# Extract Kernel : EzPublishCoreBundle';
./app/console translation:extract en -v \
  --dir=./vendor/ezsystems/ezpublish-kernel/eZ \
  --exclude-dir=Bundle/PlatformBehatBundle \
  --exclude-dir=Bundle/EzPublishCoreBundle/Tests \
  --exclude-dir=Bundle/EzPublishCoreBundle/Features \
  --exclude-dir=Bundle/EzPublishDebugBundle/Tests \
  --exclude-dir=Bundle/EzPublishIOBundle/Tests \
  --exclude-dir=Bundle/EzPublishRestBundle/Tests \
  --exclude-dir=Bundle/EzPublishRestBundle/Features \
  --exclude-dir=Publish/API/Repository/Tests \
  --exclude-dir=Publish/Core/REST/Client \
  --exclude-dir=Publish/Core/Base/Tests \
  --exclude-dir=Publish/Core/FieldType/Tests \
  --exclude-dir=Publish/Core/Helper/Tests \
  --exclude-dir=Publish/Core/IO/Tests \
  --exclude-dir=Publish/Core/Limitation/Tests \
  --exclude-dir=Publish/Core/Pagination/Tests \
  --exclude-dir=Publish/Core/Persistence/Tests \
  --exclude-dir=Publish/Core/Persistence/Legacy/Tests \
  --exclude-dir=Publish/Core/Repository/Tests \
  --exclude-dir=Publish/Core/REST/Tests \
  --exclude-dir=Publish/Core/Search/Tests \
  --exclude-dir=Publish/Core/Search/Legacy/Tests \
  --exclude-dir=Publish/Core/SignalSlot/Tests \
  --exclude-dir=Publish/Core/settings/tests \
  --exclude-dir=Publish/SPI/Tests \
  --output-dir=./vendor/ezsystems/ezpublish-kernel/eZ/Bundle/EzPublishCoreBundle/Resources/translations \
  --enable-extractor=ez_fieldtypes \
  --keep
  "$@"

echo '# Clean file references';
sed -i "s|/../../../../.././vendor/ezsystems/ezpublish-kernel/|/|g" ./vendor/ezsystems/ezpublish-kernel/eZ/Bundle/EzPublishCoreBundle/Resources/translations/*.xlf

echo 'Translation extraction done';