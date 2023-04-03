# What this extension does

## What it does do

1. Product synchronization: 
	- update product stock quantities from NextUp ERP;
	- update product prices from NextUp ERP;
	- update product tax information from NextUp ERP;
	- products that are imported directly also inherit the NextUp product name and code (as SKU).

2. Customer synchronization:
	- Customer and customer address information is exported to NextUp ERP when issuing documents from your store to NextUp ERP;
	- Existing NextUp ERP customers are updated;
	- Non-existing NextUp ERP customers are created.

3. Order synchronization:
	- For each order a NextUp ERP document can be issued: either a sale invoice or a sale order;
	- A document can be issued with either valid or temporary status;
	- Sale invoices issued with a valid status trigger automatic stock quantity updates for the corresponding order items.

4. Order document removal:
	- Each sale invoice issued from your store can be removed (sale orders cannot be removed via current NextUp ERP API);
	- Removed sale invoices previously issued with a valid status trigger automatic stock quantity updates for the corresponding order items.

## What it does not do

1. Product synchronization: 
	- Product stock quantities and prices (and all product information for that matter) are not updated in real time from NextUp ERP to your shop, since there is no way to know when they have changed in NextUp ERP;
	- Products are NOT exported to NextUp ERP;
	- Non-standard OpenCart product types are NOT handled;
	- There is no support for batch tracked NextUp ERP products.

2. Customer synchronization:
	- If you have a third party extension that collects additional customer or customer address information, that information won't be picked up and exported to NextUp ERP;
	- Customers are not imported from NextUp ERP.

3. Order synchronization:
	- Series for documents issued using this extension cannot be configured;
	- Payments are not exported to NextUp ERP;
	- No guarantees are made and no adjustments are carried out with respect to NextUp ERP's own price rounding mechanism.

4. Order document removal:
	- NextUP ERP documents that have not been issued using this extension cannot be removed;
	- Documents cannot be cancelled using this extension, only removed.