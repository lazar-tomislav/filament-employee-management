# Facts (filament-employee-management)

suite_db: n/a (no tests/ dir)
suite: none
lint: vendor/bin/pint <files>
static: none (phpstan crashes on missing spatie/activitylog trait; baseline empty)
assets: none

## Plan
- Host app installs this package from git (`dev-<branch>`), so E2E needs the branch pushed and the app's composer.json pointed at it.
- Probes: package vendor runs Illuminate, PhpSpreadsheet, dompdf without a DB; scratch probe scripts are the safe way to verify behaviour.
- Storage TTL for temporaryUrl comes from host config `filament.temporary_file_url_expiry_minutes`.
