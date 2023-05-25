<?php

namespace Smile\RetailerOffer\Search\Request\Product\Attribute\Aggregation;

use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as FilterDataProviderPrice;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Smile\ElasticsuiteCatalog\Search\Request\Product\Attribute\Aggregation\Price as BasePrice;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Price aggregation.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Price extends BasePrice
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        private Settings $settingsHelper,
        private CurrentStore $currentStore
    ) {
        parent::__construct($scopeConfig, $customerSession);
    }

    /**
     * @inheritdoc
     */
    public function getAggregationData(Attribute $attribute)
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
            $interval = $this->getRangeStepValue();
            if ($interval > 0) {
                $bucketConfig['interval'] = $interval;
            }
        }

        return $bucketConfig;
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
     * Get range calculation value.
     */
    private function getRangeCalculationValue(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_RANGE_CALCULATION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get range step value.
     */
    private function getRangeStepValue(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_RANGE_STEP, ScopeInterface::SCOPE_STORE);
    }
}
