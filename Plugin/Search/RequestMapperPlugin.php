<?php

declare(strict_types=1);

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
    public function __construct(
        private CurrentStore $currentStore,
        private Settings $settingsHelper
    ) {
    }

    /**
     * Replace the price order by a offer price order.
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
