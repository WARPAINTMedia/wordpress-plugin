Wordpress Plugin
================

A general Wordpress Plugin Boilerplate.

This plugin includes some nice helpers (image upload support) in order to make it easy to create **CRUD** plugins.

### About

This plugin has a few definitions:

* `PLUGIN_TITLE` = Title of the plugin
* `PLUGIN_TABLE` = Name of the table the plugin creates
* `PLUGIN_MENU` = Menu name in the admin
* `PLUGIN_OPTIONS` = Options route
* `PLUGIN_ROUTE` = Admin route
* `PLUGIN_AJAX` = Ajax route (default for ordering)
* `PLUGIN_CSS` = CSS file to enqueue

You can change and manipulate these constants. This make this boilerplate nice a *copy-pastable*.

### Sorting

By default this plugin support an AJAX sorting feature. You need to have an order or your table (there by default) and also have the sort AJAX setup properly. Out-of-the-box, this is all setup.

### Views

By default, there is a view for `index`, `form`, and `delete`. These have a nice view interface that lets us add in variables and use them nicely in these view templates.

### Options

There is an options route defined, but there is no handler, view, or table setup to manage options.
