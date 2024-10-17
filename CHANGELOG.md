# Changelog

All notable release changes to this project will be documented in this file.

## [1.0.0-beta.1] - Beta version

## Added
- Hosted checkout using Acquired hosted payments page is now available
- Multishipping checkout support for payments by cards
- Multishipping checkout support for payments by payment wallets
- Multishipping checkout support for payments made by hosted checkout solution
- Retry mechanism for hosted checkout to retry payment or restore checkout session
- Small code improvements
- Updated author and license information
- Change versioning format

## [0.9.4] - Beta version

## Added
- Working Google/Apple pay integration using components on checkout

## Modified
- Updated Acquired JS sdk to 1.1
- Updated default Javascript hash integrity value

## [0.9.3] - Beta version

## Added
- Added new file Block/AcquiredJs.php with 161 lines of code.
- Added Acquired to CSP whitelist for external resources
- Added system configuration for Javascript Hash Integrity check
- Added dynamic loading of acquired.js from CDN host using optional javascript integrity check

## Modified
- Modified file view/adminhtml/web/js/card.js to use CDN based Acquired.js sdk
- Modified file view/frontend/web/js/view/payment/method-renderer/card.js to use CDN based Acquired.js sdk

## Removed
- Removed self hosted acquired.js SDK file

## [0.9.2] - Beta Version

### Changed
- Updated the version to `0.9.2 beta`.
- Updated the acquired.js file

## [0.9.1] - Beta Version

### Changed
- Updated the version in `Model/Config.php` from `0.0.1` to `0.9.1 beta`.

### Added
- Added a new `moto` field to the data array in `Service/GetAdminPaymentSessionData.php`.
- Added a new line in `view/base/templates/info/info.phtml` to display the last 4 digits of the credit card number.

## [0.9.0] - Initial Beta Version

### Added
- Acquired payment gateway integration.
- Support for card payments.
- Support for 3D secure payments.
- Support for admin orders.

### Changed

### Deprecated

### Removed

### Fixed

### Security