# Admin Toolbar Version

This module modifies the admin tool help menu item (the drupal icon)
by adding version information.
- Shows the drupal version
- Shows the "`application`" version, by extracting version info from
  either a module or profile info yml file (configurable).
- Shows the current git branch (configurable)
- Shows the environment based on either domain or environment variable
  (configurable)
- Alters the background color of the menu item based on the environment
  (configurable)

Works with GIN admin theme (see recommended modules)

For a full description of the module, visit the
[project page](https://www.drupal.org/project/admin_toolbar_version).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/admin_toolbar_version).


## Table of contents

- Requirements
- Recommended modules 
- Installation
- Configuration
- Maintainers


## Requirements

This module requires the following modules:

- [Admin Toolbar Tools](https://www.drupal.org/project/admin_toolbar)


## Recommended modules 

- [Gin theme](https://www.drupal.org/project/gin)


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

**General usage**

After installing the module, you will need to configure your environments.
Configuration is found under the standard "User Interface" configuration item.

`/admin/config/user-interface/admin-toolbar-version`


## Maintainers

- Kris Booghmans - [kriboogh](https://www.drupal.org/u/kriboogh)
- Mschudders - [Mschudders](https://www.drupal.org/u/mschudders)

This project has been sponsored by:
- [Calibrate](https://www.calibrate.be)
  In the past fifteen years, we have gained large expertise in
  consulting organizations in various industries.
