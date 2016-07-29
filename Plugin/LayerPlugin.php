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

/**
 * Replace is in stock native filter on layer.
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

    public function __construct(RetailerData $retailerData, QueryFactory $queryFactory)
    {
        $this->retailerData = $retailerData;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        $retailerData = $this->retailerData->getSectionData();


        if (isset($retailerData['retailer_id']) && $retailerData['retailer_id'] !== null) {
            $retailerId = $retailerData['retailer_id'];
            $pickupDate = $retailerData['pickup_date'];

            $offerStartDateFilter = $this->queryFactory->create(QueryInterface::TYPE_BOOL,
                [
                    'should' => [
                        $this->queryFactory->create(QueryInterface::TYPE_RANGE, ['field' => 'offer.start_date', 'bounds' => ['lte' => $pickupDate]]),
                        $this->queryFactory->create(QueryInterface::TYPE_MISSING, ['field' => 'offer.start_date']),
                    ]
                ]
            );

            $offerEndDateFilter = $this->queryFactory->create(QueryInterface::TYPE_BOOL,
                [
                    'should' => [
                        $this->queryFactory->create(QueryInterface::TYPE_RANGE, ['field' => 'offer.end_date', 'bounds' => ['gte' => $pickupDate]]),
                        $this->queryFactory->create(QueryInterface::TYPE_MISSING, ['field' => 'offer.end_date']),
                    ]
                ]
            );

            $boolFilter     = $this->queryFactory->create(
                QueryInterface::TYPE_BOOL,
                [
                    'must' => [
                        $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.seller_id', 'value' => $retailerId]),
                        $status = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.is_available', 'value' => true]),
                        $offerStartDateFilter,
                        $offerEndDateFilter
                    ]
                ]
            );

            $nestedFilter   = $this->queryFactory->create(QueryInterface::TYPE_NESTED, ['path' => 'offer', 'query' => $boolFilter]);
            $collection->addQueryFilter($nestedFilter);
        }
    }
}
