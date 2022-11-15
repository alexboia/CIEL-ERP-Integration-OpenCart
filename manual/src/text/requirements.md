# NextUp Integration Extension Requirements

## OpenCart Compatibility
Tested with versions 2.3.0.0 to 2.3.0.2.

## General requirements
- PHP 7.4.0 or newer;
- NextUp ERP with Server component activated (8.38.0 or newer suggested);
- Required PHP extensions:
	- Reflection
	- Curl
	- Json
	- MbString
	- Mysqli
	- Pcre
	- Spl
	- Zlib

## Required NextUp ERP Server methods
- AddPartner;
- AddSaleInvoice;
- AddSaleOrder;
- DeleteDocument;
- GetAllArticles;
- GetAllPartners;
- GetAllStocksForArticles;
- GetAllWarehouses;
- GetArticleByCode;
- GetArticleById;
- GetPartnerByCode;
- GetPartnerById;
- SelectFromView;
- UpdatePartner.

## NextUp Server Authentication Notes
If you are accessing the web services exposed by the NextUp ERP server you need to understand 
that, regardless of what application you are using to consume them (this extension included),
you need to use a separate NextUp ERP user account for each application.

This is not a strict technical requirement but, if you share a user account between applications,
you will end up with one authentication token overriding a previous one for that account, which, 
in turn, will lead to web service method calls for that all token failing.

## Other Notes
- Integration with OpenCart Romania's extension is provided as a best-effort implementation: 
there are no hard guarantees for correctness or that it will work at all.
- Compatibility with other OpenCart modules is not guaranteed, 
although this extension does not rely on any 3rd party module.