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

use Magento\CatalogInventory\Model\Plugin\Layer;
use Smile\Retailer\CustomerData\RetailerData;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Add filtering for the current offer to the catalog.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class LayerPlugin
{
    /**
     *
     * @var RetailerData
     */
    private $retailerData;

    /**
     * @var $queryFactory
     */
    private $queryFactory;

    /**
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * LayerPlugin constructor.
     *
     * @param \Smile\Retailer\CustomerData\RetailerData                 $retailerData   The retailer Data object
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory   The Query factory
     * @param \Smile\RetailerOffer\Helper\Settings                      $settingsHelper Settings Helper
     */
    public function __construct(RetailerData $retailerData, QueryFactory $queryFactory, Settings $settingsHelper)
    {
        $this->retailerData   = $retailerData;
        $this->queryFactory   = $queryFactory;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        if (!$this->settingsHelper->isNavigationFilterApplied()) {
            return;
        }

        $retailerId = $this->retailerData->getRetailerId();
        $pickupDate = $this->retailerData->getPickupDate();

        if ($retailerId !== null && $pickupDate !== null) {
            $sellerIdFilter       = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.seller_id', 'value' => $retailerId]);
            $isAvailableFilter    = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.is_available', 'value' => true]);

            $offerStartDateFilter = $this->queryFactory->create(
                QueryInterface::TYPE_BOOL,
                [
                    'should' => [
                        $this->queryFactory->create(QueryInterface::TYPE_RANGE, ['field' => 'offer.start_date', 'bounds' => ['lte' => $pickupDate]]),
                        $this->queryFactory->create(QueryInterface::TYPE_MISSING, ['field' => 'offer.start_date']),
                    ],
                ]
            );

            $offerEndDateFilter = $this->queryFactory->create(
                QueryInterface::TYPE_BOOL,
                [
                    'should' => [
                        $this->queryFactory->create(QueryInterface::TYPE_RANGE, ['field' => 'offer.end_date', 'bounds' => ['gte' => $pickupDate]]),
                        $this->queryFactory->create(QueryInterface::TYPE_MISSING, ['field' => 'offer.end_date']),
                    ],
                ]
            );

            $mustClause   = ['must' => [$sellerIdFilter, $isAvailableFilter, $offerStartDateFilter, $offerEndDateFilter]];
            $boolFilter   = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);
            $nestedFilter = $this->queryFactory->create(QueryInterface::TYPE_NESTED, ['path' => 'offer', 'query' => $boolFilter]);

            $collection->addQueryFilter($nestedFilter);
        }
    }
}
