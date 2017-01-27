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
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\RetailerOffer\Helper\Settings;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\State;
/**
 * Check if the offer price of a previously added quote item has changed.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 */
class QuoteItemPlugin extends AbstractPlugin
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * ProductPlugin constructor.
     *
     * @param OfferHelper      $offerHelper    The offer Helper
     * @param CurrentStore     $currentStore   The Current Store object
     * @param Settings         $settingsHelper Settings Helper
     * @param State            $state          Application State
     * @param ManagerInterface $eventManager   The Event Manager
     */
    public function __construct(OfferHelper $offerHelper, CurrentStore $currentStore, Settings $settingsHelper, State $state, ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        parent::__construct($offerHelper, $currentStore, $state, $settingsHelper);
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
        $resultItem = null;

        $currentOffer = $this->getCurrentOffer($product);

        if (!$this->settingsHelper->isNavigationFilterApplied()) {
            $currentOffer = $this->getCurrentOffer($product);

            if (!$currentOffer) {

            }
        }

        if ($resultItem === null) {
            $offerPrice   = null;
            $currentOffer = $this->getCurrentOffer($product);
            if ($currentOffer) {
                $offerPrice = $currentOffer->getSpecialPrice() ? $currentOffer->getSpecialPrice() : $currentOffer->getPrice();
            }

            /** @var \Magento\Quote\Model\Quote\Item $resultItem */
            $resultItem = $proceed($product);

            /*if ($offerPrice && $resultItem->getPrice() && ((float) $resultItem->getPrice() !== (float) $offerPrice)) {
                $resultItem->setPrice($offerPrice);
                if ($resultItem->getQuote()) {
                    $resultItem->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
                }
            }*/
        }

        $resultItem = $proceed($product);

        $this->eventManager->dispatch(
            "smile_retailer_suite_quote_item_price_change",
            ['item' => $resultItem, 'product' => $product]
        );

        return $resultItem;
    }
}
