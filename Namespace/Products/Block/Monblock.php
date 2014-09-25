<?php
class Namespace_Products_Block_Monblock extends Mage_Core_Block_Template
{
     public function methodblock()
     {
     	$_basic = "1234346565768890"; // sample value
     	$_pro = "hbm000"; // sample value


	    if(Mage::getSingleton('customer/session')->isLoggedIn()){
				
			$orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->setOrder('created_at', 'desc')
        	;

        	foreach ($orders as $order) {

        		$items = $order->getAllVisibleItems();
        		foreach ($items as $item) {
        			$sku = $item->getSku();
        			if ($sku == $_basic) {
        				//echo "This customer can upgrade to Pro and Platinum versions"."<br/>";
        				$_product = Mage::getModel('catalog/product')->load(891);
						$product_string="<table border='0' width=120px>";
						$product_string.="<tr><td><img src='".$_product->getImageUrl()."'></td></tr>";
						$product_string.="<tr><td align='center'><div style='word-wrap: break-word;'><a href='".$_product->getProductUrl()."' target='_blank'>".$_product->getName()."</a></div></td></tr>";
						$price = bcmul($_product->getPrice(),100,0)/100; // to leave two decimals in the number and cut other WITHOUT rounding.
						$product_string.="<tr><td align='center'>$".$price."</td></tr>";
						$product_string.="</table>";

						echo $product_string;
        			};
        			if ($sku == $_pro) {
        				//echo "This customer can upgrade to Platinum version"."<br/>";
        			};
        		}
        		
        	}
	   	}	
     }
}