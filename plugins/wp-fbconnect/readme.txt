=== Plugin Name ===
Requires at least: 2.5
Tested up to: 2.6

WP-FacebookConnect uses Facebook Connect to integrate Wordpress with
Facebook.  Provides single-signon, avatars, and newsfeed comment
publication.

=== Installation ===

 1. Copy the plugin to a directory in wp-content/plugins
 2. In the Wordpress Admin panel, visit the plugins page and Activate the plugin.
 3. Visit the settings page and select "Facebook Connect".  Follow the
 given instructions to configure the plugin and obtain a Facebook API key.

Note that this plugin was developed with the "Kubrick" default theme.
It will likely have display and/or functionality issues on different
themes.

A future version will include template tags that mitigate this
problem, but until then some source modifications may be required.
See fbc_logged_out_cb and setup_feedform in fbconnect.js for the most
likely candidates for modification.

I (ahupp at facebook.com) would appreciate any feedback on what can be
done to make integration into various themes easier.
