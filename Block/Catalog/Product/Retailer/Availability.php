<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Block\Catalog\Product\Retailer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Directory\Model\Region;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Smile\Map\Api\MapInterface;
use Smile\Map\Api\MapProviderInterface;
use Smile\Map\Model\AddressFormatter;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Offer\Model\Offer;
use Smile\Offer\Model\OfferManagement;
use Smile\Retailer\Api\Data\RetailerExtensionInterface;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory as RetailerCollectionFactory;
use Smile\RetailerOffer\Helper\Config as HelperConfig;
use Smile\StoreLocator\Helper\Data;
use Smile\StoreLocator\Helper\Schedule;
use Smile\StoreLocator\Model\Retailer\ScheduleManagement;

/**
 * Block rendering availability in store for a given product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Availability extends Template implements IdentityInterface
{
    protected MapInterface $map;
    protected Registry $coreRegistry;
    protected ?array $storeOffers = null;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        protected ProductRepositoryInterface $productRepository,
        protected OfferManagement $offerManagement,
        protected RetailerCollectionFactory $retailerCollectionFactory,
        protected AddressFormatter $addressFormatter,
        protected Region $region,
        protected HelperConfig $helperConfig,
        MapProviderInterface $mapProvider,
        protected ScheduleManagement $scheduleManagement,
        protected Schedule $scheduleHelper,
        protected Data $storeLocatorHelper,
        array $data = []
    ) {
        $this->map = $mapProvider->getMap();
        $this->coreRegistry = $context->getRegistry();

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $jsLayout = $this->jsLayout;

        $jsLayout['components']['catalog-product-retailer-availability']['productId'] = $this->getProduct()->getId();
        $jsLayout['components']['catalog-product-retailer-availability']['storeOffers'] = $this->getStoreOffers();
        $jsLayout['components']['catalog-product-retailer-availability']['searchPlaceholderText'] = $this
            ->helperConfig->getSearchPlaceholder();

        // smile-geocoder child
        $jsLayout['components']['catalog-product-retailer-availability']['children']['geocoder']['provider']
            = $this->map->getIdentifier();
        $jsLayout['components']['catalog-product-retailer-availability']['children']['geocoder'] = array_merge(
            $jsLayout['components']['catalog-product-retailer-availability']['children']['geocoder'],
            $this->map->getConfig()
        );

        // smile-map child
        $jsLayout['components']['catalog-product-retailer-availability']['children']['map']['provider'] = $this->map
            ->getIdentifier();
        $jsLayout['components']['catalog-product-retailer-availability']['children']['map']['markers']
            = $this->getStoreOffers();
        $jsLayout['components']['catalog-product-retailer-availability']['children']['map'] = array_merge(
            $jsLayout['components']['catalog-product-retailer-availability']['children']['map'],
            $this->map->getConfig()
        );

        return json_encode($jsLayout);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        /** @var ProductModel $product */
        $product = $this->getProduct();
        $identities = $product->getIdentities();

        foreach ($this->getStoreOffers() as $offer) {
            if (isset($offer[OfferInterface::OFFER_ID])) {
                $identities[] = Offer::CACHE_TAG . '_' . $offer[OfferInterface::OFFER_ID];
            }
        }

        return $identities;
    }

    /**
     * Retrieve current product model.
     */
    protected function getProduct(): ProductInterface|ProductModel|null
    {
        if (!$this->coreRegistry->registry('product') && $this->getProductId()) {
            return $this->productRepository->getById($this->getProductId());
        }

        return $this->coreRegistry->registry('product');
    }

    /**
     * Retrieve availability by store for the current product.
     */
    protected function getStoreOffers(): array
    {
        if ($this->storeOffers === null) {
            $storeOffers = [];

            $offerByRetailer = [];
            $produdtId = (int) $this->getProduct()->getId();
            foreach ($this->offerManagement->getProductOffers($produdtId) as $offer) {
                $offerByRetailer[(int) $offer->getSellerId()] = $offer;
            }

            /** @var \Smile\Retailer\Model\ResourceModel\Retailer\Collection $retailerCollection */
            $retailerCollection = $this->retailerCollectionFactory->create();
            $retailerCollection->addAttributeToSelect('*')
                ->addFieldToFilter('is_active', 1);

            /** @var RetailerInterface $retailer */
            foreach ($retailerCollection as $retailer) {
                /** @var RetailerExtensionInterface $retailerExtensionInterface */
                $retailerExtensionInterface = $retailer->getExtensionAttributes();
                $address = $retailerExtensionInterface->getAddress();
                $regionName = $this->region->load($address->getRegionId())->getName() ?: null;
                $offer = [
                    'sellerId' => (int) $retailer->getId(),
                    'name' => $retailer->getName(),
                    'address' => $this->addressFormatter->formatAddress($address, AddressFormatter::FORMAT_ONELINE),
                    'postCode' => $address->getPostcode(),
                    'region' => $regionName,
                    'city' => $address->getCity(),
                    'latitude' => $address->getCoordinates()->getLatitude(),
                    'longitude' => $address->getCoordinates()->getLongitude(),
                    'setStoreData' => $this->getSetStorePostData($retailer),
                    'isAvailable'  => false,
                    'url' => $this->storeLocatorHelper->getRetailerUrl($retailer),
                ];

                // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $offer['schedule'] = array_merge(
                    $this->scheduleHelper->getConfig(),
                    [
                        'calendar' => $this->scheduleManagement->getCalendar($retailer),
                        'openingHours' => $this->scheduleManagement->getWeekOpeningHours($retailer),
                        'specialOpeningHours' => $retailerExtensionInterface->getSpecialOpeningHours(),
                    ]
                );
                // phpcs:enable

                if (isset($offerByRetailer[(int) $retailer->getId()])) {
                    $offer['isAvailable'] = (bool) $offerByRetailer[(int) $retailer->getId()]->isAvailable();
                    $offer[OfferInterface::OFFER_ID] = $offerByRetailer[(int) $retailer->getId()]->getId();
                }

                $storeOffers[] = $offer;
            }

            $this->storeOffers = $storeOffers;
        }

        return $this->storeOffers;
    }

    /**
     * Get the JSON post data used to build the set store link.
     */
    protected function getSetStorePostData(RetailerInterface $retailer): array
    {
        $setUrl = $this->_urlBuilder->getUrl('storelocator/store/set');
        $postData = ['id' => $retailer->getId()];

        return ['action' => $setUrl, 'data' => $postData];
    }
}
