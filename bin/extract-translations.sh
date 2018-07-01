#!/usr/bin/env sh
echo 'Translation extraction';
cd ../../..;
# Extract string for default locale
echo '# Extract Kernel : EzPublishCoreBundle';
./bin/console translation:extract en -v \
  --dir=./vendor/ezsystems/ezpublish-kernel/eZ \
  --exclude-dir=Bundle/PlatformBehatBundle \
  --exclude-dir=Tests \
  --exclude-dir=Features \
  --exclude-dir=Publish/Core/REST/Client \
  --exclude-dir=tests \
  --output-dir=./vendor/ezsystems/ezpublish-kernel/eZ/Bundle/EzPublishCoreBundle/Resources/translations \
  --enable-extractor=ez_fieldtypes \
  --keep
  "$@"

echo '# Clean file references';
sed -i "s|>.*/vendor/ezsystems/ezpublish-kernel/|>|g" ./vendor/ezsystems/ezpublish-kernel/eZ/Bundle/EzPublishCoreBundle/Resources/translations/*.xlf

cd vendor/ezsystems/ezpublish-kernel;
echo 'Translation extraction done';
