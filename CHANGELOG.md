# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added
- Year 2018 in LICENSE
- Changelog file
- README sections:
  - Setup
- Dependencies:
  - [aryelgois/databases]
  - [aryelgois/yasql-php]
  - [symfony/yaml]
  - Platform requirements:
    - zlib and zip extensions
- Composer scripts:
  - [aryelgois/yasql-php]
- Config files:
  - Builder config for [aryelgois/yasql-php]
  - Return file parser options for Banco do Nordeste's CNAB400 schema
- Database:
  - Populate `wallets` for Banco do Nordeste
  - Column `billet` in `currency_codes`
  - Table `document_kinds`
  - Column `document_kind` in `assignments`
  - Column `accept` in `titles`
  - Column `cnab` in `shipping_files`
  - Column `cnab` in `assignments`
  - Table `shipping_file_movements`
  - Column `movement` in `shipping_file_titles`
  - Column `doc_number` in `titles`
  - Columns for `interest` in `titles`
  - Column `agency_account_cd` in `assignments`
- Namespace `aryelgois\BankInterchange\ReturnFile`
- Utils `addExtension()`, `toPascalCase()`
- Generic tables for BankBillet views
- Can generate a `.zip` with multiple bank billets
- General Controller
- ShippingFile `getShippedTitles()`
- ShippingFile View `TITLE_LIMIT`
- Bank specific ShippingFile views
- Title `getCurrencyCode()`
- Mode `nomask` in Currency `format()`
- `Person` model (extending Medools `Person`)
- Namespace `aryelgois\BankInterchange\FilePack`

### Changed
- Update dependencies
- Example
  - `generate_cnab`: force the file download
  - `index`: rename sections and add text
- Database:
  - Convert to [YASQL][aryelgois/yasql]
  - Split `assignors` into `assignors` and `assignments`
  - Rename `payers` to `clients` and bound them to `assignors`
  - Replace `assignor` column with `assignment` in `titles`
  - Split `currencies` into `currencies` and `currency_codes`
  - Rename `tax` column to `billet_tax`
  - Rename `iof` column to `ioc_iof` in `titles`
  - Use `document_kinds` in `titles`
  - Split `discount` columns into multiple discounts
- Config files:
  - Rewrite ReturnFile config files in [YAML], improve patterns, rename some
    fields
- Assignor and Payer names in BankBillet fields
- Update wallets
- Move BankBillet classes to its own namespace
- BankBillet view keeps a plain array with most models extracted from a Title
- Invert default parameter value for some methods in BankBillet view
- Use bank name in PascalCase to select the BankBillet view
- Allow multiple paths to be searched for logos
- Use model id to select logo file
- Replace `$dictionary` with `$fields`
- Rename `$billet` to `$data`
- Replace `drawTableRow()` and `drawTableColumn()` with `drawRow()`
- Rewrite BankBillet Controller
- Move ShippingFile classes to its own namespace
- Replace `setCounter()` in ShippingFile model with `onFirstSave()`
- Rewrite ShippingFile Controller
- Rewrite ShippingFile View
- Move resource files to assets directory
- Replace `SPECIE_DOC` with title's `kind`
- Rewrite ShippingFile Cnab* views
- Update [aryelgois/Medools] config file

### Deprecated

### Removed
- Accidentally committed lines
- Alias 'BankI' for `aryelgois\BankInterchange`
- BankBillet View `beforeDraw()`
- Example of assignor logos
- Column `doc_type` in `titles`
- Defaults for `fine_type` and `discount_type` in `titles`

### Fixed
- Fix model Title `setOurNumber()` and `discount_type`
- Shipping File counter: using `id` is inconsistent when generating shipping
  files for more than one assignor
- Cnab240 View: wrong registry type in 'Q' segment and wrong registry count
- Model ReturnFile `analyze()`: monetary values weren't turned into float
- Model ReturnFile `analyze()`: CNAB240 occurrence is empty on success
- Our Number check digit for Banco do Nordeste has a different length and base
- Currency code for different banks and CNABs
- Remove EOF character `0x1A` in shipping files
- Rename `B. do Nordeste` to `Banco do Nordeste`
- BankBillet views
- Rename `formated` to `formatted`

### Security


## [0.1] - 2017-11-27

### Added
- Dependencies:
  - [aryelgois/Medools]
  - [vria/nodiacritic]
  - [setasign/fpdf]
- Config file for [aryelgois/Medools]
- Database schema and defaults in SQL
- Some bank logos (and assignor example logos)

### Changed
- Rename project from `cnab240` to `bank-interchange`
- Bump [aryelgois/utils] version
- Example from a moderate complex twig to a simpler HTML + JavaScript
- Code and logic mostly rewritten

### Removed
- Obsolete dependency aryelgois/objects
- Dev dependency [twig/twig]

### Fixed
- README


[Unreleased]: https://github.com/aryelgois/bank-interchange/compare/v0.1...v0.x
[0.1]: https://github.com/aryelgois/bank-interchange/compare/288be2a584bca48feab56f750fe8c51804f0e7ab...v0.1

[aryelgois/databases]: https://github.com/aryelgois/databases
[aryelgois/Medools]: https://github.com/aryelgois/Medools
[aryelgois/utils]: https://github.com/aryelgois/utils
[aryelgois/yasql]: https://github.com/aryelgois/yasql
[aryelgois/yasql-php]: https://github.com/aryelgois/yasql-php
[setasign/fpdf]: https://github.com/setasign/fpdf
[symfony/yaml]: https://github.com/symfony/yaml
[twig/twig]: https://github.com/twig/twig
[vria/nodiacritic]: https://github.com/vria/nodiacritic

[YAML]: http://yaml.org/
