<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    public const SEARCH_PLACEHOLDER_XML_PATH = 'smile_retailersuite_retailer_base_settings/search/placeholder';

    /**
     * Get config by config path.
     */
    public function getConfigByPath(string $path, string $scope = ScopeInterface::SCOPE_STORE): mixed
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * Get placeholder for search input of store_locator, default: City, Zipcode, Address, ...
     */
    public function getSearchPlaceholder(): string
    {
        return (string) $this->getConfigByPath(self::SEARCH_PLACEHOLDER_XML_PATH) ?: 'City, Zipcode, Address ...';
    }
}
