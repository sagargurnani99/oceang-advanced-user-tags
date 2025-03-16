# Advanced User Taxonomies

A plugin that allows custom tags to be assigned to users.

## Description

Advanced User Taxonomies is a powerful plugin that adds the ability to categorize WordPress users using custom taxonomies. This plugin creates a "User Tags" taxonomy that can be assigned to users, similar to how tags work for posts.

### Features

- Register a custom taxonomy called "User Tags" for WordPress users
- Add, edit, and delete User Tags from a dedicated admin page
- Assign User Tags to users when adding or editing a user
- Filter users by User Tags in the admin panel
- AJAX-powered dynamic search for User Tags using Select2
- Clean, professional code following WordPress coding standards and best practices

## Installation

1. Upload the `advanced-user-taxonomies` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Users > User Tags to manage your user tags
4. Assign tags to users by editing their profiles

## Usage

### Managing User Tags

1. Go to Users > User Tags in the WordPress admin menu
2. Add, edit, or delete User Tags just like you would with post tags

### Assigning Tags to Users

1. Go to Users > All Users
2. Edit a user profile
3. Scroll down to the "User Tags" section
4. Select tags from the dropdown or search for specific tags
5. Save the user profile

### Filtering Users by Tags

1. Go to Users > All Users
2. Use the "All User Tags" dropdown filter to select a tag
3. The user list will be filtered to show only users with the selected tag

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Developer Notes

This plugin follows WordPress coding standards and best practices:

- Object-oriented architecture with proper class structure
- Singleton pattern for main plugin class
- Proper security measures (nonces, capability checks, data sanitization)
- Internationalization ready
- Clean separation of concerns (admin, taxonomy, AJAX)
- Efficient database queries using WordPress core functions
- Responsive UI with modern JavaScript libraries (Select2)

## License

GPL v2 or later
