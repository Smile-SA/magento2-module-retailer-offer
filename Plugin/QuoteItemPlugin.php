<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Model\Config;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Check if the offer price of a previously added quote item has changed.
 */
class QuoteItemPlugin
{
    public function __construct(
        private Offer $offerHelper,
        private Settings $settingsHelper,
        private ManagerInterface $eventManager,
        private Config $taxConfig
    ) {
    }

    /**
     * Check if offer price has changed for quote item.
     *
     * This can happens if an item is already in cart when the offer price is changed.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetProduct(
        Item $item,
        Closure $proceed,
        Product $product
    ): Item {
        /** @var Item $resultItem */
        $resultItem = $proceed($product);
        $offerPrice = null;

        if ($this->settingsHelper->isDriveMode()) {
            $currentOffer = $this->offerHelper->getCurrentOffer($product);
            if ($currentOffer) {
                $offerPrice = $currentOffer->getSpecialPrice()
                    ? $currentOffer->getSpecialPrice()
                    : $currentOffer->getPrice();
            }
            $resultItemPrice = $this->getQuoteItemPrice($resultItem);

            if ($offerPrice && $resultItemPrice && ((float) $resultItemPrice !== (float) $offerPrice)) {
                $resultItem->setPrice($offerPrice);
                /** @var ?Quote $quote */
                $quote = $resultItem->getQuote();
                if ($quote) {
                    $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                    $resultItem->setQuote($quote);
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
     */
    private function getQuoteItemPrice(Item $item): float
    {
        return (float) (!$this->taxConfig->priceIncludesTax() ? $item->getPrice() : $item->getPriceInclTax());
    }
}
