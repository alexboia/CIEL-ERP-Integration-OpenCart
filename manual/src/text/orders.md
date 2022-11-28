# Order synchronization

This extension supports issuing and removing the following NextUp ERP document types (for OpenCart orders):
- Sale invoice (for `En-Gross` and `En-Detail` warehouses);
- Sale order (for `En-Gross` warehouses only).

These are strictly bound to your OpenCart store orders, which means:
- One cannot issue NextUp ERP documents independent of OpenCart orders using this extension;
- One cannot list or remove NextUp ERP documents that were not issued using this extension.

Both issuing and removing NextUp ERP documents can be carried out:
- Manually, using the order view widget box provided by this extension;
- Automatically, when the OpenCart order transitions from one state to another (which is configured using this extension's configuration page).

## Order eligibility

In order to be able to issue NextUp ERP documents for a given order, that order needs to satisfy the following conditions:
- The customer that placed the order needs to be connected to a NextUp ERP customer (the extension attempts this automatically when issuing an order);
- All the order products need to be connected each to a corresponding NextUp ERP product (this you need to carry out yourself).

## Discount handling

Discounts applied to OpenCart orders are added to NextUp ERP documents as separate lines, 
one line for each VAT quota value present in the order's items.
For instance, if there is a discount applied and the order has two items - one with 9% VAT quota value and one with 19% percent VAT quota value,
then there will be two discount lines: one that corresponds to the 9% VAT quota value and one that corresponds to the 19% VAT quota value.

To this end, the extension automatically creates a service-type product in NextUp ERP for each discount line type.
The NextUp ERP product created this has the following pattern: `CES_DISCOUNT_[quota_value]`.

## Shipping cost handling

Shipping cost for an order is added to NextUp ERP document issued using this extension as a separate line.
The VAT quota used is the one you selected in the extension configuration page.
In the same page you can opt to prevent the extension from adding shipping costs to the NextUp ERP document.

As is the case with discount handling, the extension creates a service-type product in NextUp ERP, 
with the following pattern: `CES_SHIPPING`.

## Additional NextUp ERP document flags

In the extension configuration page you can opt to have the extension instruct NextUp ERP to mark the document as having VAT applicable on payment.

## Notes on order item-related price calculation

Due to the way OpenCar stores order information this extension needs to perform additional calculations when issuing NextUp ERP documents:
- only final, discounted price is stored, so we need to lookup the product itself to see if there is any product-sale type of discount;
- stored tax is calculated per-unit price, so we need to multiply it by quantity to obtain the total tax due for a product.

In some cases this may result in differences beween what you might expect to see in your NextUp ERP documents 
issued using this extension and what has actually been exported.

Additionally, the following issues also need to be considered, also due to the way OpenCart stores order information:

- product SKU is not stored;
- product tax class id is not stored.

Obviously, since neither the SKU, neither the tax class are stored, the extension needs to look them up 
and, in turn, this means that the values exported to NextUp ERP may not be the same as they were when the order as placed.

## Rounding issues

Finally, you need to understand that, when receiving data for a new document, 
NextUp ERP also performs its own calculations and rounding operations,
so rounding differences may occur between your store order and the corresponding NextUp ERP document.

## Notes on document removal

- If a sale invoice has been issued for an order, then the sale invoice is `removed`, not `cancelled`.
- If a sale order has been issued for an order, then the sale order is neither removed, nor cancelled, 
since the NextUp ERP webservice API does not support removal for sale orders.

<div class="mp-page-break"></div>

## Consulting which orders have NextUp ERP document issued

To see which orders have NextUp ERP documents issued for them, navigate to `Sales - Orders` and check the value of the `Document issued in NextUp ERP` column:

<div class="mp-page-screenshot" markdown="1">
![Order listing column]($img_base_url$/orders-list.png "Order listing column")
</div>

<div class="mp-page-break"></div>

## Consulting NextUp ERP-related order information and actions

To view NextUp ERP-related order information, navigate to an order's details view page, 
then scroll down to the `NextUp ERP Integration` box.

The following information is displayed:
- `Document issued in NextUp ERP` - whether or not a document is issued in NextUp ERP;
- `NextUp ERP issued document type` - the type of document that has been issued (`Sale invoice` or `Sale order`).

The following actions are available for orders that don't have documents issued for them:
- `Issue NextUp ERP document`.

<div class="mp-page-screenshot" markdown="1">
![Order without document]($img_base_url$/order-view-document-not-issued.png "Order without document")
</div>

If an order has some issues with its products and, as a result, a document cannot be issued, details are displayed:

<div class="mp-page-screenshot" markdown="1">
![Order without document, with product issues]($img_base_url$/order-with-product-issues.png "Order without document, with product issues")
</div>

<div class="mp-page-break"></div>

The following actions are available for orders that have documents issued for them:
- `Remove NextUp ERP document`.

<div class="mp-page-screenshot" markdown="1">
![Order with document]($img_base_url$/order-view-document-issued.png "Order with document")
</div>