# Product synchronization

The products in your OpenCart store and the products in your NextUp ERP instance you currently operate are connected based on the product SKU: 
the SKU code in your store must match the product code in your NextUp ERP instance.

## Introduction

An explicit connection between the products in your store and the products in your NextUp ERP instance must be established before the products can:
- be updated from NextUp ERP;
- be added to documents issued to NextUp ERP from OpenCart orders.

This explicit connection is required for the following reasons:
- because NextUp does not automatically create products when issuing documents (sale invoice or sale order) 
so this is a way to insure that only OpenCart orders for which a document can actually be issued 
are allowed to be exported to NextUp ERP;
- to allow you to have products in your store that are not present in NextUp ERP, and viceversa;
- as an optimization, because only connected products are synchronized, 
so, if not all your store products are also creaded in NextUp ERP, 
then only the ones that are will be updated.

Once this connection is established, the following product information will be extracted from NextUp ERP.

### Stock quantity
For storable NextUp ERP products, the stock quantity will be read, for the warehouse you selected in the extension configuration page.
If the product is out-of-stock, then the out-of-stock status you configured will also be set.
if the product is in-stock, the in-stock status you configured will also be set.

### Price
The product price will be read from NextUp ERP, for the warehouse you selected in the extension configuration page, according to the following rules:
- the price actually assigned to the store product will not include any tax;
- if the warehouse you selected does have a price for the product, then that price will be extracted;
- otherwise: 
	- if you selected an `en-gross` warehouse, then the generic en-gross price will be extracted;
	- if you selected an `en-detail` warehouse, then the generic en-detail price will be extracted.
- if needed, the sale price without VAT will be calculated and will be rounded to 4 digit decimal precision.

### Tax information
The product tax information will be read from Nextup ERP and the following actions will be taken:
- a tax class will be created for the product's VAT quota value, 
if one does not already exist;
- a percentage-type tax rate will be created for the products' VAT quota value, 
if one does not already exist and it will be assigned to the above mentioned tax class, based on the payment address 
and associated with the customer groups and geo zone selected in the extension configuration page;
- the above mentioned tax class will be assigned to the store product.

<div class="mp-page-break"></div>

## Connecting products

### Individually

To connect a single product, navigate to that product's editor page, then to the `Nextup ERP Integration` tab.
If the product cannot be connected, a warning message will be displayed:

<div class="mp-page-screenshot" markdown="1">
![Product connect when no SKU set]($img_base_url$/product-connect-nosku.png "Product connect when no SKU set")
</div>

If the product can be connected, then the `Connect to NextUp ERP by SKU` button will be shown. 
Click it to connect the product. 

<div class="mp-page-screenshot" markdown="1">
![Connect product]($img_base_url$/connect-product.png "Connect product")
</div>

If the operation has been successful, then a confirmation message will be displayed 
and the page will automatically reload in 5 seconds.

<div class="mp-page-break"></div>

### In bulk

To connect products in bulk, navigate to `Catalog - Products`, check the desired products in the product list 
and then use the right-most button in the header button bar, the on with the plug icon:

<div class="mp-page-screenshot" markdown="1">
![Bulk connect button]($img_base_url$/products-bulk-connect.png "Bulk connect button")
</div>

For each selected product, the operation result will be shown below that product's row.

<div class="mp-page-break"></div>

## Consulting product connected status

To see which products are connected to NextUp ERP navigate to `Catalog - Products`, and check the value of the `Connected to NextUp ERP` column:

<div class="mp-page-screenshot" markdown="1">
![Products connected status]($img_base_url$/products-connected-status.png "Products connected status")
</div>

<div class="mp-page-break"></div>

## Consulting NextUp ERP-related product information and actions

To view NextUP ERP-related product information for a product that has previously been connected to NextUp ERP, 
navigate to that product's editor page, then to the `Nextup ERP Integration` tab.

The following information is displayed:
- `Connected to NextUp ERP` - whether or not the product is connected to NextUp ERP;
- `NextUp ERP article ID` - the internal NextUp ERP product ID;
- `NextUp ERP VAT option name` - the VAT option name assigned to the product in NextUp ERP;
- `NextUp ERP VAT quota value` - the VAT quota value  assigned to the product in NextUp ERP;
- `Batch tracking enabled` - whether or not batch tracking is enabled in NextUp ERP for the product.

The following actions are available for connected products:
- `Update entire information from NextUp ERP` - Updates both stock quantities and prices;
- `Update stock information from NextUp ERP` - Only updates stock quantities.

<div class="mp-page-screenshot" markdown="1">
![Product connected info]($img_base_url$/product-connected-info.png "Product connected info")
</div>