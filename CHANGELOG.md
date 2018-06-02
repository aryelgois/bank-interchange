# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]


## [0.2.0] - 2018-06-01

### Added
- Year 2018 in LICENSE
- README:
  - Repository title
  - Index
  - Setup
- Changelog
- Documentation
- Dependencies:
  - [aryelgois/databases]
  - [aryelgois/medools-router]
  - [aryelgois/yasql-php]
  - [symfony/yaml]
  - Platform requirements:
    - zlib and zip extensions
- Composer scripts:
  - [aryelgois/yasql-php]
- Config files:
  - Builder config for [aryelgois/yasql-php]
  - Return file parser options for Banco do Nordeste's CNAB400 schema
  - Router config
- Database:
  - Tables:
    - `assignments`
    - `currency_codes`
    - `document_kinds`
    - `shipping_file_movements`
  - Columns:
    - `currency_codes.billet`
    - `assignments.document_kind`
    - `titles.accept`
    - `assignments.cnab`
    - `titles.doc_number`
    - `titles.interest_*`
    - `assignments.agency_account_cd`
    - `titles.tax_included`
    - `titles.shipping_file`
    - `titles.movement`
    - `shipping_files.notes`
    - `titles.emission`
    - `titles.protest_*`
    - `titles.occurrence_*`
  - Index keys for `assignment` and `client` in `titles`
  - Populate `wallets` for Banco do Nordeste
  - SQL Programs
  - Authentication database
- Namespace `aryelgois\BankInterchange\ReturnFile`
- Namespace `aryelgois\BankInterchange\FilePack`
- BankBillet:
  - Generic table layouts
  - Can generate a `.zip` with multiple bank billets
  - `View::updateDictionary()`
  - Simple template syntax
    - Allows dynamic access to any data in the view
    - Supported by: `demonstrative`, `instructions`, `header_info`
- Models:
  - `ShippingFile::getTitles()`
  - `Title::getCurrencyCode()`
  - Mode `nomask` in `Currency::format()`
  - `Person` model extending `\aryelgois\Medools\Models\Person`
  - `Title::getActualValue()`
- ShippingFile:
  - `View::TITLE_LIMIT`
  - Bank specific views
  - `View::date()`
  - Optional 'R' segment in CNAB240
  - Movement masks
- Utils:
  - `addExtension()`
  - `cleanSpaces()`
  - `toPascalCase()`
- `public/`

### Changed
- Update README
- Update dependencies
- Database:
  - Convert to [YASQL][aryelgois/yasql]
  - Split `assignors` into `assignors` and `assignments`
  - Rename `payers` to `clients` and bound them to `assignors`
  - Replace `assignor` column with `assignment` in `titles`
  - Split `currencies` into `currencies` and `currency_codes`
  - Rename `tax` column to `billet_tax`
  - Rename `iof` column to `ioc_iof`
  - Use `document_kinds` in `titles`
  - Split `discount` columns into multiple discounts
  - Change `assignors` PRIMARY KEY to `person`
  - Move `address` column from `assignors` to `assignments`
  - Rename `billet_tax` column in `titles` to `tax_value`
  - Title `fine_type`, `interest_type` and `discount*_type` are `tinyint` have
    default value
- Config files:
  - ReturnFile:
    - Rewrite configs in [YAML]
    - Split parser config into individual files
    - Improve patterns
    - Rename some fields
    - Improve Parser
  - Update [aryelgois/Medools] config file
- Assignor and Payer names in BankBillet fields
- Update populate wallets
- Move BankBillet classes to its own namespace
- Invert default parameter value for some methods in BankBillet view
- Use bank name in PascalCase to select the BankBillet view
- Allow multiple paths to be searched for logos
- Use model id to select logo file
- Replace `$dictionary` with `$fields`
- Rename `$billet` to `$data`
- Replace `drawTableRow()` and `drawTableColumn()` with `drawRow()`
- Rewrite BankBillet Controller
- Move ShippingFile classes to its own namespace
- Rewrite ShippingFile Controller
- Rewrite ShippingFile View
- Move resource files to assets directory
- Replace `SPECIE_DOC` with title's `kind`
- Rewrite ShippingFile Cnab* views
- BankBillet and ShippingFile Controllers and Views use FilePack
- Convert billet data to [YAML]
- Use `class` keyword in foreign classes

### Removed
- Accidentally committed lines
- Alias 'BankI' for `aryelgois\BankInterchange`
- `BankBillet\View::beforeDraw()`
- Example of assignor logos
- Column `titles.doc_type`
- Table `shipping_file_titles`
- `Title::setOurNumber()`
- Old ReturnFile model
- Columns `titles.status` and `shipping_files.status`
- ReturnFile Controller
- `Utils::padAlfa()`
- Old example

### Fixed
- Shipping File counter: using `id` is inconsistent when generating shipping
  files for more than one assignor
- Cnab240 View: wrong registry type in 'Q' segment and wrong registry count
- Our Number check digit for Banco do Nordeste has a different length and base
- Currency code for different banks and CNABs
- Remove EOF character `0x1A` in ShippingFile View
- Rename `B. do Nordeste` to `Banco do Nordeste`
- BankBillet views
- Rename `formated` to `formatted`
- Comparison operators
- Database populate collation


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


[Unreleased]: https://github.com/aryelgois/bank-interchange/compare/v0.2.0...develop
[0.2.0]: https://github.com/aryelgois/bank-interchange/compare/v0.1...v0.2.0
[0.1]: https://github.com/aryelgois/bank-interchange/compare/288be2a584bca48feab56f750fe8c51804f0e7ab...v0.1

[aryelgois/databases]: https://github.com/aryelgois/databases
[aryelgois/Medools]: https://github.com/aryelgois/Medools
[aryelgois/medools-router]: https://github.com/aryelgois/medools-router
[aryelgois/utils]: https://github.com/aryelgois/utils
[aryelgois/yasql]: https://github.com/aryelgois/yasql
[aryelgois/yasql-php]: https://github.com/aryelgois/yasql-php
[setasign/fpdf]: https://github.com/setasign/fpdf
[symfony/yaml]: https://github.com/symfony/yaml
[twig/twig]: https://github.com/twig/twig
[vria/nodiacritic]: https://github.com/vria/nodiacritic

[YAML]: http://yaml.org/
