# Configure Extension

To configure the extension, navigate to `NextUp ERP - Configure`. There are three sections:
- NextUp ERP Connection Options - specify the NextUp ERP server address and credentials;
- NextUp ERP Integration Options - specify integration options;
- NextUp ERP OpenCart Workflow Options - specify workflow options.
<div class="mp-page-break"></div>

## NextUp ERP Connection Options 

In this section, you specify the NextUp ERP server address and credentials. The following fields are available:

| Field           | Type    | Description |
| --------------- | ------- | --------- |
| Web service URL | URL     | The URL for which NextUp ERP server is configured to listen (MUST include the `/POST` segment as well) |
| Username        | String  | The username used for authentication with the NextUp ERP server |
| Password        | String  | The password used for authentication with the NextUp ERP server |
| Society code    | String  | The society code to which the store will be bound to operate |
| Timeout         | Number  | The maximum duration, in seconds, for which any call to NextUp ERP server will be allowed to run |

### Screenshots

<div class="mp-page-screenshot" markdown="1">
![Configure Connection]($img_base_url$/configure-connection.png "Configure Connection")
</div>

<div class="mp-page-break"></div>

## NextUp ERP Integration Options

In this section, you specify the main integration options, such as what warehouse to use. The following fields are available:

| Field                 | Type    | Description |
| --------------------- | ------- | ----------- |
| Warehouse | Option | The warehouse to which this store will be bound to operate |
| Issue document type | Option  | What document to issue for each OpenCart order |
| Automatically issue document for these order statuses | Multiple Option | When an OpenCart order transitions into one of the selected statuses, a document will be automatically issued in NextUp ERP |
| Automatically remove document for these order statuses | Multiple Option | When an OpenCart order transitions into one of the selected statuses, the document will be automatically removed from NextUp ERP, if previously issued |
| Issue document with status | Option | When a document is issued in NextUp ERP from the store, use this status |
| Document due days | Number | When a document is issued in NextUp ERP from the store, use this number of days for calculating the due date, starting from the moment the document is issued |
| Shipping VAT Quota | Option | Use this VAT quota for ALL shipping methods in your store. When saved, the extension will attempt to reconfigure all your shipping methods to use this VAT quota. This is necessary to match NextUp ERP vat quotas when adding shipping to documents. |

<div class="mp-page-break"></div>

### Screenshots

<div class="mp-page-screenshot" markdown="1">
![Configure Integration]($img_base_url$/configure-integration.png "Configure Integration")
</div>
<div class="mp-page-break"></div>

## NextUp ERP OpenCart Workflow Options

In this section, you specify additional workflow integration options, mostly OpenCart-specific. The following fields are available:

| Field           | Type    | Description |
| --------------- | ------- | ----------- |
| VAT applicable on payment | Yes/No  | Whether or not to send the "is vat applicable on payment" flag to NextUp ERP when issuing a document. If not sent, then NextUp will use its default settings. |
| Add shipping to document | Yes/No  | Whether or not to add shipping cost to the document issued to NextUp ERP. |
| Stock status for in-stock products | Option  | When the stock quantities are updated from NextUp ERP and some products are found to be in-stock, use this status when updating the product stock status. |
| Stock status for out-of-stock products | Option  | When the stock quantities are updated from NextUp ERP and some products are found to be out-of-stock, use this status when updating the product stock status. |
| Client group used for individuals | Option | OpenCart client group used for customers that are billed as individuals. If there is not per-billing type segregation, then set this to `None`. You need to creat this customer group yourself. |
| Client group used for legal persons | Option | OpenCart client group used for customers that are billed as legal persons/companies. If there is not per-billing type segregation, then set this to `None`. You need to creat this customer group yourself. |
| Custom field used for VAT Code entry | Option | OpenCart custom field used for VAT code entry. The extension will use this when looking up the customer's VAT code. If you don't have any need for this, then set it to `None`. If You need to creat this field yourself. |
| Custom field used for reg. com. number entry | Option | OpenCart custom field used for reg. com. number entry. The extension will use this when looking up the customer's reg. com. number. If you don't have any need for this, then set it to `None`. If You need to creat this field yourself. |
| Custom field used for bank account (IBAN) entry | Option | OpenCart custom field used for bank account number entry. The extension will use this when looking up the customer's bank account number. If you don't have any need for this, then set it to `None`. If You need to creat this field yourself. |
| Custom field used for bank name entry | Option | OpenCart custom field used for bank name entry. The extension will use this when looking up the customer's bank name. If you don't have any need for this, then set it to `None`. If You need to creat this field yourself. |
| Customer group used for NextUp-imported new tax rates | Option | When importing tax rates from NextUp, assign them to this customer group by default. Mandatory. |
| Geozone used for NextUp-imported new tax rates | Option | When importing tax rates from NextUp, assign them to this geozone by default. Mandatory. |
| Weight class used for NextUp-imported new products | Option | When importing product from NextUp, assign them to this weight class by default. Mandatory. |
| Length class used for NextUp-imported new products | Option | When importing product from NextUp, assign them to this length class by default. Mandatory. |

Below is a screenshot represeting the relevant section of the configuration page:

<div class="mp-page-screenshot" markdown="1">
![Configure Workflow]($img_base_url$/configure-workflow.png "Configure Workflow")
</div>
<div class="mp-page-break"></div>