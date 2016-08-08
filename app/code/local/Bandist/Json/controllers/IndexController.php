<?php

/**
 * Class Bandist_Json_IndexController
 *
 */
class Bandist_Json_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * Limit the number of results returned by the controller
     * @var int
     */
    protected $_maxProducts = 30;

    /**
     * Return an array of products with the following information:
     * name, description, price, list of images URLs
     *
     * @return array
     */
    private function arrayProductCollection() {
        // Retrieve the collection of active products,
        // limited by the number of products defined in $_maxProducts
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect(array('name', 'description', 'price'))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
            ->setPageSize($this->_maxProducts)
            ->setCurPage(1);

        // $backendModel is used to retrieve the list of images associated with the product
        // without the necessity of loading the entire product Entity model
        $backendModel = $collection->getResource()->getAttribute('media_gallery')->getBackend();

        // Create an array with the results
        $result = array();
        foreach ($collection as $_product) {
            $productArray = array();

            // Add the basic attributes to the resulting array
            $productArray['name'] = $_product->getName();
            $productArray['description'] = $_product->getDescription();
            $productArray['price'] = (float) $_product->getPrice();
            $productArray['images'] = array();

            // Load the list of images associated with the product and add it to the result
            $backendModel->afterLoad($_product);
            foreach ($_product->getMediaGalleryImages() as $image) {
                $productArray['images'][] = $image->getUrl();
            }

            // Push the product into the result array
            $result[] = $productArray;
        }

        return $result;
    }

    /**
     * Output the list of products as a JSON
     *
     */
    public function productsAction()
    {
        // Retrieves the list of product as array
        $products = $this->arrayProductCollection();

        // Converts the array of results into a JSON
        $this->getResponse()->setHeader('Content-type','application/json',true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($products));
    }


}
