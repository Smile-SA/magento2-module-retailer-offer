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

use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Helper\Settings;
use Magento\Framework\App\State;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;

/**
 * Add filtering for the current offer to the catalog.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class LayerPlugin extends AbstractPlugin
{
    /**
     * @var $queryFactory
     */
    private $queryFactory;

    /**
     * LayerPlugin constructor.
     *
     * @param OfferHelper  $offerHelper    The offer Helper
     * @param CurrentStore $currentStore   The Retailer Data Object
     * @param State        $state          The Application State
     * @param Settings     $settingsHelper Settings Helper
     * @param QueryFactory $queryFactory   The Query factory
     */
    public function __construct(
        OfferHelper $offerHelper,
        CurrentStore $currentStore,
        State $state,
        Settings $settingsHelper,
        QueryFactory $queryFactory
    ) {
        $this->queryFactory = $queryFactory;
        parent::__construct($offerHelper, $currentStore, $state, $settingsHelper);
    }

    /**
     * {@inheritDoc}
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        if (!$this->settingsHelper->isNavigationFilterApplied()) {
            return;
        }

        $retailerId = $this->getRetailerId();
        if ($retailerId) {
            $sellerIdFilter       = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.seller_id', 'value' => $retailerId]);
            $isAvailableFilter    = $this->queryFactory->create(QueryInterface::TYPE_TERM, ['field' => 'offer.is_available', 'value' => true]);

            $mustClause   = ['must' => [$sellerIdFilter, $isAvailableFilter]];
            $boolFilter   = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);
            $nestedFilter = $this->queryFactory->create(QueryInterface::TYPE_NESTED, ['path' => 'offer', 'query' => $boolFilter]);

            $collection->addQueryFilter($nestedFilter);
        }
    }
}
