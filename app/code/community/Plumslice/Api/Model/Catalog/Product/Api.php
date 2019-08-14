<?php
// Define a Class
class Plumslice_Api_Model_Catalog_Product_Api extends Mage_Catalog_Model_Product_Api
{
    public function create($type, $set, $sku, $productData, $store = null)
    {
        // It will allow attribute set name instead id
        if (is_string($set) && !is_numeric($set)) {
            $set = Mage::helper('plumslice_api')->getAttributeSetIdByName($set);
        }

        return parent::create($type, $set, $sku, $productData, $store);
    }

    protected function _prepareDataForSave($product, $productData)
    {
        /* Define @var $product Mage_Catalog_Model_Product */

        if (isset($productData['categories'])) {
            $categoryIds = Mage::helper('plumslice_api/catalog_product')
                ->getCategoryIdsByNames((array) $productData['categories']);
            if (!empty($categoryIds)) {
                $productData['categories'] = array_unique($categoryIds);
            }
        }

        if (isset($productData['website_ids'])) {
            $websiteIds = $productData['website_ids'];
            foreach ($websiteIds as $i => $websiteId) {
                if (!is_numeric($websiteId)) {
                    $website = Mage::app()->getWebsite($websiteId);
                    if ($website->getId()) {
                        $websiteIds[$i] = $website->getId();
                    }
                }
            }
            $product->setWebsiteIds($websiteIds);
            unset($productData['website_ids']);
        }

        foreach ($productData as $code => $value) {
            $productData[$code] = Mage::helper('plumslice_api/catalog_product')
                ->getOptionKeyByLabel($code, $value);
        }

        parent::_prepareDataForSave($product, $productData);

        if (isset($productData['associated_skus'])) {
            $simpleSkus = $productData['associated_skus'];
            $priceChanges = isset($productData['price_changes']) ? $productData['price_changes'] : array();
            $configurableAttributes = isset($productData['configurable_attributes']) ? $productData['configurable_attributes'] : array();
            Mage::helper('plumslice_api/catalog_product')->associateProducts($product, $simpleSkus, $priceChanges, $configurableAttributes);
        }
    }
}