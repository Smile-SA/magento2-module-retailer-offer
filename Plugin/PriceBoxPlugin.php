<?php

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Smile\RetailerOffer\Helper\Offer;

/**
 * PriceBox Plugin : used to ensure variation of price box cache by offer.
 */
class PriceBoxPlugin
{
    public function __construct(private Offer $offerHelper)
    {
    }

    /**
     * Adding retailer Id and Pickup date to price box cache Id.
     *
     * The price box has basically a 3600s cache time so it could cause values for other retailer/date being cached.
     *
     * @see PriceBox::DEFAULT_LIFETIME
     */
    public function aroundGetCacheKey(PriceBox $priceBox, Closure $proceed): string
    {
        $cacheKey = $proceed();

        $salableItem = $priceBox->getSaleableItem();

        if ($salableItem instanceof ProductInterface) {
            $offer = $this->offerHelper->getCurrentOffer($salableItem);
            if ($offer && ($offer->getId())) {
                $cacheKey = implode('-', [$cacheKey, $offer->getId()]);
            }
        }

        return $cacheKey;
    }
}
