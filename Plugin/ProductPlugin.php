<?php

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Catalog\Model\Product;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Replace is in stock native filter on layer.
 */
class ProductPlugin
{
    public function __construct(private Offer $offerHelper, private Settings $settingsHelper)
    {
    }

    /**
     * Return offer availability (if any) instead of the product one.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We do not need to call the parent method.
     */
    public function aroundIsAvailable(Product $product, Closure $proceed): bool
    {
        $isAvailable = $proceed();

        if ($this->settingsHelper->useStoreOffers()) {
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

        if ($this->settingsHelper->useStoreOffers()) {
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

        if ($this->settingsHelper->useStoreOffers()) {
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

        if ($this->settingsHelper->useStoreOffers()) {
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
