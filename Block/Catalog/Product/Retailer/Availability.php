<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Block\Catalog\Product\Retailer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Customer\Model\Session;
use Smile\Map\Api\MapProviderInterface;
use Smile\Map\Model\AddressFormatter;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Offer\Model\Offer;
use Smile\Offer\Model\OfferManagement;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory as RetailerCollectionFactory;

/**
 * Block rendering availability in store for a given product.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Availability extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Smile\Offer\Model\OfferManagement
     */
    protected $offerManagement;

    /**
     * @var RetailerCollectionFactory
     */
    protected $retailerCollectionFactory;

    /**
     * @var \Smile\Map\Model\AddressFormatter
     */
    protected $addressFormatter;

    /**
     * @var \Smile\Map\Api\MapInterface
     */
    protected $map;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var array
     */
    protected $storeOffers = null;

    /**
     * Availability constructor.
     *
     * @param Context                    $context                   Application context
     * @param ProductRepositoryInterface $productRepository         Product Repository
     * @param OfferManagement            $offerManagement           Offer Management
     * @param RetailerCollectionFactory  $retailerCollectionFactory Retailer Collection
     * @param AddressFormatter           $addressFormatter          Address Formatter
     * @param MapProviderInterface       $mapProvider               Map Provider
     * @param array                      $data                      Block Data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        OfferManagement $offerManagement,
        RetailerCollectionFactory $retailerCollectionFactory,
        AddressFormatter $addressFormatter,
        MapProviderInterface $mapProvider,
        array $data = []
    ) {
        $this->offerManagement = $offerManagement;
        $this->retailerCollectionFactory = $retailerCollectionFactory;
        $this->addressFormatter = $addressFormatter;
        $this->map = $mapProvider->getMap();
        $this->productRepository = $productRepository;
        $this->coreRegistry = $context->getRegistry();

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getJsLayout()
    {
        $jsLayout = $this->jsLayout;

        $jsLayout['components']['catalog-product-retailer-availability']['productId'] = $this->getProduct()->getId();

        $jsLayout['components']['catalog-product-retailer-availability']['storeOffers'] = $this->getStoreOffers();

        $jsLayout['components']['catalog-product-retailer-availability']['children']['geocoder']['provider'] = $this->map->getIdentifier();
        $jsLayout['components']['catalog-product-retailer-availability']['children']['geocoder'] = array_merge(
            $jsLayout['components']['catalog-product-retailer-availability']['children']['geocoder'],
            $this->map->getConfig()
        );

        return json_encode($jsLayout);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = $this->getProduct()->getIdentities();

        foreach ($this->getStoreOffers() as $offer) {
            if (isset($offer[OfferInterface::OFFER_ID])) {
                $identities[] = Offer::CACHE_TAG . '_' . $offer[OfferInterface::OFFER_ID];
            }
        }

        return $identities;
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        if (!$this->coreRegistry->registry('product') && $this->getProductId()) {
            return $this->productRepository->getById($this->getProductId());
        }

        return $this->coreRegistry->registry('product');
    }

    /**
     * Retrieve availability by store for the current product.
     *
     * @return array
     */
    protected function getStoreOffers()
    {
        if ($this->storeOffers === null) {
            $storeOffers = [];

            $offerByRetailer = [];
            foreach ($this->offerManagement->getProductOffers($this->getProduct()->getId()) as $offer) {
                $offerByRetailer[(int) $offer->getSellerId()] = $offer;
            }

            /** @var \Smile\Retailer\Model\ResourceModel\Retailer\Collection $retailerCollection */
            $retailerCollection = $this->retailerCollectionFactory->create();
            $retailerCollection->addAttributeToSelect('*')->addFieldToFilter('is_active', (int) true);

            /** @var RetailerInterface $retailer */
            foreach ($retailerCollection as $retailer) {
                $address = $retailer->getExtensionAttributes()->getAddress();
                $offer = [
                    'sellerId'     => (int) $retailer->getId(),
                    'name'         => $retailer->getName(),
                    'address'      => $this->addressFormatter->formatAddress($address, AddressFormatter::FORMAT_ONELINE),
                    'latitude'     => $address->getCoordinates()->getLatitude(),
                    'longitude'    => $address->getCoordinates()->getLongitude(),
                    'setStoreData' => $this->getSetStorePostData($retailer),
                    'isAvailable'  => false,
                ];

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
     *
     * @param \Smile\Retailer\Api\Data\RetailerInterface $retailer The store
     *
     * @return string
     */
    protected function getSetStorePostData($retailer)
    {
        $setUrl   = $this->_urlBuilder->getUrl('storelocator/store/set');
        $postData = ['id' => $retailer->getId()];

        return ['action' => $setUrl, 'data' => $postData];
    }
}
