# Customer synchronization

This extension establishes a connection between the customers in your store and the customers in your NextUp ERP instance, 
first by searching them in NextUp ERP and then, when found, by retaining the NextUp ERP customer code.

If the search yields no result, then the extension automatically creates a customer in your NextUp ERP instance, 
along with a corresponding adress and then retains the newly created customer's code for further reference.

The extension searches for NextUp ERP customers when attempting to issue a document from your store to NextUp ERP 
and does so by using the following criteria, in this order:

1. Billing financial data: Tax attribute and Tax code;
2. Billing e-mail address;
3. Billing phone.

Names are not used as a search criteria, since they can overlap between two different persons or customers.

## Customer data mapping

- the customer's e-mail or customer's billing address e-mail maps to NextUp ERP customer address e-mail;
- the customer's first name (or billing first name) and customer's last name (or billing last name) maps to NextUp ERP customer name;
- the customer's company name maps to NextUp ERP customer name (if present, takes precedence over first name and last name);
- if the customer's VAT code contains a tax attribute (eg. `RO`), then it is mapped to the NextUp ERP customer tax attribute;
- the remainder of the customer's VAT code is mapped to the NextUp ERP customer tax code;
- the customer's country is mapped to the NextUp ERP customer address country (match is automatically done by name by NextUp ERP, so duplicates may appear if names differ for the same country);
- the customer's county is mapped to the NextUp ERP customer address county (match is automatically done by name by NextUp ERP, so duplicates may appear if names differ for the same county);
- the customer's city is mapped to the NextUp ERP customer address city (match is automatically done by name by NextUp ERP, so duplicates may appear if names differ for the same city);
- the customer's entire remaining address (address line 1, address line 2) is mapped to the NextUp ERP customer address street.

## Exported addresses

NextUp ERP supports, for each customer address, an external key.
This external key can be used to establish a correspondence betweem a NextUp ERP customer address and an address from an external system.
So this extension uses the external address key to map between a customer's billing address and a NextUp ERP customer address. 
It uses the following fields to compute the external address key:
- country;
- county;
- city;
- postal code;
- e-mail address;
- phone number.

So, if any of these fields change, it will result in a new NextUp ERP customer address for the same customer (that has already been mapped).

## ANAF Vat code lookup

In the front-end customer address form the extension performs VAT code validation, for the custom field configured as VAT code input in the extension's configuration page.
The validation has two stages:

1. First, it checks whether it has a valid VAT code or Personal Numerical Code format; if not, the validation fails.
2. If so, then ANAF vat code lookup is peformed, and the company name is also returned, if the lookup yields any result.
3. The form's corresponding field is then populated, in case of successful validation, with the company name.

## Managing customer synchronization

There is no direct way to synchronize a store customer to a NextUp ERP customer: the extension does so automatically when a document is issued from the store to NextUp ERP, either as a sale order or a sale invoice.
You can, however, check the status, if a synchronization has been performed for a customer. 
To do so, navigate to `Customers - Customers` and consult the column `Connected to NextUp ERP`: 

<div class="mp-page-screenshot" markdown="1">
![Customer listing column]($img_base_url$/customers-list.png "Customer listing column")
</div>

For more details, navigate to the customer's edit form and check the `NextUp ERP Integration` tab:

<div class="mp-page-screenshot" markdown="1">
![Customer details tab]($img_base_url$/customer-details-tab.png "Customer details tab")
</div>