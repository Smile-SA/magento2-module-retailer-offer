<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Offer\Api\OfferManagementInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Generic Helper for Retailer Offer.
 */
class Offer extends AbstractHelper
{
    /**
     * @var OfferInterface[]
     */
    private array $offersCache = [];

    public function __construct(
        Context $context,
        private OfferManagementInterface $offerManagement,
        private CurrentStore $currentStore
    ) {
        parent::__construct($context);
    }

    /**
     * Retrieve Offer for the product by retailer id.
     */
    public function getOffer(ProductInterface $product, int $retailerId): OfferInterface
    {
        $offer = null;
        $productId = (int) $product->getId();
        if ($productId && $retailerId) {
            $cacheKey = implode('_', [$productId, $retailerId]);

            if (false === isset($this->offersCache[$cacheKey])) {
                $offer                        = $this->offerManagement->getOffer($productId, $retailerId);
                $this->offersCache[$cacheKey] = $offer;
            }

            $offer = $this->offersCache[$cacheKey];
        }

        return $offer;
    }

    /**
     * Retrieve Current Offer for the product.
     */
    public function getCurrentOffer(ProductInterface $product): ?OfferInterface
    {
        $offer = null;

        if ($this->currentStore->getRetailer()) {
            $retailerId = (int) $this->currentStore->getRetailer()->getId();
            if ($retailerId) {
                $offer = $this->getOffer($product, $retailerId);
            }
        }

        return $offer;
    }
}
