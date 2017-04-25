<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Abstract plugin, contains common methods
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AbstractPlugin
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
    protected $settingsHelper;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ProductPlugin constructor.
     *
     * @param OfferHelper          $offerHelper    The offer Helper
     * @param CurrentStore         $currentStore   The Retailer Data Object
     * @param State                $state          The Application State
     * @param Settings             $settingsHelper Settings Helper
     * @param QueryFactory         $queryFactory   Query Factory
     * @param ScopeConfigInterface $scopeConfig    Scope Configuration
     */
    public function __construct(
        OfferHelper $offerHelper,
        CurrentStore $currentStore,
        State $state,
        Settings $settingsHelper,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->currentStore   = $currentStore;
        $this->helper         = $offerHelper;
        $this->state          = $state;
        $this->settingsHelper = $settingsHelper;
        $this->queryFactory   = $queryFactory;
        $this->scopeConfig    = $scopeConfig;
    }

    /**
     * Retrieve Current Offer for the product.
     *
     * @param ProductInterface $product The product
     *
     * @return OfferInterface
     */
    protected function getCurrentOffer($product)
    {
        $offer      = null;
        $retailerId = $this->getRetailerId();

        if ($retailerId) {
            $offer = $this->helper->getOffer($product, $retailerId);
        }

        return $offer;
    }

    /**
     * Retrieve currently chosen retailer id
     *
     * @return int|null
     */
    protected function getRetailerId()
    {
        $retailerId = null;
        if ($this->getRetailer()) {
            $retailerId = $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }

    /**
     * Retrieve current retailer
     *
     * @return null|\Smile\Retailer\Api\Data\RetailerInterface
     */
    protected function getRetailer()
    {
        $retailer = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailer = $this->currentStore->getRetailer();
        }

        return $retailer;
    }

    /**
     * Filter a product collection according to current Store
     *
     * @param \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $collection Product Collection
     *
     * @return mixed
     */
    protected function applyStoreLimitationToCollection($collection)
    {
        if (!$this->settingsHelper->isDriveMode()) {
            return;
        }

        $retailerId = $this->getRetailerId();
        if ($retailerId) {
            $sellerIdFilter = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.seller_id', 'value' => $retailerId]);
            $mustClause     = ['must' => [$sellerIdFilter]];

            // If out of stock products must be shown, just keep filter on product having an offer for current retailer, wether the offer is available or not.
            if (false === $this->isEnabledShowOutOfStock()) {
                $isAvailableFilter    = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.is_available', 'value' => true]);
                $mustClause['must'][] = $isAvailableFilter;
            }

            $boolFilter   = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);
            $nestedFilter = $this->queryFactory->create(QueryInterface::TYPE_NESTED, ['path' => 'offer', 'query' => $boolFilter]);

            $collection->addQueryFilter($nestedFilter);
        }
    }

    /**
     * Get config value for 'display out of stock' option
     *
     * @return bool
     */
    protected function isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
