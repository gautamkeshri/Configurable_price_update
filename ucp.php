
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
	
	/*
	@$configProd: int
	@$inStock   : boolean
	*/
	private function getAssociated_least_price($configProd,$childProducts){
		$localprice = array();
		foreach ($childProducts as $childid) {
			$child = Mage::getModel('catalog/product')->load($childid);
			if($this->configStockcheck())
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
	
	// private function getBestPriceCollection(){
	// 	if ($this->collectionConfigurable != NULL) {
	// 		foreach ($this->collectionConfigurable as $_product) {
	// 			$productid = Mage::getModel('catalog/product')->load($_product->getEntityId());
	// 			$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($_product->getEntityId());
	// 			if(count($childProducts[0]) > 0) {
	// 				$this->pricelist[$_product->getEntityId()] = $this->getAssociated_least_price($productid);
	// 			}
					
	// 		}
	// 		return $this->pricelist;
	// 	}
	// }
	
	private function setBestPrice($ConfigProduct,$bestpriceprod) {
		$this->log_msg = "";
		$child_prod_id = explode("|",key($bestpriceprod)); 
			if(!empty($child_prod_id[0])){
				
				$ChildProduct = Mage::getModel('catalog/product')->load($child_prod_id[0]);

				var_dump($ConfigProduct->getData());
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
		
		//return $this->log_msg;
	}

	public function main(){
		$ConfigurableCollection = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('type_id', array('eq' => 'configurable'));
		if($ConfigurableCollection->count())
		{
			foreach($ConfigurableCollection as $configProd)
			{
				$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configProd->getEntityId());
			    if(count($childProducts[0]))
			    {	
			    	var_dump($configProd->getData());die;
			        $bestproduct = $this->getAssociated_least_price($configProd,$childProducts[0]);
			        $this->setBestPrice($ConfigProd,$bestproduct);   
			    }
			}	
		} 	
	}	
}

$configObj = new Configpriceupdate();
$email_msg = $configObj->main();
var_dump($email_msg);
?>
