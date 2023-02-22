This container has been provisioned with the latest secured Drupal version and some relevant dependencies including **LightNest**.


# LightNest

LightNest is a starter kit for Drupal based projects, aimed for agencies and to help businesses and brands providing standard features across the organization, which will lead into a standardized support and maintenance.

All Nestlé brand websites **must be built on the top of LightNest**, the usage of its components and integrations will depend on the nature of the project itself.

Register to [Vitrine](https://lightnest.nestle.com/our-products/lightnest/what-is-lightnest) or check the profile README (/profiles/contrib/lightnest/README.md) to get more details.


## Using LightNest

Technically speaking, LightNest is delivered as a Drupal profile including:
- Custom modules for frontend components.
- Custom modules for integrations with third-party or other Nestlé solutions.
- Custom modules and default configurations to align the site with Nestlé standards (security, compliance, ...)
- Contrib modules that may add value to the site.
- Custom themes compatible with the beforementioned frontend components.

The dependeny to LightNest has been already added to the main composer.json but to get the latest updates from the package manager, it would require authentication. 


## External repository

LightNest relies on a composer based package manager to expose all the modules available.

Every time a new LightNest custom module is released, it's added to this package manager and made available for all Nestlé brand websites that are including it as external repository in their main composer file. 

To authenticate with this package manager, simply run the following composer commands: 

`composer config repositories.lightnest composer https://satis.lightnest.nestle.com/` (not needed if the repository is already added to the main composer.json)
`composer config --global http-basic.satis.lightnest.nestle.com <user> <password>`

Current credentials and further instructions can be found in [Vitrine](https://lightnest.nestle.com/tech-dev/tech-guidelines/composer-usage).


## Installation

As a Drupal profile, it can be installed either manually or using drush. 

By default, the installer enables a bunch of required modules and when installed manually, LightNest is also ading a wizard that allows the user to enable/disable some of the other optional LightNest features.


## Contributing to LightNest

LightNest is aimed to be open within Nestlé community meaning that contributions are more than welcome. Reporting bugs, raising PR, contributing with modules (white-labeled enough) that could be useful for other projects will help improving LightNest and at the same time help your agency to have a better positioning in our panel of suppliers.


## Maintenance

LightNest is releasing on a bimonthly basis but hotfixes might be released either at profile or module/theme level depending on criticallity.

In order to be compliant and meeting Nestlé security standards, all sites must be updated to the latest:
- Drupal core secured version
- LightNest version
- Contrib modules/themes compatible version
- PHP and DB supported version.

Find all the releases in [Vitrine](https://lightnest.nestle.com/our-products/lightnest/release-notes) and the changelog (/profiles/contrib/lightnest/CHANGELOG.md).

Be informed of the latest Drupal security advisories in [Drupal.org](https://www.drupal.org/security)


## Support

For LightNest retlated topics:
- Attend any of the [Q&A open sessions](https://lightnest.nestle.com/tech-dev/webcms-tech-open-sessions) held by WebCMS team.
- With the help of your business stakeholder, raise a [knowledge request ticket](https://nestle.service-now.com/sp?id=sc_cat_item&sys_id=91315746db603b04668224b3ca9619ff).

For platform related inquiries please address them directly to the correspondent platform support team.


# Getting started with the platform

Get started with Platform.sh [here](https://docs.platform.sh/get-started.html).


## Drupal 9 for Platform.sh

This template builds Drupal 9 using the "Drupal Recommended" Composer project.  It is pre-configured to use MariaDB and Redis for caching.  The Drupal installer will skip asking for database credentials as they are already provided.

Drupal is a flexible and extensible PHP-based CMS framework.

## Features

* PHP 8.1
* MariaDB 10.4
* Redis 5
* Drush included
* Automatic TLS certificates
* Composer-based build

## Post-install

Run through the Drupal installer as normal.  You will not be asked for database credentials as those are already provided.

## Customizations

The following changes have been made relative to Drupal 9 "Recommended" project as it is downloaded from Drupal.org or Packagist.  If using this project as a reference for your own existing project, replicate the changes below to your project.

* The `.platform.app.yaml`, `.platform/services.yaml`, and `.platform/routes.yaml` files have been added.  These provide Platform.sh-specific configuration and are present in all projects on Platform.sh.  You may customize them as you see fit.
* An additional Composer library, [`platformsh/config-reader`](https://github.com/platformsh/config-reader-php), has been added.  It provides convenience wrappers for accessing the Platform.sh environment variables.
* Drush and Drupal Console have been pre-included in `composer.json`.  You are free to remove one or both if you do not wish to use them.  (Note that the default cron and deploy hooks make use of Drush commands, however.)
* The Drupal Redis module comes pre-installed.  The placeholder module is not pre-installed, but it is enabled via `settings.platformsh.php` out of the box.
* The `settings.platformsh.php` file contains Platform.sh-specific code to map environment variables into Drupal configuration. You can add to it as needed. See the documentation for more examples of common snippets to include here.  It uses the Config Reader library.
* The `settings.php` file has been heavily customized to only define those values needed for both Platform.sh and local development.  It calls out to `settings.platformsh.php` if available.  You can add additional values as documented in `default.settings.php` as desired.  It is also setup such that when you install Drupal on Platform.sh the installer will not ask for database credentials as they will already be defined.

## References

* [Drupal](https://www.drupal.org/)
* [Drupal on Platform.sh](https://docs.platform.sh/frameworks/drupal8.html)
* [PHP on Platform.sh](https://docs.platform.sh/languages/php.html)



WebCMS team