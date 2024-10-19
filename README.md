# Ultimate Member - Optimize

Optimize loading for sites with the Ultimate Member plugin.

## Key Features

- Removes CSS and JS files that belongs to Ultimate Member and its extensions on pages that do not have Ultimate Member components.
- Combines CSS and JS files that belongs to Ultimate Member and its extensions on pages with Ultimate Member components.
- Allows using Profile Photo and Cover Photo images from the browser cache.
- Optimizes SQL queries to get posts and users faster.

## Installation

__Note:__ This plugin requires the [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) plugin to be installed first.

### How to install from GitHub

Open git bash, navigate to the **plugins** folder and execute this command:

`git clone --branch=main git@github.com:umdevelopera/um-optimize.git um-optimize`

Once the plugin is cloned, enter your site admin dashboard and go to _wp-admin > Plugins > Installed Plugins_. Find the "Ultimate Member - Optimize" plugin and click the "Activate" link.

### How to install from ZIP archive

You can install this plugin from the [ZIP file](https://drive.google.com/file/d/1s4AI1BPF4eNSTbQSZPO4Tz2tS1NBmc4S/view) as any other plugin. Follow [this instruction](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin).

## How to use

Go to *wp-admin > Ultimate Member > Settings > General > Optimize* to manage settings.

![UM Settings, General, Optimize (v1 2 0)](https://github.com/user-attachments/assets/4eb8dae7-ae59-49c6-8f8a-b3f0111ef601)

### CSS and JS

Ultimate Member loads various styles and scripts that are necessary for its components to work. Extensions can also load their own styles and scripts. Loading many styles and scripts can slow down page rendering.
It is recommended to disable loading of Ultimate Member styles and scripts on pages that do not have its components.

Loading one large style or script file has less impact on page rendering delay than loading multiple files.
It is recommended to combine multiple Ultimate Member styles and scripts into one style file and one script file.

- **Dequeue unused styles** - Dequeue CSS files on pages that do not have Ultimate Member components.
- **Dequeue unused scripts** - Dequeue JS files on pages that do not have Ultimate Member components.
- **Combine styles** - Combine CSS files queued by the Ultimate Member plugin and its extensions.
- **Combine scripts** - Combine JS files queued by the Ultimate Member plugin and its extensions.

### Images

Ultimate Member does not allow using Cover Photo and Profile Photo images from the browser cache. This approach is safe and secure, but it slows down rendering pages with Ultimate Member components and loading the member directory.
It is recommended to allow using images from the browser cache if your site is public.

Ultimate Member uses the largest Cover Photo thumbnail in the member directory on the desktop. Such large images are usually not necessary.
It is recommended to use an image that is 500px wide or slightly larger.

- **Profile Photo caching** - Allow using Profile Photo images from the browser cache.
- **Cover Photo caching** - Allow using Cover Photo images from the browser cache.
- **Cover Photo size in directory** - Select the size of the Cover Photo thumbnail for the member directory.

### SQL queries

Ultimate Member uses the standard WP_Query and WP_User_Query classes to build database queries. Queries built this way are reliable and stable, but not optimized. This slows down retrieving users in the user directory and posts in extensions that use custom post type, which slows down page rendering.
It is recommended to enable SQL queries optimization to get posts and users faster.

- **Speed up member directories** - *(optional)* Optimize the SQL query that retrieves users for the member directory.
- **Speed up Activity** - *(optional)* Optimize the SQL query that retrieves posts for the [Social Activity](https://ultimatemember.com/extensions/social-activity/) extension.
- **Speed up Groups** - *(optional)* Optimize the SQL query that retrieves posts for the [Groups](https://ultimatemember.com/extensions/groups/) extension.
- **Speed up Notes** - *(optional)* Optimize the SQL query that retrieves notes for the [User Notes](https://ultimatemember.com/extensions/user-notes/) extension.
- **Speed up Photos** - *(optional)* Optimize the SQL query that retrieves albums for the [User Photos](https://ultimatemember.com/extensions/user-photos/) extension.

## Support

This is a free extension created for the community. The Ultimate Member team does not provide support for this extension. Open new [issue](https://github.com/umdevelopera/um-optimize/issues) if you are facing a problem or have a suggestion.

### Related links

Ultimate Member home page: https://ultimatemember.com

Ultimate Member documentation: https://docs.ultimatemember.com

Ultimate Member download: https://wordpress.org/plugins/ultimate-member
