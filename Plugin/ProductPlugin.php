<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Catalog\Model\Product;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Replace is in stock native filter on layer.
 */
class ProductPlugin
{

    public function __construct(
        private Offer $offerHelper,
        private Settings $settingsHelper,
        protected CurrentStore $currentStore
    ) {
    }

    /**
     * Retrieve current retailer.
     */
    private function getRetailer(): ?RetailerInterface
    {
        $retailer = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            /** @var RetailerInterface $retailer */
            $retailer = $this->currentStore->getRetailer();
        }

        return $retailer;
    }

    /**
     * Return offer availability (if any) instead of the product one.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We do not need to call the parent method.
     */
    public function aroundIsAvailable(Product $product, Closure $proceed): bool
    {
        $isAvailable = $proceed();

        // show product availability if shop has been selected, even in Retail mode
        if ($this->settingsHelper->useStoreOffers() || $this->getRetailer()) {
            $isAvailable = false;
            $offer = $this->offerHelper->getCurrentOffer($product);

            if ($offer !== null && $offer->isAvailable()) {
                $isAvailable = (bool) $offer->isAvailable();
            }
        }

        return $isAvailable;
    }

    /**
     * Return offer price (if any) instead of the product one.
     */
    public function aroundGetPrice(Product $product, Closure $proceed): mixed
    {
        $price = $proceed();

        // show product offer price if shop has been selected, even in Retail mode
        if ($this->settingsHelper->useStoreOffers() || $this->getRetailer()) {
            $offer = $this->offerHelper->getCurrentOffer($product);

            if ($offer && $offer->getPrice()) {
                $price = $offer->getPrice();
            } elseif ($offer && $offer->getSpecialPrice()) {
                $price = $offer->getSpecialPrice();
            }
        }

        return $price;
    }

    /**
     * Return offer special price (if any) instead of the product one.
     */
    public function aroundGetSpecialPrice(Product $product, Closure $proceed): mixed
    {
        $price = $proceed();

        // show product offer price if shop has been selected, even in Retail mode
        if ($this->settingsHelper->useStoreOffers() || $this->getRetailer()) {
            $offer = $this->offerHelper->getCurrentOffer($product);

            if ($offer && $offer->getSpecialPrice()) {
                $price = $offer->getSpecialPrice();
            }
        }

        return $price;
    }

    /**
     * Return offer final price (if any) instead of the product one.
     */
    public function aroundGetFinalPrice(Product $product, Closure $proceed, mixed $qty = null): mixed
    {
        $price = $proceed($qty);

        // show product offer price if shop has been selected, even in Retail mode
        if ($this->settingsHelper->useStoreOffers() || $this->getRetailer()) {
            $offer = $this->offerHelper->getCurrentOffer($product);

            if ($offer) {
                if ($offer->getPrice() && $offer->getSpecialPrice()) {
                    $price = min($offer->getPrice(), $offer->getSpecialPrice());
                } elseif ($offer->getPrice()) {
                    $price = $offer->getPrice();
                } elseif ($offer->getSpecialPrice()) {
                    $price = $offer->getSpecialPrice();
                }
            }
        }

        return $price;
    }

    /**
     * Return offer minimal price (if any) instead of the product one.
     */
    public function aroundGetMinimalPrice(Product $product, Closure $proceed): mixed
    {
        return $this->aroundGetFinalPrice($product, $proceed);
    }
}
