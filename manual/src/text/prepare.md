# Preparations

In order to properly integrate the two systems (your OpenCart store and NextUp ERP) you need to carry out the following prepatations.
Some of these are required regardless of this extension and, in fact, regardless of which other system you need to connect to.

For information on how to carry out NextUp ERP related, please refer to their user's manual.

## Connectivity

- The firewall on the computer NextUp ERP is installed needs to allow connections to the port that NextUp ERP server is configured to listen (receive connections) on;
- The firewall on the computer your store is installed and running needs to allow connections TO the computer AND the port NextUp ERP server is installed and listening (this is particularly necessary if your store is running on a managed server and the firewall is managed by your hosting provider and NextUp ERP server is listening on a non-standard port, i.e. other than 80 and 443);
- NextUp ERP server must be configured to receive connections without its custom encryption enabled.

## Products

- You need to set SKUs for all the products you need synchronized with NextUp ERP.

## Configuring NextUp ERP

- It is possible that not all your customers will provide a VAT code, so you need to disable VAT code uniqueness constraint in NextUp ERP;
- Additionally, you might also want to disable VAT code validity checks in NextUp ERP, as this will reject invalid VAT Codes that your user's might mistakenly enter;
- You need to define an implicit, auto-numbered series for the documents you will be issuing using this extension.
