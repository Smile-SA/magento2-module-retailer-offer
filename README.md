## Smile Retailer Offer 

This module is a plugin for [ElasticSuite](https://github.com/Smile-SA/elasticsuite).

This module add the ability to manage offers per Retailer Shop.

### Requirements

The module requires :

- [Retailer](https://github.com/Smile-SA/magento2-module-retailer) > 1.2.*
- [Offer](https://github.com/Smile-SA/magento2-module-offer) > 1.3.*
- [Store Locator](https://github.com/Smile-SA/magento2-module-store-locator) > 1.2.*

### How to use

1. Install the module via Composer :

``` composer require smile/module-retailer-offer ```

2. Enable it

``` bin/magento module:enable Smile_RetailerOffer ```

3. Install the module and rebuild the DI cache

``` bin/magento setup:upgrade ```

### How to configure offers

Go to magento backoffice

Menu : Sellers > Retailer Offers
