## Smile Retailer Offer 

This module is a plugin for [ElasticSuite](https://github.com/Smile-SA/elasticsuite).

This module add the ability to manage offers per Retailer Shop.

### Requirements

The module requires:

- PHP ^8.1
- [ElasticSuite](https://github.com/Smile-SA/elasticsuite) > 2.11.*
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
ElasticSuite **2.11.x** |Latest release : ```composer require smile/module-retailer-offer:"^2.0"```


2. Enable it:

``` bin/magento module:enable Smile_RetailerOffer ```

3. Install the module and rebuild the DI cache:

``` bin/magento setup:upgrade ```

### How to configure offers

Go to magento backoffice

Menu : Sellers > Retailer Offers
