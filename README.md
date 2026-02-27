# Ultimate Member - Optimize and Color

Improves the performance of sites with Ultimate Member. Customize Ultimate Member colors.

## Key Features

- Removes CSS and JS files that belongs to Ultimate Member and its extensions on pages that do not have Ultimate Member components.
- Combines CSS and JS files that belongs to Ultimate Member and its extensions.
- Allows using Profile Photo and Cover Photo images from the browser cache.
- Optimizes SQL queries to get posts and users faster.
- Adds settings to customize Ultimate Member colors.

## Installation

**Note:** This plugin requires the [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) plugin to be installed first.

### How to install from GitHub

Open git bash, navigate to the **plugins** folder and execute this command:

`git clone --branch=main git@github.com:umdevelopera/um-optimize.git um-optimize`

Once the plugin is cloned, enter your site admin dashboard and go to _wp-admin > Plugins > Installed Plugins_. Find the "Ultimate Member - Optimize and Color" plugin and click the "Activate" link.

### How to install from ZIP archive

You can install the plugin from this [ZIP file](https://drive.google.com/file/d/1T0T5MZ-qG9OGYtEL2GiKkvKHGUsWMn_z/view) as any other plugin. Follow [this instruction](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin).

## How to use

### How to customize Ultimate Member colors

Go to _wp-admin > Ultimate Member > Settings > Appearance > Colors_ and turn on the **Enable custom colors** setting then use settings below to customize Ultimate Member colors.

The **Colors** tab also contains tools that may be helpful:

- **Reset colors** - Restore the default color set, close to Ultimate Member's own colors. Current colors will be overridden.
- **Export colors** - Save current color set to the _json_ file.
- **Import colors** - Load color set from the _json_ file. Current colors will be overridden.

![UM Settings, Appearance, Color v1 3 0](https://github.com/user-attachments/assets/4333ac79-e02f-48b3-91b2-4be7bcf79e15)

#### Common

- **Active element**
- **Active element text**
- **Background**
- **Light line**
- **Line**
- **Light text**
- **Text**

#### Links and buttons

- **Link**
- **Link hover**
- **Primary button**
- **Primary button hover**
- **Primary button text**
- **Secondary button**
- **Secondary button hover**
- **Secondary button text**

#### Fields and filters

- **Active element**
- **Background**
- **Background for item**
- **Border**
- **Placeholder**
- **Text**
- **Label**

#### Profile menu

- **Active tab**
- **Background**
- **Hover**
- **Text**

### How to optimize Ultimate Member to improve performance

Go to _wp-admin > Ultimate Member > Settings > General > Optimize_ to manage settings.

![UM Settings, General, Optimize (v1 2 0)](https://github.com/user-attachments/assets/4eb8dae7-ae59-49c6-8f8a-b3f0111ef601)

#### CSS and JS

Ultimate Member loads various styles and scripts that are necessary for its components to work.
Extensions can also load their own styles and scripts. Loading many styles and scripts can slow down page rendering.
It is recommended to disable loading of Ultimate Member styles and scripts on pages that do not have its components.

Loading one large style or script file has less impact on page rendering delay than loading multiple files.
It is recommended to combine multiple Ultimate Member styles and scripts into one style file and one script file.

- **Dequeue unused styles** - Dequeue CSS files on pages that do not have Ultimate Member components.
- **Dequeue unused scripts** - Dequeue JS files on pages that do not have Ultimate Member components.
- **Combine styles** - Combine CSS files queued by the Ultimate Member plugin and its extensions.
- **Combine scripts** - Combine JS files queued by the Ultimate Member plugin and its extensions.

#### Images

Ultimate Member does not allow using Cover Photo and Profile Photo images from the browser cache.
This approach is safe and secure, but it slows down rendering the member directory and pages with Ultimate Member components.
It is recommended to allow using images from the browser cache.

Ultimate Member uses the largest Cover Photo thumbnail in the member directory on the desktop.
However, the directory does not need large images.
It is recommended to use Cover Photo thumbnail that is about 500 pixels wide to load images faster.

- **Profile Photo caching** - Allow using Profile Photo images from the browser cache.
- **Cover Photo caching** - Allow using Cover Photo images from the browser cache.
- **Cover Photo size in directory** - Select the size of the Cover Photo thumbnail for the member directory.

#### SQL queries

Ultimate Member uses the standard WP_Query and WP_User_Query classes to build database queries.
Queries created this way are reliable and stable, but are not optimized for speed.
This slows down retrieving users in the member directory and posts in extensions that use custom post type.
It is recommended to enable SQL queries optimization to get posts and members faster.

- **Speed up member directories** - *(optional)* Optimize the SQL query that retrieves users for the member directory.
- **Speed up Activity** - *(optional)* Optimize the SQL query that retrieves posts for the [Social Activity](https://ultimatemember.com/extensions/social-activity/) extension.
- **Speed up Groups** - *(optional)* Optimize the SQL query that retrieves posts for the [Groups](https://ultimatemember.com/extensions/groups/) extension.
- **Speed up Notes** - *(optional)* Optimize the SQL query that retrieves notes for the [User Notes](https://ultimatemember.com/extensions/user-notes/) extension.
- **Speed up Photos** - *(optional)* Optimize the SQL query that retrieves albums for the [User Photos](https://ultimatemember.com/extensions/user-photos/) extension.
- **Speed up Reviews** - *(optional)* Optimize the SQL query that retrieves reviews for the [User Reviews](https://ultimatemember.com/extensions/user-reviews/) extension.

## Support

This is a free extension created for the community. The Ultimate Member team does not provide support for this extension.
Open new [issue](https://github.com/umdevelopera/um-optimize/issues) if you are facing a problem or have a suggestion.

**Give a star if you think this extension is useful. Thanks.**

## Useful links

[Ultimate Member core plugin info and download](https://wordpress.org/plugins/ultimate-member)

[Documentation for Ultimate Member](https://docs.ultimatemember.com)

[Official extensions for Ultimate Member](https://ultimatemember.com/extensions/)

[Free extensions for Ultimate Member](https://docs.google.com/document/d/1wp5oLOyuh5OUtI9ogcPy8NL428rZ8PVTu_0R-BuKKp8/edit?usp=sharing)

[Code snippets for Ultimate Member](https://docs.google.com/document/d/1_bikh4JYlSjjQa0bX1HDGznpLtI0ur_Ma3XQfld2CKk/edit?usp=sharing)

