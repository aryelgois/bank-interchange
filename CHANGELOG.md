# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added
- Wallets for Banco do Nordeste

### Changed
- Example generate_cnab: now it forces the file download
- Example index: rename sections and add text
- Assignor and Payer names

### Deprecated

### Removed
- Accidentally committed lines

### Fixed
- Fix model Title `setOurNumber()` and `discount_type`
- Shipping File counter: using `id` is inconsistent when generating shipping
  files for more than one assignor
- Cnab240 View: wrong registry type in 'Q' segment and wrong registry count
- Model ReturnFile `analyze()`: monetary values weren't turned into float
- Model ReturnFile `analyze()`: CNAB240 occurrence is empty on success
- Our Number check digit for Banco do Nordeste has a different length and base
- Specie code for different banks or the cnabs

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

[aryelgois/utils]: https://github.com/aryelgois/utils
[aryelgois/Medools]: https://github.com/aryelgois/Medools
[vria/nodiacritic]: https://github.com/vria/nodiacritic
[setasign/fpdf]: https://github.com/setasign/fpdf
[twig/twig]: https://github.com/twig/twig
