<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;

class AddSimpleProduct implements DataPatchInterface
{
    public function __construct(
        protected ProductRepositoryInterface      $productRepositoryInterface,
        protected State                           $appState,
        protected ProductInterfaceFactory         $productInterfaceFactory,
        protected CollectionFactory       $categoryCollectionFactory,
        protected CategoryLinkManagementInterface $categoryLinkManagement,
        protected StoreManagerInterface           $storeManagerInterface,
    )
    {
    }


    /**
     * @throws CouldNotSaveException
     * @throws StateException
     * @throws InputException
     * @throws LocalizedException
     */
    public function apply(): void
    {
        $this->appState->setAreaCode('frontend');

        $websiteIDs = [$this->storeManagerInterface->getStore()->getWebsiteId()];

        $product = $this->productInterfaceFactory->create();
        $product->setTypeId('simple')
            ->setAttributeSetId(4)
//            ->setWebsiteIds([1])
            ->setWebsiteIds($websiteIDs)
            ->setName('Test Product')
            ->setSku('test-product')
            ->setPrice(10)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData([
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'is_in_stock' => 1,
                'qty' => 100
            ]);

        $this->productRepositoryInterface->save($product);

        $categoryTitles = ['Men'];
        $categoryIds = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', ['in' => $categoryTitles])
            ->getAllIds();
        $this->categoryLinkManagement->assignProductToCategories($product->getSku(), $categoryIds);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
