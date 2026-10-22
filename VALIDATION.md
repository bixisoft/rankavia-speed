# Preview validation

Release: v0.1.0-preview.1. Runtime version: 0.1.0. Public development preview; release asset is the manifest-verified install ZIP.

Install ZIP SHA256: `fa7a91dfc4e7a8dd6de5ef2b2cb3fb1a6484ecb4b545eb11ec81c698d4d351c5`.
Reviewed predecessor ZIP SHA256: `6ea04d2f4cf083c06d59ece6fd249cbd3edaf84c501f86c796ddb5073227685d`.
All predecessor runtime files are byte-identical. Only full GPL LICENSE and SOURCE.txt were added. ZIP metadata is normalized and entries are stored without compression for platform-independent byte reproduction. `release-manifest.json` records every packaged byte hash; `python tools/package.py` verifies them before packaging.

## Existing evidence reused for identical runtime

- WordPress Plugin Check 2.1, plugin_repo category: 0 errors; 0 warnings.
- Packaged PHP syntax: PHP7.4.33 passed (90 files across both plugins).
- Exact-runtime WordPress7.1.3/PHP8.2/MariaDB coexistence and plugin bootstrap passed with separate bundled cores and no development autoloader.
- Rankavia migration engine: synthetic Yoast/Rank Math storage fixtures, signed preview, apply/rollback, stale/tamper/replay rejection, SQL failure, transaction locks and physical connection loss recovery passed. This is synthetic source-storage testing, not an actual source-plugin release matrix.
- Public-payload review: all predecessor110 files scanned across both plugins; no private/credential signatures, missing Composer classmap targets or unsafe archive paths found. Editable source closure and license text verified.

## Final publication checks

Final license/source-notice ZIP: Plugin Check2.1 plugin_repo on owned local WordPress7.1.3/PHP8.2 fixture exited0 with 0 errors and0 warnings. Every packaged file matched the extracted test payload; the original PHP/JS runtime was unchanged. The temporary unexpected-markdown warning was fixed by using SOURCE.txt.

[GitHub exact-preview checks](https://github.com/bixisoft/rankavia-speed/actions/runs/39723959210) validate archive SHA256 reproduction, PHP7.4/8.3 syntax and Plugin Check with the plugin_repo error gate. Warnings remain visible, with no ignored warning codes. Rankavia also passed a fresh Node22 npm ci/build and byte comparison of every generated admin/editor asset against shipped build/. A local clean dependency install passed as additional source-install evidence.


## Open acceptance

Actual WordPress6.5/PHP7.4 runtime; multisite; theme/builder/commerce coexistence; native activation/upgrade/uninstall retention matrix; actual Yoast/Rank Math version matrix; directory metadata/screenshots and WordPress.org submission remain open. No claim of WordPress.org listing, stable production readiness, ranking gain, AI citation, active install count, paid launch or revenue is made. Cache/Heartbeat and unfinished import code are excluded from these packages.

No production migration, real customer transaction, paid activation, scanner/Hub deployment or marketplace submission is part of this source preview.
