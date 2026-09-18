#! /bin/bash

MW_BRANCH=$1
EXTENSION_NAME=$2

# Clone rather than download the tarball: the test runner needs
# phpunit.xml.template, which is marked export-ignore and therefore
# absent from tarballs.
git clone --depth=1 --branch="$MW_BRANCH" https://github.com/wikimedia/mediawiki.git mediawiki

cd mediawiki

# Composer 2.10+ refuses to install dependency versions flagged by
# security advisories. This is a throwaway CI install, torn down after
# this one run -- allow them here rather than pin around them.
php -r '$f = "composer.json"; $c = json_decode( file_get_contents( $f ), true ); $c["config"]["policy"]["advisories"]["block"] = false; file_put_contents( $f, json_encode( $c, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" );'

# Wikibase's newer branches can pull in beta/dev stability dependencies.
# prefer-stable keeps everything that has a stable release on its
# stable release.
composer config minimum-stability dev
composer config prefer-stable true

composer install
php maintenance/install.php --dbtype sqlite --dbuser root --dbname mw --dbpath $(pwd) --pass AdminPassword WikiName AdminUser

echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
echo 'ini_set("display_errors", 1);' >> LocalSettings.php
echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
echo '$wgShowDBErrorBacktrace = true;' >> LocalSettings.php
echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
echo '$wgServer = "http://localhost";' >> LocalSettings.php
echo '$wgDeprecationReleaseLimit = "1.33";' >> LocalSettings.php

echo '$wgEnableWikibaseRepo = true;' >> LocalSettings.php
echo '$wgEnableWikibaseClient = false;' >> LocalSettings.php

# WikibaseBoolean's own extension.json requires MediaWiki >= 1.43.0, so
# unlike the real precedent this is adapted from, there's no need to
# branch on older, pre-wfLoadExtension-for-Wikibase MediaWiki versions.
echo 'wfLoadExtension( "WikibaseRepository", __DIR__ . "/extensions/Wikibase/extension-repo.json" );' >> LocalSettings.php
echo 'require_once __DIR__ . "/extensions/Wikibase/repo/ExampleSettings.php";' >> LocalSettings.php

echo 'wfLoadExtension( "'$EXTENSION_NAME'" );' >> LocalSettings.php

# Merges WikibaseBoolean's own composer.json requirements
# (data-values/data-values, data-values/interfaces) into MediaWiki's
# root vendor/ via MediaWiki core's already-present composer-merge-plugin
# -- this is what lets the extension's classes resolve at all once
# loaded, the same "must resolve via MediaWiki's root autoloader, not
# the extension's own standalone vendor/" requirement noted elsewhere in
# this project.
cat <<EOT >> composer.local.json
{
  "require": {
    "wikibase/wikibase": "dev-$MW_BRANCH"
  },
	"extra": {
		"merge-plugin": {
			"merge-dev": true,
			"include": [
				"extensions/$EXTENSION_NAME/composer.json"
			]
		}
	}
}
EOT