# WP-CLI Reduce DB Command

Optimize your WordPress database size efficiently using WP-CLI.

## Overview

This command-line tool allows you to significantly reduce the size of your WordPress database by:

- **Removing Non-Essential Data**:
  - Deletes revisions
  - Clears transients
  - Removes orphaned entries
- **Content Optimization**:
  - Retains only the 500 most recent contents for each content type
  - Retains only the 500 most recent comments

## Plugin Data Removal

The following plugin data will be purged during optimization:

- **Action Scheduler**
- **Broken Link Checker**
- **Cavalcade**
- **Contact Form 7**
- **FacetWP**
- **FormidableForms**
- **GDPR Cookie Consent**
- **GravityForms**
- **Log HTTP Requests**
- **Matomo**
- **PublishPress Future (post-expirator)**
- **Rank Math**
- **Redirection**
- **SearchWP 3.x & 4.x**
- **Stream**
- **TA Links**
- **ThirstyAffiliates**
- **WP All Export**
- **WP Cerber**
- **WP Forms**
- **WP Mail Log**
- **WP Mail Logging**
- **WordPress Native PHP Sessions**
- **WP Rocket**
- **WP Security Audit Log**
- **WooCommerce**
- **Yoast SEO**
- **Yop Polls**

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

To install the package, run:
```
wp package install BeAPI/wp-cli-reduce-db-command
```
To update the package, use:
```
wp package update
```

## Usage

Start database cleanup
```
wp reduce-db
```
## Credits

Based on https://github.com/BeAPI/wp-cli-light-db-export for the table/plugin list
