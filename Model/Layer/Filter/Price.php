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

use Smile\ElasticsuiteCatalog\Model\Layer\Filter\DecimalFilterTrait;
use Smile\ElasticsuiteCatalog\Model\Search\Request\Field\Mapper;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

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
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory               $filterItemFactory   Item filter factory.
     * @param \Magento\Store\Model\StoreManagerInterface                    $storeManager        Store manager.
     * @param \Magento\Catalog\Model\Layer                                  $layer               Search layer.
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder          $itemDataBuilder     Item data builder.
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price       $resource            Price resource.
     * @param \Magento\Customer\Model\Session                               $customerSession     Customer session.
     * @param \Magento\Framework\Search\Dynamic\Algorithm                   $priceAlgorithm      Price algorithm.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface             $priceCurrency       Price currency.
     * @param \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory  $algorithmFactory    Algorithm factory.
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory Data provider.
     * @param \Smile\RetailerOffer\Helper\Settings                          $settingsHelper      Settings Helper.
     * @param \Smile\StoreLocator\CustomerData\CurrentStore                 $currentStore        Current Store.
     * @param \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory     $queryFactory        Query Factory.
     * @param \Smile\ElasticsuiteCatalog\Model\Search\Request\Field\Mapper  $requestFieldMapper  Search request field mapper.
     * @param array                                                         $data                Custom data.
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        \Smile\RetailerOffer\Helper\Settings $settingsHelper,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore,
        \Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory $queryFactory,
        \Smile\ElasticsuiteCatalog\Model\Search\Request\Field\Mapper $requestFieldMapper,
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

        $this->settingsHelper = $settingsHelper;
        $this->dataProvider   = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->currentStore   = $currentStore;
        $this->queryFactory   = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
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
    private function getFilterField()
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
    private function getRetailerId()
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
    private function addQueryFilter($fromValue, $toValue)
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
