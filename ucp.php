
<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

class Configpriceupdate 
{
	private $collectionConfigurable;
	private $pricelist;
	private $log_msg;
	private $system_stock_check;
		
	private function configStockcheck(){
		return !Mage::helper('cataloginventory')->isShowOutOfStock();
	}	
	
	private function getAssociated_least_price($configProd){
		$localprice = array();
		$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configProd->getEntityId());
		foreach ($childProducts[0] as $childid) {
			$child = Mage::getModel('catalog/product')->load($childid);
			if(($this->configStockcheck()) && ($configProd->getStatus() == 1)) 					// Check config stock setting
			{					
				if($child->getIsInStock()) 					// is_in_stock
				{
					$localprice[$child->getEntityId()."|p"] = $child->getPrice();
					if((int)$child->getSpecialPrice() > 100)
						$localprice[$child->getEntityId()."|s"] = $child->getSpecialPrice();	
				}					
			}else
			{
				$localprice[$child->getEntityId()."|p"] = $child->getPrice();
					if((int)$child->getSpecialPrice() > 100)
						$localprice[$child->getEntityId()."|s"] = $child->getSpecialPrice();	
			}
			
		}
		asort($localprice);
		return array_slice($localprice, 0, 1);
	}
	
	private function getBestPriceCollection(){
		if ($this->collectionConfigurable != NULL) {
			foreach ($this->collectionConfigurable as $_product) {
				$productid = Mage::getModel('catalog/product')->load($_product->getEntityId());
				$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($_product->getEntityId());
				if(count($childProducts[0]) > 0) {
					$this->pricelist[$_product->getEntityId()] = $this->getAssociated_least_price($productid);
				}
					
			}
			return $this->pricelist;
		}
	}
	
	private function getBestPrice() {
		//$this->log_msg = "";
		$this->getBestPriceCollection();
		foreach ($this->collectionConfigurable as $_product) {
			$this->log_msg = "";
			$ConfigProduct = Mage::getModel('catalog/product')->load($_product->getEntityId());
			$child_prod_id = explode("|",key($this->pricelist[$_product->getEntityId()])); 
			if(!empty($child_prod_id[0])){
				$ChildProduct = Mage::getModel('catalog/product')->load($child_prod_id[0]);
				//If change in price
				if(($ConfigProduct->getPrice() != $ChildProduct->getPrice()) || ($ConfigProduct->getSpecialPrice() != $ChildProduct->getSpecialPrice())) {
					//$ConfigProduct->setPrice($ChildProduct->getPrice()); 
					//$ConfigProduct->setSpecialPrice($ChildProduct->getSpecialPrice()); 
					//$ConfigProduct->save();
					$childstock = $ChildProduct->getStockItem();

					//$this->log_msg .= "Config price: ".$ConfigProduct->getPrice()." Config Sprice: ".$ConfigProduct->getSpecialPrice()." is_in_stock: ".$ConfigProduct->isInStock()." is saleable: ".$ConfigProduct->isSaleable()." is Avilable: ".$ConfigProduct->isAvailable()."\r\n";
					//$this->log_msg = " Best Price : ".$ChildProduct->getPrice()." Best Sprice:".$ChildProduct->getSpecialPrice()." is_in_stock: ".$childstock->getIsInStock()." is saleable: ".$ChildProduct->isSaleable()." is Avilable: ".$ConfigProduct->isAvailable()."\r\n";
					$this->log_msg .= "Config Sku: ".$ConfigProduct->getSku()." Config price: ".$ConfigProduct->getPrice()." Config Sprice: ".$ConfigProduct->getSpecialPrice()."\r\n";
					$this->log_msg .= "Simple Sku: ".$ChildProduct->getSku()." Best Price : ".$ChildProduct->getPrice()." Best Sprice:".$ChildProduct->getSpecialPrice()."\r\n"; 
				}
				echo $this->log_msg;		
			}
		}
		//return $this->log_msg;
	}
	public function main(){
		$this->collectionConfigurable = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('type_id', array('eq' => 'configurable'));
		self::getBestPriceCollection();
		var_dump($this->pricelist);die;
		$email_msg = $configObj->setBestPrice();
		return $email_msg;
	}
}

/*
	case 1: nothing
		Configurable product with No child 
	
	case 2: update with least
		Configurable product with few in_stock and few out_of_stock product with show_out_of_stock = false


	case 3: update with in_stock least
		Configurable product with few in_stock and few out_of_stock product with show_out_of_stock = true	

	case 4: update with in_stock least
		Configurable product with all in_stock with show_out_of_stock = false

	case 5: update with in_stock least
		Configurable product with all out_of_stock with show_out_of_stock = false	

	case 6: update with in_stock least
		Configurable product with all in_stock with show_out_of_stock = true

	case 7: update with in_stock least
		Configurable product with all out_of_stock with show_out_of_stock = true


	//######################################
	
	$prodCollection = $configurable->getCollecttion('configurable'); 
	$UpdatePriceCollection = getBestPriceCollection();
	$pricelist = array();

	foreach($prodCollection){
		if(has_child($prodCollection)){
			$show_out_of_stock => system->inventory -> show_out_of_stock (boolean)
			if($show_out_of_stock){
				$ChildProdId = getAssociated_least_price($ConfigProdId,true);
			}else{
				$ChildProdId = getAssociated_least_price($ConfigProdId,false);
			}
			setBestPrice($ConfigProdId,$ChildProdId);	
		}else{
			// Do nothing as no child
		}
	}
		
	//######################################
*/
$configObj = new Configpriceupdate();
$email_msg = $configObj->main();
var_dump($email_msg);
?>
