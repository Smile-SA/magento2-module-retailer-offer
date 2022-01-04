<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @author    Maxime Leclercq <maxime.leclercq@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Model\Rule\Condition\Product\SpecialAttribute;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Customer\Model\Session as CustomerSession;
use Smile\ElasticsuiteCatalogRule\Api\Rule\Condition\Product\SpecialAttributeInterface;
use Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Smile\ElasticsuiteCatalogRule\Model\Rule\Condition\Product\SpecialAttribute\IsDiscount as BaseIsDiscount;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Is Discount rule condition.
 * Override the virtual category discount rule condition for use offer data when in drive mode.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 */
class IsDiscount extends BaseIsDiscount implements SpecialAttributeInterface
{
    /**
     * @var CurrentStore
     */
    private $currentStore;

    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * IsDiscount constructor.
     *
     * @param Yesno           $booleanSource   Boolean source model
     * @param CustomerSession $customerSession Customer Session
     * @param QueryFactory    $queryFactory    Query factory
     * @param CurrentStore    $currentStore    Current Store
     * @param Settings        $settingsHelper  Setting Helper
     */
    public function __construct(
        Yesno $booleanSource,
        CustomerSession $customerSession,
        QueryFactory $queryFactory,
        CurrentStore $currentStore,
        Settings $settingsHelper
    ) {
        $this->currentStore = $currentStore;
        $this->settingsHelper = $settingsHelper;

        parent::__construct($booleanSource, $customerSession, $queryFactory);
    }

    /**
     * {@inheritdoc}
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
     *
     * @param integer $retailerId Retailer Id
     *
     * @return QueryInterface
     */
    private function getIsDiscountForCurrentRetailerQuery($retailerId)
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
     *
     * @return QueryInterface
     */
    private function getIsDiscountOffersQuery()
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            ['path' => 'offer', 'query' => $this->getIsDiscountOfferTermQuery()]
        );
    }

    /**
     * Retrieve term query to match discounted offers.
     *
     * @return QueryInterface
     */
    private function getIsDiscountOfferTermQuery()
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.is_discount', 'value' => true]
        );
    }
}
