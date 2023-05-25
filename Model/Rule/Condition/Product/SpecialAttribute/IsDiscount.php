<?php

namespace Smile\RetailerOffer\Model\Rule\Condition\Product\SpecialAttribute;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Customer\Model\Session as CustomerSession;
use Smile\ElasticsuiteCatalogRule\Api\Rule\Condition\Product\SpecialAttributeInterface;
use Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product\SpecialAttribute\IsDiscount as BaseIsDiscount;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Is Discount rule condition.
 * Override the virtual category discount rule condition for use offer data when in drive mode.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class IsDiscount extends BaseIsDiscount implements SpecialAttributeInterface
{
    public function __construct(
        Yesno $booleanSource,
        CustomerSession $customerSession,
        QueryFactory $queryFactory,
        private CurrentStore $currentStore,
        private Settings $settingsHelper
    ) {

        parent::__construct($booleanSource, $customerSession, $queryFactory);
    }

    /**
     * @inheritdoc
     */
    public function getSearchQuery(ProductCondition $condition)
    {
        $query = parent::getSearchQuery($condition);

        if ($this->settingsHelper->isDriveMode()) {
            $query = $this->getIsDiscountOffersQuery();
            $currentRetailer = $this->currentStore->getRetailer();
            if ($currentRetailer && $currentRetailer->getId()) {
                $query = $this->getIsDiscountForCurrentRetailerQuery($currentRetailer->getId());
            }
        }

        return $query;
    }

    /**
     * Retrieve query based on 'offer.is_discount' for current retailer.
     */
    private function getIsDiscountForCurrentRetailerQuery(int $retailerId): QueryInterface
    {
        $sellerIdFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.seller_id', 'value' => $retailerId]
        );
        $mustClause  = ['must' => [$sellerIdFilter]];
        $mustClause['must'][] = $this->getIsDiscountOfferTermQuery();

        return $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            ['path' => 'offer', 'query' => $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause)]
        );
    }

    /**
     * Filter products having a discounted offer.
     */
    private function getIsDiscountOffersQuery(): QueryInterface
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            ['path' => 'offer', 'query' => $this->getIsDiscountOfferTermQuery()]
        );
    }

    /**
     * Retrieve term query to match discounted offers.
     */
    private function getIsDiscountOfferTermQuery(): QueryInterface
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.is_discount', 'value' => true]
        );
    }
}
