<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Model\Product;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Replace is in stock native filter on layer.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class ProductPlugin
{
    /**
     * @var Offer
     */
    private Offer $helper;

    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * ProductPlugin constructor.
     *
     * @param Offer        $offerHelper    The offer Helper
     * @param CurrentStore $currentStore   The Retailer Data Object
     * @param Settings     $settingsHelper Settings Helper
     */
    public function __construct(
        Offer $offerHelper,
        CurrentStore $currentStore,
        Settings $settingsHelper
    ) {
        $this->currentStore   = $currentStore;
        $this->helper         = $offerHelper;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Return offer availability (if any) instead of the product one.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We do not need to call the parent method.
     *
     * @param Product   $product The product
     * @param \Closure  $proceed The overridden isAvailable() method
     *
     * @return bool
     */
    public function aroundIsAvailable(Product $product, \Closure $proceed): bool
    {
        $isAvailable = $proceed();

        if ($this->settingsHelper->useStoreOffers()) {
            $isAvailable = false;
            $offer       = $this->helper->getCurrentOffer($product);

            if ($offer !== null && $offer->isAvailable()) {
                $isAvailable = (bool) $offer->isAvailable();
            }
        }

        return $isAvailable;
    }

    /**
     * Return offer price (if any) instead of the product one.
     *
     * @param Product   $product The product
     * @param \Closure  $proceed The overridden getPrice() method
     *
     * @return float|null
     */
    public function aroundGetPrice(Product $product, \Closure $proceed): float|null
    {
        $price = $proceed();

        if ($this->settingsHelper->useStoreOffers()) {
            $offer = $this->helper->getCurrentOffer($product);

            if ($offer && $offer->getPrice()) {
                $price = $offer->getPrice();
            } elseif ($offer && $offer->getSpecialPrice()) {
                $price = $offer->getSpecialPrice();
            }
        }

        return $price;
    }

    /**
     * Return offer special price (if any) instead of the product one.
     *
     * @param Product   $product The product
     * @param \Closure  $proceed The overridden getSpecialPrice() method
     *
     * @return float|null
     */
    public function aroundGetSpecialPrice(Product $product, \Closure $proceed): float|null
    {
        $price = $proceed();

        if ($this->settingsHelper->useStoreOffers()) {
            $offer = $this->helper->getCurrentOffer($product);

            if ($offer && $offer->getSpecialPrice()) {
                $price = $offer->getSpecialPrice();
            }
        }

        return $price;
    }

    /**
     * Return offer final price (if any) instead of the product one.
     *
     * @param Product   $product The product
     * @param \Closure  $proceed The overridden getFinalPrice() method
     * @param ?float    $qty     The quantity added to the cart
     *
     * @return float|null
     */
    public function aroundGetFinalPrice(Product $product, \Closure $proceed, ?float $qty = null): float|null
    {
        $price = $proceed($qty);

        if ($this->settingsHelper->useStoreOffers()) {
            $offer = $this->helper->getCurrentOffer($product);

            if ($offer) {
                if ($offer->getPrice() && $offer->getSpecialPrice()) {
                    $price = min($offer->getPrice(), $offer->getSpecialPrice());
                } elseif ($offer->getPrice()) {
                    $price = $offer->getPrice();
                } elseif ($offer->getSpecialPrice()) {
                    $price = $offer->getSpecialPrice();
                }
            }
        }

        return $price;
    }

    /**
     * Return offer minimal price (if any) instead of the product one.
     *
     * @param Product   $product The product
     * @param \Closure  $proceed The overridden getFinalPrice() method
     *
     * @return float|null
     */
    public function aroundGetMinimalPrice(Product $product, \Closure $proceed): float|null
    {
        return $this->aroundGetFinalPrice($product, $proceed);
    }
}
