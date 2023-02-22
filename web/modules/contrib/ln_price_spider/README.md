## Price Spider connector
This module creates a new field type that allows you to setup price spider where to buy button in any type of entity. It's llows us to guide shoppers to your products and follow them along every step of the purchasing funnel. 
It’s one of the most sophisticated and effective where-to-buy solutions anywhere.


Usage:
======
- Showing Product where to buy options with integration of Price spider.
- Option to buy from different merchants.

Configuration:
=============

Installation of this module is just like any other Drupal module.

1) Go to drupal extend and enable the module.
2) Once enabled the module go to "Lightnest Price Spider" content type manage field.
3) Go to content type and create field field_price_spider with type of price spider.
4) Configure the field and save the field settings.
5) Go to node/add/product and give price spider id to see the widget.
6) Save node form and Clear the cache.

####Migration of Price Spider ID's
Price Spider migration means user can import multiple or bulk price spider id's in to the product type. Follow these steps
to import bulk Price Spider migration using Lightnest Price spider.
- Go to this URL "/admin/config/lightnest/ln-price-spider/field-update"
- Choose price spider field from select list and associated content type to import id's.
- Upload csv file and sample csv file is uploaded under lightnest Price spider module under migrations/example/price_spider_by_nid.csv
- After uploading file and select field type submit it.
- All nid given in csv file will update the Price spider id's.


## Additional steps to fully implement

#### Installation and use

Install the module as usual. The module requires no extra configuration.

After the installation you'll have a new field type called "PriceSpider Button". You can add it directly to your Content Type or to another fieldable entity you're using in your site.

The next time you create any content using the entity where you added the PriceSpider Button, you'll see a textfield where you can add the PriceSpider Lightbox ID.


##### Override template

The module comes with a simple twig template called "price-spider-button.html.twig"
you can copy this template in your theme and customize it.
