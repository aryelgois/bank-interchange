# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added
- Year 2018 in LICENSE
- Dependencies [aryelgois/databases], [aryelgois/yasql-php] and [symfony/yaml]
- Composer scripts and Builder config for [aryelgois/yasql-php]
- Wallets for Banco do Nordeste
- Namespace `aryelgois\BankInterchange\ReturnFile`
- Rewrite ReturnFile config files in [YAML]
- Configurations for matching return files in Banco do Nordeste CNAB400 schema
- Billet column in currency_codes table
- Add Utils `addExtension()`, `checkOutput()` and `toPascalCase()`
- Changelog file
- Generic tables for BankBillet views
- Can generate a `.zip` with multiple bank billets
- Platform requirements (zlib and zip extensions)
- Setup section

### Changed
- Bump [aryelgois/medools] version
- Example generate_cnab: now it forces the file download
- Example index: rename sections and add text
- Assignor and Payer names in BankBillet fields
- Convert database.sql to [YASQL][aryelgois/yasql]
- Update ReturnFile config file: improved patterns, renamed some fields
- Split assignors table into `assignors` and `assignments`
- Rename payers to clients and bound them to assignors
- Replace assignor column with assignment in titles table
- Split currencies table into `currencies` and `currency_codes`
- Update wallets
- Move BankBillet classes to its own namespace
- BankBillet view keeps a plain array with most models extracted from a Title
- Invert default parameter value for some methods in BankBillet view
- Use bank name in PascalCase to select the BankBillet view
- Allow multiple paths to be searched for logos
- Replace `$dictionary` with `$fields`
- Rename `$billet` to `$data`
- Replace `drawTableRow()` and `drawTableColumn()` with `drawRow()`
- Rewrite BankBillet Controller

### Deprecated

### Removed
- Accidentally committed lines
- Alias 'BankI' for `aryelgois\BankInterchange`
- Remove `beforeDraw()`

### Fixed
- Fix model Title `setOurNumber()` and `discount_type`
- Shipping File counter: using `id` is inconsistent when generating shipping
  files for more than one assignor
- Cnab240 View: wrong registry type in 'Q' segment and wrong registry count
- Model ReturnFile `analyze()`: monetary values weren't turned into float
- Model ReturnFile `analyze()`: CNAB240 occurrence is empty on success
- Our Number check digit for Banco do Nordeste has a different length and base
- Specie code for different banks or the cnabs
- Remove EOF character `0x1A` in shipping files
- Rename `B. do Nordeste` to `Banco do Nordeste`
- BankBillet views

### Security


## [0.1] - 2017-11-27

### Added
- Dependencies [aryelgois/Medools], [vria/nodiacritic] and [setasign/fpdf]
- Config file for [aryelgois/Medools]
- Database schema and defaults in SQL
- Some bank logos (and assignor example logos)

### Changed
- Project name changed from `cnab240` to `bank-interchange`
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
