<?php
class Namespace_Products_IndexController extends Mage_Core_Controller_Front_Action
{
   public function indexAction ()
   {
     //echo 'Testing index Controller';
   	  if( !Mage::getSingleton('customer/session')->isLoggedIn() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }
         
        $this->loadLayout();
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation'); // we need this to highlight link at navigation menu on customer's account page.
        if ($navigationBlock) {
            $navigationBlock->setActive('upgrade/index');
        }
      $this->renderLayout();
   }
   
}