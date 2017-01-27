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
use Magento\Framework\App\State;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\RetailerOffer\Helper\Settings;

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
     * @var OfferHelper
     */
    private $helper;

    /**
     * @var CurrentStore
     */
    private $currentStore;

    /**
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * ProductPlugin constructor.
     *
     * @param OfferHelper  $offerHelper    The offer Helper
     * @param CurrentStore $currentStore   The Retailer Data Object
     * @param State        $state          The Application State
     * @param Settings     $settingsHelper Settings Helper
     */
    public function __construct(OfferHelper $offerHelper, CurrentStore $currentStore, State $state, Settings $settingsHelper)
    {
        $this->currentStore   = $currentStore;
        $this->helper         = $offerHelper;
        $this->state          = $state;
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
    public function aroundIsAvailable(Product $product, \Closure $proceed)
    {
        $isAvailable = $proceed();

        if ($this->useStoreOffers()) {
            $isAvailable = false;
            $offer       = $this->getCurrentOffer($product);

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
    public function aroundGetPrice(Product $product, \Closure $proceed)
    {
        $price = $proceed();

        if ($this->useStoreOffers()) {
            $offer = $this->getCurrentOffer($product);

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
    public function aroundGetSpecialPrice(Product $product, \Closure $proceed)
    {
        $price = $proceed();

        if ($this->useStoreOffers()) {
            $offer = $this->getCurrentOffer($product);

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
     *
     * @return bool
     */
    public function aroundGetFinalPrice(Product $product, \Closure $proceed)
    {
        $price = $proceed();

        if ($this->useStoreOffers()) {
            $offer = $this->getCurrentOffer($product);

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
    public function aroundGetMinimalPrice(Product $product, \Closure $proceed)
    {
        return $this->aroundGetFinalPrice($product, $proceed);
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
        $retailerId = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailerId = $this->currentStore->getRetailer()->getId();
        }

        if ($retailerId) {
            $offer = $this->helper->getOffer($product, $retailerId);
        }

        return $offer;
    }

    /**
     * Check if we should use store offers
     *
     * @return bool
     */
    private function useStoreOffers()
    {
        return !($this->isAdmin() || !$this->settingsHelper->isNavigationFilterApplied());
    }

    /**
     * Check if we are browsing admin area
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isAdmin()
    {
        return $this->state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }
}
