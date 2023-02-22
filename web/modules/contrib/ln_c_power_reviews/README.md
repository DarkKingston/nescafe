## Power Reviews Connector

This module creates a new field type that allows you to setup power reviews field in any type of entity. It's allows us
to show power reviews on any of content type detail page and also it works as a component to display in the component
page.


Usage:
======

- Showing power reviews for any of the entity type with write a review options.
- Options to show display variations for reviews.

Configuration:
=============

Installation of this module is just like any other Drupal module.

1) Go to drupal extend and enable the module.
2) Save all require values and configs "/admin/config/lightnest/ln-c-power-reviews/ln-c-power-reviews-settings".
3) Once enabled the module go to "Lightnest Power reviews" content type manage field.
4) Go to content type and create field_ln_c_power_reviews with type of power reviews.
5) Configure the field and save the field settings.
6) Go to node/add/product and give price spider id to see the widget.
7) Save node form and Clear the cache.

Using as a paragraph in the component page.

1) Go to drupal paragraph list and check lightnest power reviews component.
2) Configure any of the content type and by default comes in the component content type.
3) Go to node/add/component and add power reviews component.
4) Add all variations based on need and display the reviews.

Template Override:
==================

- In power reviews module two template files
    - paragraph template (paragraph--ln-c-power-reviews)
    - field_type template (ln-c-power-reviews-type)
- Copy and paste in the custom theme or site a specific theme to override the order of reviews and additional markup or
  classes.

## Additional steps to fully implement

#### Installation and use

Install the module as usual. The module requires no extra configuration.

After the installation you'll have a new field type called "Power Reviews". You can add it directly to your Content Type
or to another fieldable entity you're using in your site.

The next time you create any content using the entity where you added the Power Reviews, you'll see a textfield where
you can add the Power Reviews page id.

##### Override template

The module comes with a simple twig template called "paragraph--ln-c-power-review"
you can copy this template in your theme and customize it.
