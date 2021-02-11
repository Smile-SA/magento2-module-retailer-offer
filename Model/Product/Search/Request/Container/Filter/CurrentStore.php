<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Maxime Leclercq <maxime.leclercq@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Model\Product\Search\Request\Container\Filter;

use Smile\ElasticsuiteCore\Api\Search\Request\Container\FilterInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore as CustomerCurrentStore;

/**
 * Class CurrentStore
 * Append the offer filter in the elasticsearch request if the drive mode is enabled.
 */
class CurrentStore implements FilterInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var CustomerCurrentStore
     */
    private $currentStore;

    /**
     * @var FilterInterface[]
     */
    private $retailerStockFilters;

    /**
     * Search Blacklist filter constructor.
     *
     * @param QueryFactory $queryFactory Query Factory
     * @param CustomerCurrentStore $currentStore Current Store
     * @param Settings $settingsHelper Retailer offer settings helper
     * @param FilterInterface[] $retailerStockFilters Retailer stock filters
     */
    public function __construct(
        QueryFactory $queryFactory,
        CustomerCurrentStore $currentStore,
        Settings $settingsHelper,
        array $retailerStockFilters = []
    ) {
        $this->queryFactory = $queryFactory;
        $this->currentStore = $currentStore;
        $this->settingsHelper = $settingsHelper;
        $this->retailerStockFilters = $retailerStockFilters;
    }

    /**
     * Append offer filter if the drive mode is enabled.
     *
     * @return QueryInterface|null
     */
    public function getFilterQuery()
    {
        $retailer = $this->currentStore->getRetailer();
        if (!$this->settingsHelper->isDriveMode() || !$retailer) {
            return null;
        }

        $sellerIdFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.seller_id', 'value' => $retailer->getId()]
        );
        $mustClause = ['must' => [$sellerIdFilter]];

        // If out of stock products must be shown, just keep filter on product having an offer for current
        // retailer, wether the offer is available or not.
        if (false === $this->settingsHelper->isEnabledShowOutOfStock()) {
            foreach($this->retailerStockFilters as $retailerStockFilter) {
                if (!$retailerStockFilter instanceof FilterInterface) {
                    throw new \RuntimeException('The stock filter is not an FilterInterface');
                }
                $currentStockFilter = $retailerStockFilter->getFilterQuery();
                if ($currentStockFilter !== null) {
                    $mustClause['must'][] = $currentStockFilter;
                }
            }
        }

        $boolFilter = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);
        $nestedFilter = $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            ['path' => 'offer', 'query' => $boolFilter]
        );

        return $nestedFilter;
    }
}
