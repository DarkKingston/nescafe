## Fusepump connector
This module creates a new field type that allows you to setup Fusepump where to buy button in any type of entity. It's llows us to guide shoppers to your products and follow them along every step of the purchasing funnel. 
It’s one of the most sophisticated and effective where-to-buy solutions anywhere.


Usage:
======
- Showing Product where to buy options with integration of Fusepump.
- Option to buy from different merchants.

Configuration:
=============

Installation of this module is just like any other Drupal module.

1) Go to drupal extend and enable the module.
2) Once enabled the module go to "Lightnest Fusepump" content type manage field.
3) Go to content type and create field field_fusemump with type of fusemump.
4) Configure the field and save the field settings.
5) Go to node/add/product and give Fusepump id to see the widget.
6) Save node form and Clear the cache.

####Migration of WunderMan ID's
Fusepump migration means user can import multiple or bulk wunderman id's in to the product type. Follow these steps
to import bulk WunderMan migration using Lightnest WunderMan.
- Go to this URL "/admin/config/lightnest/ln-fusepump/field-update"
- Choose Fusepump field from select list and associated content type to import id's.
- Upload csv file and sample csv file is uploaded under lightnest Fusepump module under migrations/example/wunderman_id_by_nid.csv
- After uploading file and select field type submit it.
- All nid given in csv file will update the WunderMan id's.


## Additional steps to fully implement

#### Installation and use

Install the module as usual. The module requires no extra configuration.

After the installation you'll have a new field type called "Fusepump Button". You can add it directly to your Content Type or to another fieldable entity you're using in your site.

The next time you create any content using the entity where you added the Fusepump Button, you'll see a textfield where you can add the Fusepump ID.


##### Override template

The module comes with a simple twig template called "fusepumpbutton.html.twig"
you can copy this template in your theme and customize it.
