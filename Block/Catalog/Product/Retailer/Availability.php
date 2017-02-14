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
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Smile\Map\Api\MapProviderInterface;
use Smile\Map\Model\AddressFormatter;
use Smile\Offer\Model\ResourceModel\Offer\CollectionFactory;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory as RetailerCollectionFactory;
use Smile\StoreLocator\Helper\Data as StoreLocatorHelper;

/**
 * Block rendering availability in store for a given product.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Availability extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Smile\Offer\Model\ResourceModel\Offer\Grid\CollectionFactory
     */
    private $offerCollectionFactory;

    /**
     * @var RetailerCollectionFactory
     */
    private $retailerCollectionFactory;

    /**
     * @var StoreLocatorHelper
     */
    private $storeLocatorHelper;

    /**
     * @var \Smile\Map\Model\AddressFormatter
     */
    private $addressFormatter;

    /**
     * @var \Smile\Map\Api\MapInterface
     */
    private $map;

    /**
     * Availability constructor.
     *
     * @param Context                    $context                   Application context
     * @param EncoderInterface           $urlEncoder                Url encoder
     * @param JsonEncoderInterface       $jsonEncoder               Json Encoder
     * @param StringUtils                $string                    String utils
     * @param Product                    $productHelper             Product Helper
     * @param ConfigInterface            $productTypeConfig         Product Type Configuration
     * @param FormatInterface            $localeFormat              Locale Format
     * @param Session                    $customerSession           Customer Session
     * @param ProductRepositoryInterface $productRepository         Product Repository
     * @param PriceCurrencyInterface     $priceCurrency             Price Currency
     * @param CollectionFactory          $offerCollectionFactory    Offer Collection
     * @param RetailerCollectionFactory  $retailerCollectionFactory Retailer Collection
     * @param StoreLocatorHelper         $storeLocatorHelper        Store Locator Helper
     * @param AddressFormatter           $addressFormatter          Address Formatter
     * @param MapProviderInterface       $mapProvider               Map Provider
     * @param array                      $data                      Block Data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        CollectionFactory $offerCollectionFactory,
        RetailerCollectionFactory $retailerCollectionFactory,
        StoreLocatorHelper $storeLocatorHelper,
        AddressFormatter $addressFormatter,
        MapProviderInterface $mapProvider,
        array $data = []
    ) {
        $this->offerCollectionFactory = $offerCollectionFactory;
        $this->retailerCollectionFactory = $retailerCollectionFactory;
        $this->storeLocatorHelper = $storeLocatorHelper;
        $this->addressFormatter = $addressFormatter;
        $this->map = $mapProvider->getMap();

        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
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
     * Retrieve availability by store for the current product.
     *
     * @return array
     */
    private function getStoreOffers()
    {
        $storeOffers = [];

        $offerCollection = $this->offerCollectionFactory->create();
        $offerCollection->addProductFilter($this->getProduct()->getId());

        $offerByRetailer = [];
        foreach ($offerCollection as $offer) {
            $offerByRetailer[(int) $offer->getSellerId()] = $offer;
        }

        /** @var \Smile\Retailer\Model\ResourceModel\Retailer\Collection $retailerCollection */
        $retailerCollection = $this->retailerCollectionFactory->create();
        $retailerCollection->addAttributeToSelect('*')->addFieldToFilter('is_active', (int) true);

        foreach ($retailerCollection as $retailer) {
            $offer = [
                'sellerId'     => (int) $retailer->getId(),
                'name'         => $retailer->getName(),
                'address'      => $this->addressFormatter->formatAddress($retailer->getAddress(), AddressFormatter::FORMAT_ONELINE),
                'latitude'     => $retailer->getAddress()->getCoordinates()->getLatitude(),
                'longitude'    => $retailer->getAddress()->getCoordinates()->getLongitude(),
                'url'          => $this->storeLocatorHelper->getRetailerUrl($retailer),
                'setStoreData' => $this->getSetStorePostData($retailer),
                'isAvailable'  => false,
            ];

            if (isset($offerByRetailer[(int) $retailer->getId()])) {
                $offer['isAvailable'] = (bool) $offerByRetailer[(int) $retailer->getId()]->isAvailable();
            }

            $storeOffers[] = $offer;
        }

        return $storeOffers;
    }

    /**
     * Get the JSON post data used to build the set store link.
     *
     * @param \Smile\Retailer\Api\Data\RetailerInterface $retailer The store
     *
     * @return string
     */
    private function getSetStorePostData($retailer)
    {
        $setUrl   = $this->_urlBuilder->getUrl('storelocator/store/set');
        $postData = ['id' => $retailer->getId()];

        return ['action' => $setUrl, 'data' => $postData];
    }
}
