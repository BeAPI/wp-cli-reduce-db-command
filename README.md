# wp-cli-reduce-db-command

Reduce DB size for WP-CLI

This command allows you to significantly reduce the size of the WordPress database by removing non-essential data, revisions, transients, orphaned data, and keeping only the 500 most recent contents for each content type.

Data from the following plugins will be deleted:
* Action Scheduler
* Broken Link Checker
* Cavalcade
* Contact Form 7
* FacetWP
* FormidableForms
* GDPR Cookie Consent
* GravityForms
* Log HTTP requests
* Matomo
* Redirection
* SearchWP 3.x & 4.x
* Stream
* TA Links
* ThirstyAffiliates
* WP All Export
* WP Cerber
* WP Forms
* WP Mail Log
* WP Mail Logging
* WP Rocket
* WP Security Audit Log
* WooCommerce
* Yoast SEO
* Yop Polls

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install BeAPI/wp-cli-reduce-db-command`
You also can update your package with `wp package update`

## Usage

Start database cleanup

`wp reduce-db`

## Credits

Based on https://github.com/BeAPI/wp-cli-light-db-export for the table/plugin list
