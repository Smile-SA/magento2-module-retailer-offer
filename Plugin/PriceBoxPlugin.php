<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\Render\PriceBox;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;

/**
 * PriceBox Plugin : used to ensure variation of price box cache by offer.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class PriceBoxPlugin extends AbstractPlugin
{
    /**
     * Adding retailer Id and Pickup date to price box cache Id.
     * The price box has basically a 3600s cache time so it could cause values for other retailer/date being cached.
     * @see \Magento\Framework\Pricing\Render\PriceBox::DEFAULT_LIFETIME
     *
     * @param \Magento\Framework\Pricing\Render\PriceBox $priceBox The Price Box Renderer
     * @param \Closure                                   $proceed  The getCacheKey() method of price box renderer.
     *
     * @return string
     */
    public function aroundGetCacheKey(PriceBox $priceBox, \Closure $proceed)
    {
        $cacheKey = $proceed();

        $salableItem = $priceBox->getSaleableItem();

        if ($salableItem instanceof ProductInterface) {
            $offer = $this->getCurrentOffer($salableItem);
            if ($offer && ($offer->getId())) {
                $cacheKey = implode('-', [$cacheKey, $offer->getId()]);
            }
        }

        return $cacheKey;
    }
}
