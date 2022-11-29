# Import new products from NextUp ERP

This tool will help you import into your store products that you have recently added to NextUp ERP. 
To do so, it scans your store, it scans your NextUp ERP products and, 
based on the NextUp ERP product code and on the product SKU in your store, 
it determines what products are new and, therefore, eligible for import.

When comparing, it ignores products in your store that have no SKU set, so, on that basis, 
it may suggest duplicate products.

Please note that determining the new products is a potentially lengthy operation, 
and if your site runs behind a service such as CloudFare, 
the page may time out repeatedly.

## Importing new products

Navigate to `NextUp ERP - Import new products` and wait for the list of new products to be computed.
Select the products you want to import, using the check mark on the left side of each row,
scroll down to the bottom of the list and click the `Import selected products` button.

<div class="mp-page-screenshot" markdown="1">
![Import new products]($img_base_url$/import-new-products.png "Import new products")
</div>

If there are no new products to be imported, a relevant message is displayed.

<div class="mp-page-screenshot" markdown="1">
![Import new products - no new products]($img_base_url$/import-new-products-no-products.png "Import new products - no new products")
</div>