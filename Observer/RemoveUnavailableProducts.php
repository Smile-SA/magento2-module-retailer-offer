<?php

namespace Smile\RetailerOffer\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\RetailerOffer\Helper\Settings as SettingsHelper;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Remove unavailable products (according to their current offer) from current quote.
 */
class RemoveUnavailableProducts implements ObserverInterface
{
    public function __construct(
        private ManagerInterface $eventManager,
        private OfferHelper $offerHelper,
        private CurrentStore $currentStore,
        private SettingsHelper $settingsHelper
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->settingsHelper->isDriveMode()) {
            /** @var Collection $productCollection */
            $productCollection = $observer->getEvent()->getCollection();

            $unavailableProducts = [];

            foreach ($productCollection as $key => $product) {
                $offer = $this->getCurrentOffer($product);

                if ($offer === null || (false === $offer->isAvailable())) {
                    $unavailableProducts[] = $product;
                    $productCollection->removeItemByKey($key);
                }
            }

            $this->eventManager->dispatch(
                "smile_retailer_suite_remove_unavailable_quote_items",
                ['unavailable_products' => $unavailableProducts]
            );
        }
    }

    /**
     * Retrieve Current Offer for the product.
     */
    private function getCurrentOffer(ProductInterface $product): OfferInterface
    {
        $offer = null;
        $retailerId = $this->getRetailerId();

        if ($retailerId) {
            $offer = $this->offerHelper->getOffer($product, $retailerId);
        }

        return $offer;
    }

    /**
     * Return the current retailer id.
     */
    private function getRetailerId(): ?int
    {
        $retailerId = null;

        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailerId = (int) $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }
}
