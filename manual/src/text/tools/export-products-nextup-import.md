# Export store products for NextUp Import

This tool will export the products in your store in a format suitable for direct import into NextUp ERP.
The resulting data will be made available to you as a CSV file, with the following structure:

| Column name | Data Source |
| ----------- | ----------- |
| `Cod` (Code) | OpenCart product's SKU property |
| `Denumire` (Name) | OpenCart product's name |
| `Unitate de masura` (Measurement unit) | By default set to `Buc` |
| `Cod de bare` (Barcode) | Empty |
| `Articol stocabil` (Whether or not is a storable product) | By default set to `1`, which means is a storable product) |
| `Pret vanzare fara tva` (Product price without VAT) | OpenCart product's regular price |
| `Pret vanzare cu tva` (Product price with VAT) | OpenCart product's regular price + calculated VAT amount |
| `Moneda pret vanzare` (Product price currency) | By default set to `RON` |
| `Sablon cont` (Product template) | By default set to `Marfuri` |
| `Optiune tva pentru vanzarea cu amanuntul` (VAT option for sale operations) | By default set to `Taxabile`, for products with non zero VAT quota and to `Neimpozabile` for products with zero VAT quota |
| `Cota tva pentru vanzarea cu amanuntul` (VAT quota for sale operations) | OpenCart products' VAT quota value (numeric, sans `%` symbol) |

Please note that the above mentioned default values cannot be changed at this time.

<div class="mp-page-break"></div>

## Generating the export file:

Navigate to `NextUp ERP - Export products for NextUp Import` and click the `Generate file` button.
When the file generation is completed, a download prompt will be displayed by your browser.

Save the file to a destination of your choosing, adjust it to your needs and then import it to NextUp.
Describing the NextUp ERP product import operation is beyond the scope of this document.

<div class="mp-page-screenshot" markdown="1">
![Export products for NextUp import]($img_base_url$/export-products-for-nextup-import.png "Export products for NextUp import")
</div>