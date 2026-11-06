# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://git.d3data.de/D3Public/DataWizard/compare/3.0.0.0...rel_3.x)

### Added
- Support for OXID eShop 7.2 through 7.5.
- Configurable box templates for individual exports and actions.
- Spreadsheet-safe CSV export format that neutralizes potential formula prefixes.
- An option on each export and action to preview its SQL query without executing it.

### Changed
- Use spreadsheet-safe CSV for the primary admin export button; raw CSV remains available.
- Process rows incrementally for built-in CSV and JSON exports to reduce PHP memory usage.
- Report successful actions and their affected row count as a result instead of an exception.
- Serve admin JavaScript and styles locally rather than loading them from CDNs.
- Update the CSV dependency and replace deprecated League CSV calls.
- Use assertion exceptions for validation.

### Fixed
- Prevent exports with colliding file names from overwriting existing files.
- Publish file exports atomically so consumers do not see partially written files.

### Removed
- The global debug setting; SQL query previews are selected per export or action.

## [3.0.0.0](https://git.d3data.de/D3Public/DataWizard/compare/2.1.1.3...3.0.0.0) - 2024-05-13
### Added
- installable in OXID 7.0 + 7.1
- Twig support

### Removed
- support for OXID < 7

## [2.1.1.3](https://git.d3data.de/D3Public/DataWizard/compare/2.1.1.2...2.1.1.3) - 2023-09-12
### Added
- apply roles and rights

## [2.1.1.2](https://git.d3data.de/D3Public/DataWizard/compare/2.1.1.1...2.1.1.2) - 2023-03-30
### Fixed
- fix JavaScript for multiple forms

## [2.1.1.1](https://git.d3data.de/D3Public/DataWizard/compare/2.1.1.0...2.1.1.1) - 2023-03-22
### Fixed
- wrong cascaded HTML elements

## [2.1.1.0](https://git.d3data.de/D3Public/DataWizard/compare/2.1.0.0...2.1.1.0) - 2022-12-24
### Added
- installable in OXID 6.5.0 and 6.5.1
- request confirmation before execute action

### Changed
- documentation extended

## [2.1.0.0](https://git.d3data.de/D3Public/DataWizard/compare/2.0.0.0...2.1.0.0) - 2021-12-21
### Changed
- adjust to CLI extension which can export to STDOUT

## [2.0.0.0](https://git.d3data.de/D3Public/DataWizard/compare/1.4.0.0...2.0.0.0) - 2021-12-20
### Added
- add tests
- make installable in OXID 6.4

### Changed
- adjust code to PHP 7.3 and up

## [1.4.0.0](https://git.d3data.de/D3Public/DataWizard/compare/1.3.1.0...1.4.0.0) - 2021-11-11
### Added
- add JSON export renderer
- add tpl block for easier extension
- enable controller based exception handling

### Changed
- change deprecated CSV export code
- show registered export renderers in admin submit without required tpl block extension
- force get translations from admin

## [1.3.1.0](https://git.d3data.de/D3Public/DataWizard/compare/1.2.0.0...1.3.0.0) - 2021-09-10
### Added
- installable in OXID 6.3.1

## [1.3.0.0](https://git.d3data.de/D3Public/DataWizard/compare/1.2.0.0...1.3.0.0) - 2021-07-29
### Changed
- can handle long task description text
- has more generic admin controller templates

## [1.2.0.0](https://git.d3data.de/D3Public/DataWizard/compare/1.1.0.0...1.2.0.0) - 2021-07-27
### Added
- adjustments for CLI extension

### Fixed
- fix OXID 6.1 incompatibilities

## [1.1.0.0](https://git.d3data.de/D3Public/DataWizard/compare/1.0.0.0...1.1.0.0) - 2021-06-25
### Added
- implement form builder

### Fixed
- fix key figures export

## [1.0.0.0](https://git.d3data.de/D3Public/DataWizard/releases/tag/1.0.0.0) - 2021-06-22
### Added
- initial implementation