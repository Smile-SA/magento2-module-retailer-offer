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

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Retailer\CustomerData\RetailerData;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Magento\Framework\Event\ManagerInterface;

/**
 * Check if the offer price of a previously added quote item has changed.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 */
class QuoteItemPlugin
{
    /**
     * @var OfferHelper
     */
    private $helper;

    /**
     * @var \Smile\Retailer\CustomerData\RetailerData
     */
    private $retailerData;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * ProductPlugin constructor.
     *
     * @param ManagerInterface $eventManager The Event Manager
     * @param OfferHelper      $offerHelper  The offer Helper
     * @param RetailerData     $retailerData The Retailer Data Object
     */
    public function __construct(ManagerInterface $eventManager, OfferHelper $offerHelper, RetailerData $retailerData)
    {
        $this->retailerData = $retailerData;
        $this->helper       = $offerHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * Check if offer price has changed for quote item.
     * This can happens if an item is already in cart when the offer price is changed.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We do not need the original Item.
     *
     * @param \Magento\Quote\Model\Quote\Item $item    The Quote Item
     * @param \Closure                        $proceed The overridden setProduct() method
     * @param \Magento\Catalog\Model\Product  $product The product
     *
     * @return bool
     */
    public function aroundSetProduct(Item $item, \Closure $proceed, Product $product)
    {
        $offerPrice   = null;
        $currentOffer = $this->getCurrentOffer($product);
        if ($currentOffer) {
            $offerPrice = $currentOffer->getSpecialPrice() ? $currentOffer->getSpecialPrice() : $currentOffer->getPrice();
        }

        /** @var \Magento\Quote\Model\Quote\Item $resultItem */
        $resultItem = $proceed($product);

        if ($offerPrice && $resultItem->getPrice() && ((float) $resultItem->getPrice() !== (float) $offerPrice)) {
            if ($resultItem->getQuote()) {
                $resultItem->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
            }

            $this->eventManager->dispatch(
                "smile_retailer_suite_quote_item_price_change",
                ['item' => $resultItem, 'product' => $product]
            );
        }

        return $resultItem;
    }

    /**
     * Return the current pickup date.
     *
     * @return string
     */
    private function getPickupDate()
    {
        return $this->retailerData->getPickupDate();
    }

    /**
     * Return the current retailer id.
     *
     * @return int
     */
    private function getRetailerId()
    {
        return $this->retailerData->getRetailerId();
    }

    /**
     * Retrieve Current Offer for the product.
     *
     * @param Product $product The product
     *
     * @return OfferInterface
     */
    private function getCurrentOffer($product)
    {
        $offer      = null;
        $retailerId = $this->getRetailerId();
        $pickupDate = $this->getPickupDate();

        if ($retailerId && $pickupDate) {
            $offer = $this->helper->getOffer($product, $retailerId, $pickupDate);
        }

        return $offer;
    }
}
