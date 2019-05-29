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

use Smile\ElasticsuiteCore\Search\Request\BucketInterface;

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
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Price constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig Scope Config
     * @param \Magento\Customer\Model\Session $customerSession Customer session, if any
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Smile\RetailerOffer\Helper\Settings $settingsHelper,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore
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
    public function getAggregationData(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
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
        if ($calculation === \Magento\Catalog\Model\Layer\Filter\DataProvider\Price::RANGE_CALCULATION_MANUAL) {
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
    private function getRetailerId()
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
    private function getRangeCalculationValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    private function getRangeStepValue()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_STEP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
