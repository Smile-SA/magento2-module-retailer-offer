## Smile Retailer Offer 

This module is a plugin for [ElasticSuite](https://github.com/Smile-SA/elasticsuite).

This module add the ability to manage offers per Retailer Shop.

### Compatibility

| Module version | Magento | Mage-OS | PHP |
| --- | --- | --- | --- |
| 2.1.x | 2.4.6 and later (tested up to 2.4.9) | 1.0.0 and later (tested up to 4.0) | 8.1 to 8.5, as supported by the platform |
| 2.0.x, from 2.0.5 | 2.4.4 and 2.4.5 | not available | 8.1 |

The 2.0.x line (from 2.0.5) requires Elasticsuite 2.10.x, the 2.1.x line Elasticsuite 2.11 or later.

Releases up to 2.0.4 are not covered by this table: they declared `php ^8.1` and `magento/framework >=103.0.4`, and their method signatures use native `mixed` or union types, which the code generator of Magento 2.4.4 and 2.4.5 rejects (`setup:di:compile` fails).

### Requirements

The module requires:

- PHP ^8.1
- [ElasticSuite](https://github.com/Smile-SA/elasticsuite) 2.10.*
- [Retailer](https://github.com/Smile-SA/magento2-module-retailer) >= 2.0.*
- [Offer](https://github.com/Smile-SA/magento2-module-offer) >= 2.0.*
- [Store Locator](https://github.com/Smile-SA/magento2-module-store-locator) >= 2.2.*

### How to use

1. Install the module via Composer :

ElasticSuite Version   | Module Version
-----------------------|------------------------------------------------------------------------
ElasticSuite **2.1.x** |Latest release : ```composer require smile/module-retailer-offer:"^1.3"```
ElasticSuite **2.3.x** |Latest release : ```composer require smile/module-retailer-offer:"^1.3"```
ElasticSuite **2.6.x** |Latest release : ```composer require smile/module-retailer-offer:"^1.3"```
ElasticSuite **2.7.x** |Latest release : ```composer require smile/module-retailer-offer:"1.4.0"```
ElasticSuite **2.8.x** |Latest release : ```composer require smile/module-retailer-offer:"^1.5"```
ElasticSuite **2.9.x** |Latest release : ```composer require smile/module-retailer-offer:"^1.6"```
ElasticSuite **2.10.x** |Latest release : ```composer require smile/module-retailer-offer:"~2.0.5"```
ElasticSuite **2.11.x** and later |Latest release : ```composer require smile/module-retailer-offer:"^2.1"```


2. Enable it:

``` bin/magento module:enable Smile_RetailerOffer ```

3. Install the module and rebuild the DI cache:

``` bin/magento setup:upgrade ```

### How to configure offers

Go to magento backoffice

Menu : Sellers > Retailer Offers
