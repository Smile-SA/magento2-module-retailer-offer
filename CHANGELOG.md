# Changelog

All notable changes to this project will be documented in this file.

## [2.0.5] - Unreleased
[2.0.5]: https://github.com/Smile-SA/magento2-module-retailer-offer/compare/2.0.4...2.0.5

- Supported platforms: Magento 2.4.4 and 2.4.5 only, PHP 8.1
- `composer.json` now states it: `php ^8.1` and `magento/framework >=103.0.4 <103.0.6` (previously `php ^8.1` and `magento/framework >=103.0.4`)
- Magento 2.4.6 and later and Mage-OS are supported by the 2.1.x line
- Fix `setup:di:compile` on Magento 2.4.4 and 2.4.5: native `mixed` and union types are removed from method signatures and kept in docblocks
- Require Elasticsuite 2.10.x only (`smile/elasticsuite ~2.10.0`); Elasticsuite 2.11 and later go with the 2.1.x line
- Require the Smile modules of the same line: `smile/module-offer ~2.0.2`, `smile/module-retailer ~2.0.5`, `smile/module-store-locator ~2.2.5`
- CI: static analysis on Magento 2.4.5, `setup:di:compile` on Magento 2.4.4 and 2.4.5

## [2.0.4] - 2026-06-03
[2.0.4]: https://github.com/Smile-SA/magento2-module-retailer-offer/compare/2.0.3...2.0.4

- Remove dependency to unused jQuery UI components

## [2.0.3] - 2024-05-03
[2.0.3]: https://github.com/Smile-SA/magento2-module-retailer-offer/compare/2.0.2...2.0.3

- Restore Retail mode - catalog web price. Only Drive mode can show speific retailer offer price, while Retail mode must show same catalog web price for each retailer regarding 1 product

## [2.0.2] - 2024-05-03
[2.0.2]: https://github.com/Smile-SA/magento2-module-retailer-offer/compare/2.0.1...2.0.2

- Add modal map to product page search
- Fix show product offer price if shop has been selected, even in Retail mode

## [2.0.1] - 2023-10-05
[2.0.1]: https://github.com/Smile-SA/magento2-module-retailer-offer/compare/2.0.0...2.0.1

- Fix CategoryPlugin closure return type

## [2.0.0] - 2023-09-20
[2.0.0]: https://github.com/Smile-SA/magento2-module-retailer-offer/compare/1.8.1...2.0.0

Dataset compatibility ES 2.11.x and PHP 8.2

- Fix Dynamic type declaration
- Fix Type hinting
- Replace `Zend_Date` by `DateTime`
- Remove `MutationObserver` support
- Fix UI Component Retailer Offer editing
- Replace `Zend_Validate` by `Laminas\Validator`
- Fix Retailer Grid Column Action
- Fix Retailer Grid Mass Actions
- Fix some translations
- Remove Temando/Shipping Plugin
