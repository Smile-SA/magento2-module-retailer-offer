<?php

namespace Smile\RetailerOffer\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as DataProviderPrice;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as ResourceModelFilterPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\DecimalFilterTrait;
use Smile\ElasticsuiteCatalog\Model\Search\Request\Field\Mapper;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Custom Price model.
 * Used to work with Offer Prices instead of web prices.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Smile\ElasticsuiteCatalog\Model\Layer\Filter\Price
{
    use DecimalFilterTrait;

    private DataProviderPrice $dataProvider;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        ResourceModelFilterPrice $resource,
        Session $customerSession,
        Algorithm $priceAlgorithm,
        PriceCurrencyInterface $priceCurrency,
        AlgorithmFactory $algorithmFactory,
        PriceFactory $dataProviderFactory,
        private Settings $settingsHelper,
        private CurrentStore $currentStore,
        private QueryFactory $queryFactory,
        Mapper $requestFieldMapper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $queryFactory,
            $requestFieldMapper,
            $data
        );

        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @inheritdoc
     */
    public function apply(RequestInterface $request)
    {
        if (!$this->getRetailerId() || !$this->settingsHelper->useStoreOffers()) {
            return parent::apply($request);
        }

        $filter = $request->getParam($this->getRequestVar());
        if ($filter && !is_array($filter)) {
            $filterParams = explode(',', $filter);
            $filter = $this->dataProvider->validateFilter($filterParams[0]);

            if ($filter) {
                $this->dataProvider->setInterval($filter);
                $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
                if ($priorFilters) {
                    $this->dataProvider->setPriorIntervals($priorFilters);
                }

                [$fromValue, $toValue] = $filter;
                $this->setCurrentValue(['from' => $fromValue, 'to' => $toValue]);

                $this->addQueryFilter($fromValue, $toValue);

                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($this->_renderRangeLabel(empty($fromValue) ? 0 : $fromValue, $toValue), $filter)
                );
            }
        }

        return $this;
    }

    /**
     * Retrieve current retailer Id.
     */
    private function getRetailerId(): ?int
    {
        $retailerId = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailerId = (int) $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }

    /**
     * Compute proper price interval for current Retailer.
     */
    private function addQueryFilter(int $fromValue, int $toValue): void
    {
        $sellerIdFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.seller_id', 'value' => $this->getRetailerId()]
        );
        $mustClause  = ['must' => [$sellerIdFilter]];

        $rangeFilter = $this->queryFactory->create(
            QueryInterface::TYPE_RANGE,
            ['field' => 'offer.price', 'bounds' => ['gte' => $fromValue, 'lte' => $toValue]]
        );
        $mustClause['must'][] = $rangeFilter;

        $boolFilter   = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);
        $nestedFilter = $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            ['path' => 'offer', 'query' => $boolFilter]
        );

        $this->getLayer()->getProductCollection()->addQueryFilter($nestedFilter);
    }
}
