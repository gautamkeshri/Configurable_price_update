
<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

class Configpriceupdate 
{
	private $collectionConfigurable;
	private $pricelist;
	private $log_msg;
	
	public function getBasedir() {
		return Mage::getBaseDir();
	}

	private function configStockcheck(){
		return !Mage::helper('cataloginventory')->isShowOutOfStock();
	}	
	public function nonull($val){
		return $val > 0;
	}
	public function getProdcollection()	{
		return $this->collectionConfigurable;
	}
	public function setProdcollection()	{
		$this->collectionConfigurable =  Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('type_id', array('eq' => 'configurable'));
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
		//return $localprice;
	}
	
	public function getBestPriceCollection(){
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
	
	public function setBestPrice() {
		$this->log_msg = "";
		$this->getBestPriceCollection();
		foreach ($this->collectionConfigurable as $_product) {
			$ConfigProduct = Mage::getModel('catalog/product')->load($_product->getEntityId());
			$child_prod_id = explode("|",key($this->pricelist[$_product->getEntityId()])); 
			if(!empty($child_prod_id[0])){
				$ChildProduct = Mage::getModel('catalog/product')->load($child_prod_id[0]);
				//If change in price
				if(($ConfigProduct->getPrice() != $ChildProduct->getPrice()) || ($ConfigProduct->getSpecialPrice() != $ChildProduct->getSpecialPrice())) {
					$ConfigProduct->setPrice($ChildProduct->getPrice()); 
					$ConfigProduct->setSpecialPrice($ChildProduct->getSpecialPrice()); 
					$ConfigProduct->save();
					$childstock = $ChildProduct->getStockItem();

					//$this->log_msg .= "Config price: ".$ConfigProduct->getPrice()." Config Sprice: ".$ConfigProduct->getSpecialPrice()." is_in_stock: ".$ConfigProduct->isInStock()." is saleable: ".$ConfigProduct->isSaleable()." is Avilable: ".$ConfigProduct->isAvailable()."\r\n";
					//$this->log_msg = " Best Price : ".$ChildProduct->getPrice()." Best Sprice:".$ChildProduct->getSpecialPrice()." is_in_stock: ".$childstock->getIsInStock()." is saleable: ".$ChildProduct->isSaleable()." is Avilable: ".$ConfigProduct->isAvailable()."\r\n";
					$this->log_msg .= "Config Sku: ".$ConfigProduct->getSku()." Config price: ".$ConfigProduct->getPrice()." Config Sprice: ".$ConfigProduct->getSpecialPrice()."\r\n";
					$this->log_msg .= "Simple Sku: ".$ChildProduct->getSku()." Best Price : ".$ChildProduct->getPrice()." Best Sprice:".$ChildProduct->getSpecialPrice()."\r\n"; 
				}
				//echo $this->log_msg;		
			}
		}
		return $this->log_msg;
	}
}


$configObj = new Configpriceupdate();
$configObj->setProdcollection();
$email_msg = $configObj->setBestPrice();
var_dump($email_msg);


//$product = Mage::getModel('catalog/product')->load(2);
//$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);

//$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds(2);
//var_dump($childProducts);
//var_dump(Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('type_id', array('eq' => 'configurable'))->getData());

?>
