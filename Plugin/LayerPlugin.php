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

use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * {@inheritDoc}
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        $this->applyStoreLimitationToCollection($collection);
    }
}
