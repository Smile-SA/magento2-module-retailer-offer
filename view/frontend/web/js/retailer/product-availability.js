define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'ko',
    'uiRegistry',
    'Smile_Map/js/model/markers',
    'leaflet',
    'smile-storelocator-store-collection',
    'mage/translate',
    'jquery/ui'
    ], function ($, Component, storage, ko, registry, Markers, L, StoreCollection) {

    "use strict";

    var retailer = storage.get('current-store');

    return Component.extend({

        /**
         * Constructor
         */
        initialize: function () {
            this._super();
            var offers = new StoreCollection({items : this.storeOffers});
            offers.getList().forEach(function(marker) {
                marker.distance = ko.observable(0);
            });
            this.storeOffers = ko.observable(offers.getList());
            this.displayedOffers = ko.observable(offers.getList());
            this.observe(['fulltextSearch']);
        },

        /**
         * Search Shop per words
         */
        onSearchOffers: function() {
            this.displayedOffers(
                this.filterPerWords(this.storeOffers(), this.fulltextSearch())
            );
        },

        /**
         * Custom filter to allow approaching result per words
         *
         * @param markers
         * @param terms
         * @returns {[]|jQuery|*[]|*|null}
         */
        filterPerWords(markers, terms) {
            let self = this;
            let arrayOfTerms = terms.split(' ');
            let term = $.map(arrayOfTerms, function (tm) {
                if (tm.length <= 2) {
                    // ignore smallest term for performance reason
                    return null;
                }
                return $.ui.autocomplete.escapeRegex(self.normalizeAccent(tm));
            }).join('|');
            let matcher= new RegExp("\\b" + term, "i");

            if (term.length <= 2) {
                // ignore smallest term for performance reason
                return null;
            }

            return $.grep(markers, function (marker) {
                // try to match one of the 4 elements
                return matcher.test(marker.name)
                    || matcher.test(marker.postCode)
                    || matcher.test(self.normalizeAccent(marker.city))
                    || matcher.test(marker.region)
                ;
            });
        },

        /**
         * Replace accent from string
         *
         * @param str
         * @returns {*}
         */
        normalizeAccent: function(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        },

        /**
         * Check if there is a current store
         *
         * @returns {boolean}
         */
        hasStore : function () {
            return (retailer().entity_id !== null) && (retailer().entity_id !== undefined);
        },

        /**
         * Retrieve link label
         *
         * @returns {string}
         */
        getLinkLabel : function () {
            return $.mage.__('View availability in stores ...');
        },

        /**
         * Get current store name
         *
         * @returns {*}
         */
        getStoreName : function () {
            return retailer().name;
        },

        /**
         * Get stock label depending of the status
         *
         * @returns {string}
         */
        getStockLabel: function () {
            return this.getIsInStock() === true ? $.mage.__('In Stock') : $.mage.__('Out Of Stock');
        },

        /**
         * Check if the product is in stock for the currently selected store.
         *
         * @returns {boolean}
         */
        getIsInStock: function() {
            var result = false;
            if (this.hasStore()) {
                var retailerId = retailer().entity_id;
                this.storeOffers().forEach(function(store) {
                    if ((store.isAvailable) && (parseInt(store.sellerId, 10) === parseInt(retailerId, 10))) {
                        result = true;
                    }
                }, this);
            }

            return result;
        },

        /**
         * Geolocalize me button action
         */
        geolocalizeMe: function() {
            registry.get(this.name + '.geocoder', function (geocoder) {
                this.geocoder = geocoder;
                this.geocoder.geolocalize(this.geolocationSuccess.bind(this))
            }.bind(this));
        },

        /**
         * Action on geolocation success
         */
        geolocationSuccess: function(position) {
            if (position.coords && position.coords.latitude && position.coords.longitude) {
                registry.get(this.name + '.map', function (map) {
                    this.map = map;
                    this.map.applyPosition(position);
                    this.map.addMarkerWithMyPosition(position)
                }.bind(this));

                this.updateDisplayedOffers();
            }
        },

        /**
         * Update list of displayed offers
         */
        updateDisplayedOffers: function () {
            registry.get(this.name + '.map', function (map) {
                this.map = map;
                this.map.refreshDisplayedMarkers();
                this.displayedOffers(this.map.displayedMarkers());
            }.bind(this));
        },

        /**
         * Load modal function to set moveend event on map
         *
         * @returns {string}
         */
        loadRetailerAvailabilityModal : function () {
            let self = this;
            registry.get(this.name + '.map', function (map) {
                this.map = map;

                // Update map if geolocation is ON and customer already click to geolocalize button
                if (navigator.geolocation && window.location.search === '' && window.location.hash.length > 1) {
                    self.geolocalizeMe();
                }

                // Refresh moveen trigger
                this.map.map.off('moveend');
                this.map.map.on('moveend', self.updateDisplayedOffers.bind(self));
            }.bind(this));
        }
    });
});
