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
namespace Smile\RetailerOffer\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\Offer\Api\OfferManagementInterface;
use Smile\Retailer\CustomerData\RetailerData;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Remove unavailable products (according to their current offer) from current quote
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RemoveUnavailableProducts implements ObserverInterface
{
    /**
     * @var OfferHelper
     */
    private $helper;

    /**
     * @var CurrentStore
     */
    private $currentStore;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * RemoveUnavailableProducts constructor.
     *
     * @param ManagerInterface $eventManager The Event Manager
     * @param OfferHelper      $offerHelper  The offer Helper
     * @param CurrentStore     $currentStore The Retailer Data object
     */
    public function __construct(ManagerInterface $eventManager, OfferHelper $offerHelper, CurrentStore $currentStore)
    {
        $this->eventManager = $eventManager;
        $this->helper       = $offerHelper;
        $this->currentStore = $currentStore;
    }

    /**
     * Remove unavailable products (according to their current offer) from current quote
     *
     * @param EventObserver $observer The observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
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

    /**
     * Retrieve Current Offer for the product.
     *
     * @param ProductInterface $product The product
     *
     * @return OfferInterface
     */
    private function getCurrentOffer($product)
    {
        $offer = null;
        $retailerId = $this->getRetailerId();

        if ($retailerId) {
            $offer = $this->helper->getOffer($product, $retailerId);
        }

        return $offer;
    }

    /**
     * Return the current retailer id.
     *
     * @return int
     */
    private function getRetailerId()
    {
        $retailerId = null;

        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailerId = $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }
}
