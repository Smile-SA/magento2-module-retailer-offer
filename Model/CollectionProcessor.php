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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\RetailerOffer\Api\CollectionProcessorInterface;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Collection Processor.
 * Used to filter product collection according current store configuration.
 * Also used to build proper sort orders for collection according to offers data.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class CollectionProcessor implements CollectionProcessorInterface
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
    protected Settings $settingsHelper;

    /**
     * @var QueryFactory
     */
    protected QueryFactory $queryFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * ProductPlugin constructor.
     *
     * @param Offer                 $offerHelper    The offer Helper
     * @param CurrentStore          $currentStore   The Retailer Data Object
     * @param Settings              $settingsHelper Settings Helper
     * @param QueryFactory          $queryFactory   Query Factory
     * @param ScopeConfigInterface  $scopeConfig    Scope Configuration
     */
    public function __construct(
        Offer $offerHelper,
        CurrentStore $currentStore,
        Settings $settingsHelper,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig
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
    public function applyStoreSortOrders(Collection $collection): void
    {
        if ($this->settingsHelper->isDriveMode()) {
            $retailerId = $this->getRetailerId();
            if ($retailerId) {
                $collection->addSortFilterParameters('price', 'offer.price', 'offer', ['offer.seller_id' => $retailerId]);
            }
        }
    }

    /**
     * Retrieve currently chosen retailer id
     *
     * @return int|null
     */
    private function getRetailerId(): int|null
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
     * @return null|RetailerInterface
     */
    private function getRetailer(): null|RetailerInterface
    {
        $retailer = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailer = $this->currentStore->getRetailer();
        }

        return $retailer;
    }
}
