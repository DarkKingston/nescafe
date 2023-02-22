# About LightNest
![LightNest Logo](https://lightnest.nestle.com/sites/default/files/nestle-logo-png-transparent.png)\
LightNest is Nestlé's own custom Drupal distribution, it includes a curated list of frequently used contrib modules as well as custom ones that enable integration with corporate or validated 3rd party systems and define reusable components to build a website.

# Getting started:
> ***Warning***: The following instructions are for the manual installation of Lightnest.
For agencies intending to develop a new webstite, the standard process is to request a container in ServiceNow ([link ](https://lightnest.nestle.com/getting-started/demand-process/request-hosting)to Vitrine),
this will provide access to the site's repository with all the following steps already applied.

In order to develop LightNest locally, you need to follow these steps:
1. Create a new composer project
    ```bash
    composer create-project --no-install drupal/recommended-project lightnest
    ```
2. Move to the newly created directory
    ```bash
    cd lightnest
    ```
3. Ensure that "minimum-stability" in composer.json is set to "dev" (currently anything above will block installation). Don't worry, "prefer-stable" comes already set to "true".
    ```bash
    composer config minimum-stability dev
    ```
4. Make sure you enable patching in composer.json.
    ```bash
    composer config --json --merge extra.enable-patching 'true'
    ```
    The previous command will only work with Composer 2.x, otherwise manually set the "enable-patching" flag to true in the extra section like follows:
    ```json
    "extra": {
        "enable-patching": true,
            ...
    ```
5. Add the Lightnest repository.
    ```bash
    composer config repositories.lightnest composer https://satis.lightnest.nestle.com/
    ```
6. Add the Packagist repository.
    ```bash
    composer config repositories.asset-packagist composer https://asset-packagist.org
    ```
7. Add satis credentials (more details: https://lightnest.nestle.com/tech-dev/tech-guidelines/composer-usage)
    ```bash
    composer config --global http-basic.satis.lightnest.nestle.com <user> <password>
    ```
8. Clean composer's cache to ensure we get the latest versions.
    ```bash
    composer clear-cache
    ```
9. Finally, install Lightnest.
    ```bash
    composer require lightnest/lightnest:^4
    ```
    >for the legacy *3.x* version (based on Drupal 8) run this instead:
    `composer require --no-update acquia/lightning:^4`\
    `composer require lightnest/lightnest:^3`

# Setting up the site
The previous actions will provide the codebase but, as with any other Drupal website, you'll then need to complete the setup by running Drupal's installer before you can start using your site.
Run the installation wizard and select the Lightnest installation profile, this will add additional steps to choose what Lightnest's specific components and integrations you want to have available in this site.

> If unsure, you can always leave these unchecked and enable them later (like an ordinary Drupal module).

> ***Warning***: Some of those (specially integrations) may require further configuration before they can be used.
Please refer to each individual module's documentation and the one in Vitrine ([link](https://lightnest.nestle.com/our-products/lightnest)) for more information.

# Updating Lightnest
Both Drupal core as well as Lightnest are projects that are in continuous development, so it's always a good idea to update regularly in order to benefit from the new improvements and security fixes.

Should you wish to update Lightnest specifically, the process is the same as with any other composer package, as you simply need to run:
```bash
composer update lightnest/lightnest --with-dependencies
```
Afterwards, you should also make sure that any update scripts are run by executing:
```bash
./vendor/bin/drush updb
```

# References
For more comprehensive and up to date information of Lightnest capabilities and processes, please check out the main Lightnest documentation site: [Vitrine](https://lightnest.nestle.com/)

# Contributing
As Lightnest is used in over a thousand websites that are developed independently for the most part, there is a high likelyhood that enhancements or bugfixes done in one site could be used in others.
If you have a bugfix, enhancement or idea that you'd like to be added to Lightnest, please check out the [Contributions](https://lightnest.nestle.com/tech-dev/agency-collaboration-program/lightnest-open-source) and [Bug Reporting](https://dsu-confluence.nestle.biz/display/DWCMS/Bug+Reporting+Process) pages in Vitrine.

# Maintainers
Nestlé WebCMS Team
