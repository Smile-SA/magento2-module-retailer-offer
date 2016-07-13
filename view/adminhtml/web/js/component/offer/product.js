/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Retailer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

define([
    'Magento_Ui/js/form/components/html',
    'jquery',
    'MutationObserver'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            value: {},
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            },
            additionalClasses: "admin__fieldset"
        },

        /**
         * Initialize the component
         */
        initialize: function ()
        {
            this._super();
            this.initProductListener();
        },

        /**
         * Init Observation on fields
         *
         * @returns {exports}
         */
        initObservable: function ()
        {
            this._super();
            this.productObject = {};
            this.observe('productObject value');

            return this;
        },

        /**
         * Init Observer on the product id field.
         */
        initProductListener: function ()
        {
            var observer = new MutationObserver(function () {
                var rootNode = document.getElementById(this.index);
                if (rootNode !== null) {
                    this.rootNode = document.getElementById(this.index);
                    observer.disconnect();
                    var productObserver = new MutationObserver(this.updateProduct.bind(this));
                    var productObserverConfig = {childList:true, subtree: true, attributes: true};
                    productObserver.observe(rootNode, productObserverConfig);
                    this.updateProduct();
                }
            }.bind(this));
            var observerConfig = {childList: true, subtree: true};
            observer.observe(document, observerConfig)
        },

        /**
         * Update value of the Product Object
         */
        updateProduct: function ()
        {
            var productObject = {};
            var hashValues = [];

            $(this.rootNode).find("[name*=" + this.index + "]").each(function () {
                hashValues.push(this.name + this.value.toString());
                var currentProductObject = productObject;

                var path = this.name.match(/\[([^[\[\]]+)\]/g)
                    .map(function (pathItem) { return pathItem.substr(1, pathItem.length-2); });

                while (path.length > 1) {
                    var currentKey = path.shift();

                    if (currentProductObject[currentKey] === undefined) {
                        currentProductObject[currentKey] = {};
                    }

                    currentProductObject = currentProductObject[currentKey];
                }

                currentKey = path.shift();
                currentProductObject[currentKey] = $(this).val();
            });

            var newHashValue = hashValues.sort().join('');

            if (newHashValue !== this.currentHashValue) {
                if (this.currentHashValue !== undefined) {
                    this.bubble('update', true);
                }
                this.currentHashValue = newHashValue;
                this.productObject(productObject);

                this.value(productObject);
            }
        }
    })
});

