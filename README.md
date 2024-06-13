# Ultimate Member - Optimize

Optimize loading for sites with the Ultimate Member plugin.

## Key Features

- Removes CSS and JS files that belongs to Ultimate Member and its extensions from pages where there are no Ultimate Member elements.
- Combines CSS and JS files that belongs to Ultimate Member and its extensions on pages with Ultimate Member elements.
- Optimizes SQL queries to get posts and users faster.

## Installation

__Note:__ This plugin requires the [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) plugin to be installed first.

### How to install from GitHub

Open git bash, navigate to the **plugins** folder and execute this command:

`git clone --branch=main git@github.com:umdevelopera/um-optimize.git um-optimize`

Once the plugin is cloned, enter your site admin dashboard and go to _wp-admin > Plugins > Installed Plugins_. Find the "Ultimate Member - Optimize" plugin and click the "Activate" link.

### How to install from ZIP archive

You can install this plugin from the [ZIP file](https://drive.google.com/file/d/15YDvFMcfVFAixVLI4n3jbwFQZrgz6Rfu/view) as any other plugin. Follow [this instruction](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin).

## How to use

Go to *wp-admin > Ultimate Member > Settings > General > Optimize* to manage settings:

- **Dequeue unused CSS files** - Dequeue CSS files queued by the Ultimate Member plugin from pages where there are no Ultimate Member elements.
- **Dequeue unused JS files** - Dequeue JS files queued by the Ultimate Member plugin from pages where there are no Ultimate Member elements.
- **Combine CSS files** - Combine CSS files queued by the Ultimate Member plugin and its extensions.
- **Combine JS files** - Combine JS files queued by the Ultimate Member plugin and its extensions.

- **Speed up Activity** - *(optional)* Optimize the SQL query that retrieves posts for the [Social Activity](https://ultimatemember.com/extensions/social-activity/) extension.
- **Speed up Groups** - *(optional)* Optimize the SQL query that retrieves posts for the [Groups](https://ultimatemember.com/extensions/groups/) extension.
- **Speed up Notes** - *(optional)* Optimize the SQL query that retrieves notes for the [User Notes](https://ultimatemember.com/extensions/user-notes/) extension.
- **Speed up Photos** - *(optional)* Optimize the SQL query that retrieves albums for the [User Photos](https://ultimatemember.com/extensions/user-photos/) extension.
- **Speed up member directories** - *(optional)* Optimize the SQL query that retrieves users for the member directory.

Image - Optimize settings.
![UM Settings, General, Optimize](https://github.com/umdevelopera/um-optimize/assets/113178913/22783720-c9af-4562-8ca7-7fff43123d99)

## Support

This is a free extension created for the community. The Ultimate Member team does not provide support for this extension. Open new [issue](https://github.com/umdevelopera/um-optimize/issues) if you are facing a problem or have a suggestion.

### Related links

Ultimate Member home page: https://ultimatemember.com

Ultimate Member documentation: https://docs.ultimatemember.com

Ultimate Member download: https://wordpress.org/plugins/ultimate-member

Article: [How to remove CSS and JS on non UM pages](https://docs.ultimatemember.com/article/1490-how-to-remove-css-and-js-on-non-um-pages)
