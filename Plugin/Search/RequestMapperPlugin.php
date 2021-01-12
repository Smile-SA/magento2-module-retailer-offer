<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Maxime Leclercq <maxime.leclercq@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin\Search;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\ContainerConfigurationInterface;
use Smile\ElasticsuiteCore\Model\Search\RequestMapper;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Request Mapper Plugin.
 * Used to change the price order by a offer price order.
 */
class RequestMapperPlugin
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
     * RequestMapperPlugin constructor.
     *
     * @param CurrentStore $currentStore Current Store
     * @param Settings $settingsHelper Settings Helper
     */
    public function __construct(
        CurrentStore $currentStore,
        Settings $settingsHelper
    ) {
        $this->currentStore = $currentStore;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Replace the price order by a offer price order.
     *
     * @param RequestMapper $subject Current request mapper object
     * @param $result Current sort order configuraiton
     * @param ContainerConfigurationInterface $containerConfiguration Container configuration
     * @param SearchCriteriaInterface $searchCriteria Search criteria
     *
     * @return array
     */
    public function afterGetSortOrders(
        RequestMapper $subject,
        $result,
        ContainerConfigurationInterface $containerConfiguration,
        SearchCriteriaInterface $searchCriteria
    ) {
        $retailer = $this->currentStore->getRetailer();
        if (!$this->settingsHelper->isDriveMode() || !$retailer) {
            return $result;
        }

        foreach ($result as $sortField => $sortParams) {
            if ($sortField !== 'price.price') {
                continue;
            }
            unset($result[$sortField]);
            $sortParams['nestedFilter'] = ['offer.seller_id' => $retailer->getId()];
            $sortParams['nestedPath'] = 'offer';
            $result['offer.price'] = $sortParams;
        }

        return $result;
    }
}
