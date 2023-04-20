<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Fanny DECLERCK <fadec@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Search\Request\Product\Attribute\Aggregation;

use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as FilterDataProviderPrice;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Price aggregation
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Fanny DECLERCK <fadec@smile.fr>
 */
class Price extends \Smile\ElasticsuiteCatalog\Search\Request\Product\Attribute\Aggregation\Price
{
    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * Price constructor.
     *
     * @param ScopeConfigInterface  $scopeConfig        Scope Config
     * @param Session               $customerSession    Customer session, if any
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Settings $settingsHelper,
        CurrentStore $currentStore
    ) {
        parent::__construct($scopeConfig, $customerSession);

        $this->settingsHelper = $settingsHelper;
        $this->currentStore   = $currentStore;
        $this->scopeConfig     = $scopeConfig;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregationData(Attribute $attribute): array
    {
        $retailerId = $this->getRetailerId();
        if (!$retailerId || !$this->settingsHelper->useStoreOffers()) {
            return parent::getAggregationData($attribute);
        }

        $bucketConfig = [
            'name' => 'offer.price',
            'type' => BucketInterface::TYPE_HISTOGRAM,
            'nestedFilter' => ['offer.seller_id' => $retailerId], 'minDocCount' => 1,
        ];

        $calculation = $this->getRangeCalculationValue();
        if ($calculation === FilterDataProviderPrice::RANGE_CALCULATION_MANUAL) {
            if ((int)$this->getRangeStepValue() > 0) {
                $bucketConfig['interval'] = (int)$this->getRangeStepValue();
            }
        }

        return $bucketConfig;
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
     * @return mixed
     */
    private function getRangeCalculationValue(): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    private function getRangeStepValue(): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_STEP,
            ScopeInterface::SCOPE_STORE
        );
    }
}
