#!/usr/bin/env sh
echo 'Translation extraction';
cd ../../..;
# Extract string for default locale
echo '# Extract Kernel : EzPublishCoreBundle';
./bin/console translation:extract en -v \
  --dir=./vendor/ezsystems/ezplatform-kernel/eZ \
  --exclude-dir=Tests \
  --exclude-dir=Features \
  --exclude-dir=tests \
  --output-dir=./vendor/ezsystems/ezplatform-kernel/eZ/Bundle/EzPublishCoreBundle/Resources/translations \
  --enable-extractor=ez_fieldtypes \
  --keep
  "$@"

echo '# Clean file references';
sed -i "s|>.*/vendor/ezsystems/ezplatform-kernel/|>|g" ./vendor/ezsystems/ezplatform-kernel/eZ/Bundle/EzPublishCoreBundle/Resources/translations/*.xlf

cd vendor/ezsystems/ezplatform-kernel;
echo 'Translation extraction done';
