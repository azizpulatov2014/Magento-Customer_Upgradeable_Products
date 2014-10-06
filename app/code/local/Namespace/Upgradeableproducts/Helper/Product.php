<?php

/**
 * Product helper extensions.
 *
 * @package   Namespace_Upgradeableproducts
 * @author    Name <name@email.com>
 * @copyright 2014 Your Company
 */

class Namespace_Upgradeableproducts_Helper_Product
    extends Mage_Catalog_Helper_Product
{

    /**
     * A simple hierarchy of upgrade paths for your
     * virtual products. An ideal implementation
     * would be to build this feature into each
     * product as some kind of serialized attribute
     * which can be configured via admin UI.
     * 
     * @var array
     */
    protected $_upgradeMatrix = array(
        // Replace with your own SKUs
        
        // Basic edition upgrade paths
        '01-SBASIC' => array(
            '01-SPROUP',    // Pro upgrade (from Basic) > Not Visible Individually
            '01-SBPUPG',    // Platinum upgrade (from Basic) > Not Visible Individually
        ),
        // Professional edition upgrade paths
        '01-SPROFE' => array(
            '01-SPPLUP'     // Platinum upgrade (from Pro) > Not Visible Individually
        ),
        // Platinum edition upgrade paths
        '01-SPLATI' => array(
            // No upgrades available
        ),
    );

    /**
     * Get all orders for the currently logged in customer.
     *
     * @todo   Consider accepting arbitrary customer IDs, or
     *         fetch ID/hash from query params if included
     *         from a generated URL in an e-mail message.
     * 
     * @return Varien_Data_Collection
     */
    public function getCustomerOrders()
    {
        $orders = null;

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $orders = Mage::getResourceModel('sales/order_collection')
                //->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                ->setOrder('created_at', 'desc')
                ;
        }

        // Return a consistent object type even if empty
        if (empty($orders)) {
            return new Varien_Data_Collection();
        }

        return $orders;
    }

    /**
     * Get the available product upgrades for the given item.
     * 
     * @param Mage_Sales_Model_Order_Item $item The order item.
     * 
     * @return array
     */
    public function getUpgradeCandidates(Mage_Sales_Model_Order_Item $item)
    {
        $candidates = array();

        // Verify that order item has an upgrade path
        // Item SKU must exist as a top-level element of upgrade matrix
        if (array_key_exists($item->getSku(), $this->_upgradeMatrix)) {
            foreach ($this->_upgradeMatrix[$item->getSku()] as $upgradeSku) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $upgradeSku);

                // Add product as candidate if actually found in catalog
                if ($product->getId()) {
                    // Store by product ID for quick scanning in isVisibleToCustomer method
                    $candidates[$product->getId()] = $product;
                }
            }
        }

        return $candidates;
    }

    /**
     * Check if the product should be visible to the currently logged in customer.
     * 
     * @param Mage_Catalog_Model_Product $product The product model.
     * 
     * @return boolean
     */
    public function isVisibleToCustomer(Mage_Catalog_Model_Product $product)
    {
        // We need to check customer order history to verify that 
        // this product is a qualifying upgrade to the customer. 
        // If it is, then we will return true to force visibility.
        
        // Check every order
        foreach ($this->getCustomerOrders() as $order) {
            // For each order, inspect every item
            foreach ($order->getAllVisibleItems() as $item) {
                // For each item, fetch all available upgrades
                $candidates = $this->getUpgradeCandidates($item);
                
                // Consider requested product visible if found in upgrade candidates
                if (array_key_exists($product->getId(), $candidates)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Modified from parent.
     * 
     * Check if a product can be shown.
     *
     * @param Mage_Catalog_Model_Product|int $product The product model or ID.
     * @param string                         $where   Not used.
     * 
     * @return boolean
     */
    public function canShow($product, $where = 'catalog')
    {
        if (is_int($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }

        /* @var $product Mage_Catalog_Model_Product */

        if (!$product->getId()) {
            return false;
        }

        // In addition to basic visibility checking, we can override
        // if product should be visible to the customer as an upgrade
        return 
            ( $product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility() ) ||
            $this->isVisibleToCustomer($product);
    }

}
