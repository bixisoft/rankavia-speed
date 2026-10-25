# Rankavia Speed

Free, GPL-2.0-or-later WordPress plugin by Bixisoft.

**Development preview — plugin version 0.1.0; release v0.1.0-preview.2.** Test on a disposable copy before using it on a live site. This repository is public source distribution, not a WordPress.org listing or a stable compatibility certification.

WordPress cleanup plus opt-in native script defer, Core prefetch restrictions and preconnect controls. No page cache, image/font optimizer, universal speed improvement or guaranteed Core Web Vitals result is included.

## Download and install

Download **rankavia-speed.zip** from [the preview release](https://github.com/bixisoft/rankavia-speed/releases/tag/v0.1.0-preview.2). Upload that asset through WordPress Plugins → Add New → Upload Plugin, then activate. The GitHub-generated source archives contain development material and are not the install ZIP. Keep a database and file backup, especially before migration or changing asset loading.

Requires WordPress 6.5+ and PHP 7.4+. Exact executable files passed coexistence/homepage acceptance on WordPress 6.5/PHP 7.4.33. File-identical predecessor Plugin Check 2.1 reported 0 errors and 0 warnings. Repository CI checks the normalized artifact before release. Read [VALIDATION.md](VALIDATION.md) for limits and hashes.

## Source and building

All plugin code is editable PHP. No JavaScript compilation or dependency installation is required.

All production PHP dependencies are included in `vendor/`, with a plugin-specific core namespace so both free plugins can coexist. Do **not** run `composer install` in this extracted distribution: the historical composer.json describes a monorepo path dependency, not a standalone dependency setup.

Build the install ZIP from the verified runtime manifest with Python 3:

```sh
python tools/package.py
```

This checks every packaged file and creates `artifacts/rankavia-speed.zip`. It excludes repository docs, CI and development dependencies. Runtime changes require refreshed validation and a new manifest/release; this script intentionally rejects unreviewed changes.

## Privacy and support

See [readme.txt](readme.txt) for external service disclosures and current limitations. No paid activation, telemetry, external publishing, auto updates or billing is enabled by this preview. Report reproducible issues in this repository; never include credentials or private customer data.

## License

Rankavia code is GPL-2.0-or-later; full GPL v2 text is in [LICENSE](LICENSE). Composer's bundled loader retains its MIT license in `vendor/composer/LICENSE`. Build dependencies retain their own package licenses, recorded in the editable-source dependency manifests/lockfile where applicable.
