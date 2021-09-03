# Shippit - Magento 2 Extension

Simplify your shipping, display delivery options from multiple couriers and streamline your order fulfilment.

Shippit is an award winning fulfilment extension that plugs right into your shopping cart. Enabling different delivery options at checkout and an amazing tracking experience that lets you manage all your deliveries in one place. Our out-of-the-box solution includes Australia Post, Couriers Please and Fastway.

- Access multiple carriers and services easily with one account and bill - 3 hour deliver up and interstate line haul
- Never leave your store again with daily pickup on all deliveries
- Keep customers happy with FREE email and SMS notifications

You don't have time to waste on shipping stuff. Plug in to Shippit and forget all about negotiating rates or spending hours on the phone chasing couriers.

## Installation

### Composer Installation (Recommended)

*Note: Installation of the module via composer should be completed by a developer or someone comfortable with composer and the server commandline - we suggest testing this installation on a staging instance before pushing this live*

To install this module via composer, simply run the following commands from your server shell

```
composer require shippit/magento2
```

Once the package has been installed, proceed with the standard Magento module installation instructions, such as running the setup scripts, di compilation and static asset compilation

This will fetch and install the latest version of the Magento 2 module, as referenced in the master branch.

### Manual File Upload

*Note: Installation of the module via manual file upload should be completed by a developer or someone comfortable with working with the Magento Application Structure - we suggest testing this installation on a staging instance before pushing this live*

1. Download the extension from github (https://github.com/Go-Shippit/Magento2)
2. In the magento installation, create a folder path of `app/code/Shippit/Shipping`
3. Move the contents of the extension to `app/code/Shippit/Shipping`
4. On the server and in the Magento installation directory, run the command ``php bin/magento setup:upgrade``
5. If required, run the production di compilation and static asset compilation
