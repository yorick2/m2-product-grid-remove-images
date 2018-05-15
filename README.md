# magento 2 - Add the ability to mass delete all the  images from product's in the product grid.
# work in progress- dosnt work yet 

## Installation
- composer config repositories.paulmillband-add-products-to-categories-in-admin vcs git@bitbucket.org:yorick2/magento-2-product-grid-remove-images.git
- composer require paulmillband/m2-product-grid-remove-images:dev-master
- composer update 
- php bin/magento module:enable Paulmillband_ProductGridRemoveImages
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile

## Instructions
- login to magento admin
- go to catalog > products 
- select your products 
- select 'update attributes' from the drop down
- select 'Images'

![](screenshot1.png)
