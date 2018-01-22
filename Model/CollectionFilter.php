<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Model;

use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Api\CollectionFilterInterface;

/**
 * Collection Processor.
 * Used to filter product collection according current store configuration.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class CollectionFilter implements CollectionFilterInterface
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
     * @param \Smile\RetailerOffer\Helper\Offer                         $offerHelper    The offer Helper
     * @param \Smile\StoreLocator\CustomerData\CurrentStore             $currentStore   The Retailer Data Object
     * @param \Smile\RetailerOffer\Helper\Settings                      $settingsHelper Settings Helper
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory   Query Factory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface        $scopeConfig    Scope Configuration
     */
    public function __construct(
        \Smile\RetailerOffer\Helper\Offer $offerHelper,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore,
        \Smile\RetailerOffer\Helper\Settings $settingsHelper,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->currentStore   = $currentStore;
        $this->helper         = $offerHelper;
        $this->settingsHelper = $settingsHelper;
        $this->queryFactory   = $queryFactory;
        $this->scopeConfig    = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function applyStoreLimitation(\Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $collection)
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
     * Retrieve currently chosen retailer id
     *
     * @return int|null
     */
    private function getRetailerId()
    {
        $retailerId = null;
        if ($this->getRetailer()) {
            $retailerId = $this->getRetailer()->getId();
        }

        return $retailerId;
    }

    /**
     * Retrieve current retailer
     *
     * @return null|\Smile\Retailer\Api\Data\RetailerInterface
     */
    private function getRetailer()
    {
        $retailer = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailer = $this->currentStore->getRetailer();
        }

        return $retailer;
    }

    /**
     * Get config value for 'display out of stock' option
     *
     * @return bool
     */
    private function isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
