Spam Karma is a powerful and flexible anti-spam system for blogs and other sites that accept comments.

# Details #

Spam Karma is a modular system for detecting and blocking comment spam.  It works through the use of various plugins that each test a comment for a particular aspect of "spamminess".  Each of these tests assigns points to the comment, and after all the tests are run they are totaled up.  if the overall points are negative, it's considered spam and blocked.

There are a lot of options for how you want the system to interpret the points and so forth.  You can set the relative strength of each test individually, making some tests more important than others.  There are [third-party plugins](ThirdPartyPlugins.md) available as well, which add functionality beyond that of Spam Karma alone.

It is currently only available as a plugin for [WordPress](http://wordpress.org/), but could probably be adapted to other platforms.

# Install #

Spam Karma for WordPress installs the same as any standard WordPress plugin.  Unzip the zip file and put the resulting folder in your plugins directory.  Then go into WordPress Admin and activate it on the Plugins page.