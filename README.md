# WooCommerce Blocks Incompatibility Check

While the initial cart and checkout pages of WooCommerce are based on PHP, the new cart and checkout pages of WooCommerce Blocks are based on React. As a result of that, not every extension, that is compatible to WooCommerce, is also compatible to WooCommerce Blocks.

This plugin in a proof of concept to check if an extension is incompatible to WooCommerce Blocks.

## Core

The core of this proof of concept plugin takes place in https://github.com/nielslange/woocommerce-blocks-incompatibility-check/blob/726f58cc3c54c315d90e8a6fc0608568d97c7532/woocommerce-blocks-incompatibility-check.php#L192-L202

Currently, I'm only searching within the selected plugin folder, e.g. `wp-content/plugin/woocommerce-blocks`, for the string `registerPaymentMethod`.  

## Screenshots

### Screen when selecting an extension

![incompatibility-check-select-extension](https://user-images.githubusercontent.com/3323310/194843134-1b2cfc45-326d-4a52-a980-4e2eddedeedc.png)</td>

### Screen when the extension is compatible

![incompatibility-check-compatible-extension](https://user-images.githubusercontent.com/3323310/194843112-29875cdb-aa22-41b2-bcff-e79c228f320e.png)

### Screen when the extension is incompatible

![incompatibility-check-incompatible-extension](https://user-images.githubusercontent.com/3323310/194843127-90339f6a-6c62-43ea-8553-608facc7886f.png)

