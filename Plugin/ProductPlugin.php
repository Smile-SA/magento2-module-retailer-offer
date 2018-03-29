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
     * @var \Smile\RetailerOffer\Helper\Offer
     */
    private $helper;

    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * ProductPlugin constructor.
     *
     * @param \Smile\RetailerOffer\Helper\Offer             $offerHelper    The offer Helper
     * @param \Smile\StoreLocator\CustomerData\CurrentStore $currentStore   The Retailer Data Object
     * @param \Smile\RetailerOffer\Helper\Settings          $settingsHelper Settings Helper
     */
    public function __construct(
        \Smile\RetailerOffer\Helper\Offer $offerHelper,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore,
        \Smile\RetailerOffer\Helper\Settings $settingsHelper
    ) {
        $this->currentStore   = $currentStore;
        $this->helper         = $offerHelper;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Return offer availability (if any) instead of the product one.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We do not need to call the parent method.
     *
     * @param \Magento\Catalog\Model\Product $product The product
     * @param \Closure                       $proceed The overridden isAvailable() method
     *
     * @return bool
     */
    public function aroundIsAvailable(\Magento\Catalog\Model\Product $product, \Closure $proceed)
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
     * @param \Magento\Catalog\Model\Product $product The product
     * @param \Closure                       $proceed The overridden getPrice() method
     *
     * @return bool
     */
    public function aroundGetPrice(\Magento\Catalog\Model\Product $product, \Closure $proceed)
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
     * @param \Magento\Catalog\Model\Product $product The product
     * @param \Closure                       $proceed The overridden getSpecialPrice() method
     *
     * @return bool
     */
    public function aroundGetSpecialPrice(\Magento\Catalog\Model\Product $product, \Closure $proceed)
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
     * @param \Magento\Catalog\Model\Product $product The product
     * @param \Closure                       $proceed The overridden getFinalPrice() method
     * @param int                            $qty     The quantity added to the cart
     *
     * @return bool
     */
    public function aroundGetFinalPrice(\Magento\Catalog\Model\Product $product, \Closure $proceed, $qty)
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
     * @param \Magento\Catalog\Model\Product $product The product
     * @param \Closure                       $proceed The overridden getFinalPrice() method
     *
     * @return bool
     */
    public function aroundGetMinimalPrice(\Magento\Catalog\Model\Product $product, \Closure $proceed)
    {
        return $this->aroundGetFinalPrice($product, $proceed);
    }
}
