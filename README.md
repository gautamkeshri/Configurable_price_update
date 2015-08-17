# Configurable_price_update
In magento we can set price of configurable product using this simple script.
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
