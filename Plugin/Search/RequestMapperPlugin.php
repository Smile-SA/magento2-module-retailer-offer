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
    private CurrentStore $currentStore;

    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * RequestMapperPlugin constructor.
     *
     * @param CurrentStore  $currentStore   Current Store
     * @param Settings      $settingsHelper Settings Helper
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
     * @param RequestMapper                     $subject                    Current request mapper object
     * @param array                             $result                     Current Sort order configuraiton
     * @param ContainerConfigurationInterface   $containerConfiguration     Container configuration
     * @param SearchCriteriaInterface           $searchCriteria             Search criteria
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSortOrders(
        RequestMapper $subject,
        array $result,
        ContainerConfigurationInterface $containerConfiguration,
        SearchCriteriaInterface $searchCriteria
    ): array {
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

    /**
     * Post process catalog filters.
     *
     * @param RequestMapper                     $subject                    Request mapper.
     * @param array                             $result                     Original filters.
     * @param ContainerConfigurationInterface   $containerConfiguration     Container configuration.
     * @param SearchCriteriaInterface           $searchCriteria             Search criteria.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetFilters(
        RequestMapper $subject,
        array $result,
        ContainerConfigurationInterface $containerConfiguration,
        SearchCriteriaInterface $searchCriteria
    ): array {
        $retailer = $this->currentStore->getRetailer();
        if (!$this->settingsHelper->isDriveMode() || !$retailer) {
            return $result;
        }

        foreach ($result as $fieldName => $filterValue) {
            if ($fieldName !== 'price.price') {
                continue;
            }
            unset($result[$fieldName]);
            $result['offer.price'] = $filterValue;
        }

        return $result;
    }
}
