# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)

## [Unreleased]

## [0.0.4] - 2025-01-28
### Changed
- Fix issue with all contents removed instead of just keeping the 500 most recent.

## [0.0.3] - 2025-01-17
### Added
- Functionality to retain only the 500 most recent comments for each site.

### Changed
- Use _gmt versions of post_date and comment_date to avoid timezone issues.

## [0.0.2] - 2024-12-16
### Added
- Initial release of the WP-CLI Reduce DB Command.
- Functionality to remove non-essential data: revisions, transients, and orphaned entries.
- Feature to retain only the 500 most recent entries for each content type.
- Support for data removal from multiple plugins.
