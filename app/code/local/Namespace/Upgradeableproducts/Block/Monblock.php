<?php

/**
 * Upgradeable products block.
 *
 * @package   Namespace_Upgradeableproducts
 * @author    Name <name@email.com>
 * @copyright 2014 Your Company
 */

class Namespace_Upgradeableproducts_Block_Monblock 
    extends Mage_Core_Block_Template
{

    /**
     * Get a generated product thumbnail (80x80) URL.
     * 
     * @param Mage_Catalog_Model_Product $product The product model.
     * 
     * @return string
     */
    protected function _getProductThumbnailUrl(Mage_Catalog_Model_Product $product)
    {
        $url = Mage::helper('catalog/image')->init($product, 'small_image')
            ->keepAspectRatio(true)
            ->resize(80);

        if (!$url) {
            return $product->getImageUrl();
        }

        return $url;
    }

    /**
     * Render upgradeable products based on order history.
     *
     * @todo   Consider re-naming method to be more descriptive.
     * 
     * @return string
     */
    public function methodblock()
    {
        $html = '';

        foreach (Mage::helper('catalog/product')->getCustomerOrders() as $order) {
    		foreach ($order->getAllVisibleItems() as $item) {
                foreach (Mage::helper('catalog/product')->getUpgradeCandidates($item) as $product) {
                    // Note: It would be better to output this from a template
                    $html .= '
                        <tr>
                            <td style="text-align:center;">
                                <img src="' . $this->_getProductThumbnailUrl($product) . '" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                <div style="word-wrap:break-word;">
                                    <a href="' . $product->getProductUrl() . '" target="_blank">' . $product->getName() . '</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                ' . Mage::helper('core')->currency($product->getFinalPrice(), true, true) . '
                            </td>
                        </tr>
                    ';
                }
    		}

            // Wrap rows in table if not empty
            if ($html != '') {
                $html = '
                    <table style="width:120px;border:none;">
                        <tbody>
                            ' . $html . '
                        </tbody>
                    </table>
                ';

                // Not sure what you want to do here, default logic is to break from
                // orders loop because we found at least one match
                break;
            }
        }

        return $html;
    }
}