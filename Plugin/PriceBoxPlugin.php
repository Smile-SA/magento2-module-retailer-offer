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

use Magento\Framework\Pricing\Render\PriceBox;
use Smile\RetailerOffer\Helper\Offer;

/**
 * PriceBox Plugin : used to ensure variation of price box cache by offer.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class PriceBoxPlugin
{
    /**
     * @var Offer
     */
    private Offer $offerHelper;

    /**
     * PriceBoxPlugin constructor.
     *
     * @param Offer $offerHelper Offer Helper
     */
    public function __construct(
        Offer $offerHelper
    ) {
        $this->offerHelper = $offerHelper;
    }

    /**
     * Adding retailer Id and Pickup date to price box cache Id.
     * The price box has basically a 3600s cache time so it could cause values for other retailer/date being cached.
     * @see PriceBox::DEFAULT_LIFETIME
     *
     * @param PriceBox $priceBox The Price Box Renderer
     * @param \Closure $proceed  The getCacheKey() method of price box renderer.
     *
     * @return string
     */
    public function aroundGetCacheKey(PriceBox $priceBox, \Closure $proceed): string
    {
        $cacheKey = $proceed();

        $salableItem = $priceBox->getSaleableItem();

        if ($salableItem instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            $offer = $this->offerHelper->getCurrentOffer($salableItem);
            if ($offer && ($offer->getId())) {
                $cacheKey = implode('-', [$cacheKey, $offer->getId()]);
            }
        }

        return $cacheKey;
    }
}
