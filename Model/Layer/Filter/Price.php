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
namespace Smile\RetailerOffer\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as DataProviderPrice;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as ResourceModelFilterPrice;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\DecimalFilterTrait;
use Smile\ElasticsuiteCatalog\Model\Search\Request\Field\Mapper;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Custom Price model.
 * Used to work with Offer Prices instead of web prices.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Price extends \Smile\ElasticsuiteCatalog\Model\Layer\Filter\Price
{
    use DecimalFilterTrait;

    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * @var DataProviderPrice
     */
    private DataProviderPrice $dataProvider;

    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var QueryFactory
     */
    private QueryFactory $queryFactory;

    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param ItemFactory              $filterItemFactory   Item filter factory.
     * @param StoreManagerInterface    $storeManager        Store manager.
     * @param Layer                    $layer               Search layer.
     * @param DataBuilder              $itemDataBuilder     Item data builder.
     * @param ResourceModelFilterPrice $resource            Price resource.
     * @param Session                  $customerSession     Customer session.
     * @param Algorithm                $priceAlgorithm      Price algorithm.
     * @param PriceCurrencyInterface   $priceCurrency       Price currency.
     * @param AlgorithmFactory         $algorithmFactory    Algorithm factory.
     * @param PriceFactory             $dataProviderFactory Data provider.
     * @param Settings                 $settingsHelper      Settings Helper.
     * @param CurrentStore             $currentStore        Current Store.
     * @param QueryFactory             $queryFactory        Query Factory.
     * @param Mapper                   $requestFieldMapper  Search request field mapper.
     * @param array                    $data                Custom data.
     */
    public function __construct(
        ItemFactory                                                  $filterItemFactory,
        StoreManagerInterface                                        $storeManager,
        Layer                                                        $layer,
        DataBuilder              $itemDataBuilder,
        ResourceModelFilterPrice $resource,
        Session                  $customerSession,
        Algorithm                $priceAlgorithm,
        PriceCurrencyInterface   $priceCurrency,
        AlgorithmFactory         $algorithmFactory,
        PriceFactory             $dataProviderFactory,
        Settings                 $settingsHelper,
        CurrentStore             $currentStore,
        QueryFactory             $queryFactory,
        Mapper                   $requestFieldMapper,
        array                    $data = []
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

        $this->settingsHelper = $settingsHelper;
        $this->dataProvider   = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->currentStore   = $currentStore;
        $this->queryFactory   = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RequestInterface $request): self|Price
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

                list($fromValue, $toValue) = $filter;
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
     * Get filter field.
     *
     * @return string
     */
    private function getFilterField(): string
    {
        if (!$this->getRetailerId() || !$this->settingsHelper->useStoreOffers()) {
            return 'price.price';
        }

        return 'offer.price';
    }

    /**
     * Retrieve current retailer Id.
     *
     * @return int|null
     */
    private function getRetailerId(): int|null
    {
        $retailerId = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailerId = (int) $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }

    /**
     * Compute proper price interval for current Retailer.
     *
     * @param int $fromValue The From value for price interval
     * @param int $toValue   The To value for price interval
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
        $nestedFilter = $this->queryFactory->create(QueryInterface::TYPE_NESTED, ['path' => 'offer', 'query' => $boolFilter]);

        $this->getLayer()->getProductCollection()->addQueryFilter($nestedFilter);
    }
}
