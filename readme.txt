=== Ultimate Member - Optimize ===

Author: umdevelopera
Author URI: https://github.com/umdevelopera
Plugin URI: https://github.com/umdevelopera/um-optimize
Tags: ultimate member, optimize, assets, images
Requires at least: 6.5
Tested up to: 6.7
Requires UM core at least: 2.6.8
Tested UM core up to: 2.9.1
Stable tag: 1.2.1
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

== Description ==

Optimize loading for sites with the Ultimate Member plugin.

= Key Features =

* Removes CSS and JS files that belongs to Ultimate Member and its extensions on pages that do not have Ultimate Member components.
* Combines CSS and JS files that belongs to Ultimate Member and its extensions on pages with Ultimate Member components.
* Allows using Profile Photo and Cover Photo images from the browser cache.
* Optimizes SQL queries to get posts and users faster.

= Documentation & Support =

This is a free extension created for the community. The Ultimate Member team does not provide support for this extension.
Open new issue in the GitHub repository if you are facing a problem or have a suggestion: https://github.com/umdevelopera/um-optimize/issues
Documentation is the README section in the GitHub repository: https://github.com/umdevelopera/um-optimize

== Installation ==

Download ZIP file from GitHub or Google Drive. You can find download links here: https://github.com/umdevelopera/um-optimize

You can install this plugin from the ZIP file as any other plugin. Follow this instruction: https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin

== Changelog ==

= 1.2.1: November 17, 2024 =

 - Fixed: "Load textdomain just in time" issue

= 1.2.0: October 19, 2024 =

* Enhancements:

 - Added: The "Images" section with settings "Profile Photo caching", "Cover Photo caching", "Cover Photo size in directory".
 - Tweak: Settings structure.
 - Tweak: Documentation.

= 1.1.3: October 4, 2024 =

 - Added: Hook um_optimize_not_dequeue used to filter assets that should not be dequeued.
 - Fixed: Issue #5 - Notification Bell.

= 1.1.2: June 13, 2024 =

 - Fixed: Issue #2 - Error when "Combine CSS" is active.
 - Fixed: Issue #3 - Folder um_optimize missing.

= 1.1.1: December 17, 2023 =

 - Fixed: Add non-UM dependencies to combined files.
 - Fixed: Load combined footer scripts in footer.
 - Fixed: Sanitize table alias in SQL requests.
 - Tweak: The "Speed up member directories" is available in any mode.

= 1.1.0: December 11, 2023 =

 - Added: Features that optimize SQL queries to get posts and users faster.
 - Tweak: Detecting Ultimate Member pages and Ultimate Member elements on the page.
 - Tweak: Combined files minification.

= 1.0.0: November 19, 2023 =

* Initial release.