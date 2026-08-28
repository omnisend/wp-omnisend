#!/usr/bin/env bash

env=$1

case $env in
    "dev")
        domain=omnisend-dev.work
        snippet_domain=omnisrc-dev.work
        ;;
    "test")
        domain=omnisend.work
        snippet_domain=omnisrc.work
        ;;
    "prod")
        domain=omnisend.com
        snippet_domain=omnisnippet1.com
        ;;
    *)
        echo "pass one of these argument: dev,test,prod"
        exit 1
        ;;
esac

mkdir -p temp
rm -rf temp/omnisend
rm -f omnisend-$env.zip
cp -r omnisend temp/omnisend
rm -rf temp/omnisend/node_modules

# The asset version is the enqueued script's cache key, so it has to change with the rewritten bundles.
version_suffix="s/'version' => '\([^']*\)'/'version' => '\1-$env'/g"

if [[ "$OSTYPE" == "darwin"* ]]; then
    grep -rl "omnisend.com" temp/omnisend | xargs sed -i '' 's/omnisend\.com/'$domain'/g'
    grep -rl "omnisnippet1.com" temp/omnisend | xargs sed -i '' 's/omnisnippet1\.com/'$snippet_domain'/g'
    sed -i '' "$version_suffix" temp/omnisend/build/*.asset.php
else
    grep -rl "omnisend.com" temp/omnisend | xargs sed -i 's/omnisend\.com/'$domain'/g'
    grep -rl "omnisnippet1.com" temp/omnisend | xargs sed -i 's/omnisnippet1\.com/'$snippet_domain'/g'
    sed -i "$version_suffix" temp/omnisend/build/*.asset.php
fi

( cd temp ; zip -r ../omnisend-$env.zip omnisend )
rm -rf temp
