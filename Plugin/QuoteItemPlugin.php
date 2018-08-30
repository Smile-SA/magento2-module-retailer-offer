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
     * @var \Smile\RetailerOffer\Helper\Offer
     */
    private $offerHelper;

    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * ProductPlugin constructor.
     *
     * @param \Smile\RetailerOffer\Helper\Offer             $offerHelper    The offer Helper
     * @param \Smile\StoreLocator\CustomerData\CurrentStore $currentStore   The Current Store object
     * @param \Smile\RetailerOffer\Helper\Settings          $settingsHelper Settings Helper
     * @param \Magento\Framework\Event\ManagerInterface     $eventManager   The Event Manager
     * @param \Magento\Tax\Model\Config                     $taxConfig      Tax config.
     */
    public function __construct(
        \Smile\RetailerOffer\Helper\Offer $offerHelper,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore,
        \Smile\RetailerOffer\Helper\Settings $settingsHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->offerHelper    = $offerHelper;
        $this->currentStore   = $currentStore;
        $this->settingsHelper = $settingsHelper;
        $this->eventManager   = $eventManager;
        $this->taxConfig      = $taxConfig;
    }

    /**
     * Check if offer price has changed for quote item.
     * This can happens if an item is already in cart when the offer price is changed.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We do not need the original Item.
     *
     * @param \Magento\Quote\Model\Quote\Item $item    The Quote Item
     * @param \Closure                        $proceed The overridden setProduct() method
     * @param \Magento\Catalog\Model\Product  $product The product
     *
     * @return bool
     */
    public function aroundSetProduct(
        \Magento\Quote\Model\Quote\Item $item,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $resultItem = null;
        $offerPrice = null;

        /** @var \Magento\Quote\Model\Quote\Item $resultItem */
        $resultItem = $proceed($product);

        if ($this->settingsHelper->isDriveMode()) {
            $currentOffer = $this->offerHelper->getCurrentOffer($product);
            if ($currentOffer) {
                $offerPrice = $currentOffer->getSpecialPrice() ? $currentOffer->getSpecialPrice() : $currentOffer->getPrice();
            }
            $resultItemPrice = $this->getQuoteItemPrice($resultItem);

            if ($offerPrice && $resultItemPrice && ((float) $resultItemPrice !== (float) $offerPrice)) {
                $resultItem->setPrice($offerPrice);
                if ($resultItem->getQuote()) {
                    $resultItem->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
                }

                $this->eventManager->dispatch(
                    "smile_retailer_suite_quote_item_price_change",
                    ['item' => $resultItem, 'product' => $product]
                );
            }
        }

        return $resultItem;
    }

    /**
     * Return quote item price (include or exclude tax).
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return float|null
     */
    private function getQuoteItemPrice(\Magento\Quote\Model\Quote\Item $item)
    {
        return !$this->taxConfig->priceIncludesTax() ? $item->getPrice() : $item->getPriceInclTax();
    }
}
