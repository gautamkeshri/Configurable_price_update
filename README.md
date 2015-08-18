# Configurable_price_update
In magento we can set price of configurable product using this simple script.

###############################################################################

case 1: Do-nothing
	Configurable product with No child 

case 2: Update: with least price
	Configurable product with few in_stock and few out_of_stock product with show_out_of_stock = false

case 3: Update: with in_stock least price
	Configurable product with few in_stock and few out_of_stock product with show_out_of_stock = true	

case 4: Update: with in_stock least price
	Configurable product with all in_stock with show_out_of_stock = false

case 5: Update: with in_stock least price
	Configurable product with all out_of_stock with show_out_of_stock = false	

case 6: Update: with in_stock least price
	Configurable product with all in_stock with show_out_of_stock = true

case 7: Update: with in_stock least price
	Configurable product with all out_of_stock with show_out_of_stock = true

