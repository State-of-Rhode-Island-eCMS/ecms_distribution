# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][] and this project adheres to a
modified Semantic Versioning scheme. See the "Versioning scheme" section of the
[CONTRIBUTING][] file for more information.

[Keep a Changelog]: http://keepachangelog.com/
[CONTRIBUTING]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/docs/release-workflow.md#versioning-scheme

## [Unreleased]
### Added
- RIG-158: Force HTTPS on production environment.

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [0.2.7] - 2020-12-01
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.8.
- RIG-6: Updated ecms_profile to 0.2.6.
### Security
- RIG-157: Security Update: SA-CORE-2020-013

## [0.2.6] - 2020-11-25
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.7.
- RIG-6: Updated ecms_profile to 0.2.5.

### Fixed
- RIG-6 - Fixed settings variable and path for config directories.

## [0.2.5] - 2020-11-24
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.6.
- RIG-6: Updated ecms_profile to 0.2.4

## [0.2.4] - 2020-11-20
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.5

## [0.2.3] - 2020-11-19
### Changed
- RIG-6: Updated ecms_profile to 0.2.3
- RIG-6: Updated ecms_patternlab to 0.2.4
### Security
- RIG-152: SA-CORE-2020-012.

## [0.2.2] - 2020-11-18
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.3.
- RIG-6: Updated ecms_profile to 0.2.2.

## [0.2.1] - 2020-11-13
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.2.
- RIG-6: Updated ecms_profile to 0.2.1.

## [0.2.0] - 2020-11-12
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.0 (0.1.10).
- RIG-6: Updated ecms_profile to 0.2.0.

## [0.1.13] - 2020-11-11
### Changed
- RIG-6: Updated ecms_patternlab to 0.1.9.
- RIG-6: Updated ecms_profile to 0.1.9.
- Updated oomphinc/composer-installers-extender to ^2.0.

## [0.1.12] - 2020-11-06
### Changed
- RIG-6: Updated ecms_patternlab to 0.1.8.
- RIG-6: Updated ecms_profile to 0.1.8.

## [0.1.11] - 2020-11-05
### Changed
- RIG-6 - Hotfix pattern lab to 0.1.7.

## [0.1.10] - 2020-11-05
### Added
- RIG-6 - Added config.php post settings hook.

### Changed
- RIG-6: Updated ecms_patternlab to 0.1.6.
- RIG-6: Updated ecms_profile to 0.1.7.

## [0.1.9] - 2020-10-30
### Changed
- RIG-6 - Updated ecms_profile to use 0.1.6.

## [0.1.8] - 2020-10-30
### Changed
- RIG-6 - Updated ecms_patternlab to use 0.1.5.

## [0.1.7] - 2020-10-30
### Changed
- RIG-6 - Updated ecms_patternlab to use 0.1.4.
- RIG-6 - Updated the ecms_profile to use 0.1.5.
- RIG-6 - Locked Lando's composer version to 1.10.1.

## [0.1.6] - 2020-10-28
### Changed
- RIG-6 - Updated ecms_patternlab to use 0.1.3.
- RIG-6 - Updated ecms_profile to use 0.1.4.

## [0.1.5] - 2020-10-27
### Changed
- RIG-6: Updated ecms_patternlab to use 0.1.2.
- RIG-6: Updated ecms_profile to use 0.1.3.

### Fixed
- RIG-122: Fixed the features import drush command.

## [0.1.4] - 2020-10-22
### Changed
- Changed the ecms_patternlab to use 0.1.1.
- Changed the ecms_profile to use 0.1.2.

## [0.1.3] - 2020-10-19
### Added
- Added verbose logging to the site factory db-update script.

### Changed
- Changed the ecms_profile to use 0.1.1.
- Changed the ecms_patternlab to use 0.1.0.

## [0.1.2] - 2020-10-15
### Added
- RIG-12: Added the secrets.php post settings hook.
- RIG-28: Added the automated ci/cid workflow to push to Acquia.
- RIG-37: Added custom package repository for ECMS Pattern Lab integration.
- RIG-83: Updated the Pattern lab requirement and all dependencies.
- RIG-96: Added db-update cloud hook to revert features.

### Changed
- RIG-67: Updated composer lock file to ensure latest dependencies are installed.
- RIG-6: Updated the ecms_profile to the initial 0.1.0 release.

### Fixed
- RIG-28: Updated the lock file to include ecms_profile requirements.

## [0.1.1] - 2020-10-09
## [0.1.0] - 2020-10-07
### Added
- Initial Release of the site

[Unreleased]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.7...HEAD
[0.2.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.6...0.2.7
[0.2.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.5...0.2.6
[0.2.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.4...0.2.5
[0.2.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.3...0.2.4
[0.2.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.2...0.2.3
[0.2.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.13...0.2.0
[0.1.13]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.12...0.1.13
[0.1.12]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.11...0.1.12
[0.1.11]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.10...0.1.11
[0.1.10]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.9...0.1.10
[0.1.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.8...0.1.9
[0.1.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.7...0.1.8
[0.1.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.6...0.1.7
[0.1.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.5...0.1.6
[0.1.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.4...0.1.5
[0.1.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.3...0.1.4
[0.1.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.2...0.1.3
[0.1.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/releases/tag/0.1.0
