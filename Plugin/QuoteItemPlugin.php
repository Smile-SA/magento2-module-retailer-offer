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
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Model\Config;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

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
     * @var Offer
     */
    private Offer $offerHelper;

    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @var Config
     */
    private Config $taxConfig;

    /**
     * ProductPlugin constructor.
     *
     * @param Offer             $offerHelper    The offer Helper
     * @param CurrentStore      $currentStore   The Current Store object
     * @param Settings          $settingsHelper Settings Helper
     * @param ManagerInterface  $eventManager   The Event Manager
     * @param Config            $taxConfig      Tax config.
     */
    public function __construct(
        Offer $offerHelper,
        CurrentStore $currentStore,
        Settings $settingsHelper,
        ManagerInterface $eventManager,
        Config $taxConfig
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
     * @param Item      $item    The Quote Item
     * @param \Closure  $proceed The overridden setProduct() method
     * @param Product   $product The product
     *
     * @return bool
     */
    public function aroundSetProduct(
        Item $item,
        \Closure $proceed,
        Product $product
    ): Item {
        $resultItem = null;
        $offerPrice = null;

        /** @var Item $resultItem */
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
     * @param Item $item
     *
     * @return float|null
     */
    private function getQuoteItemPrice(Item $item): float|null
    {
        return !$this->taxConfig->priceIncludesTax() ? $item->getPrice() : $item->getPriceInclTax();
    }
}
