# Pronamic Post Expiration

Easily manage and automate post expiration in WordPress.

## WordPress post statuses

| Post status         | Label      | Link |
| ------------------- | ---------- | ---- |
| `publish`           | Published  |  https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L640-L652 |
| `future`            | Scheduled  | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L654-L666 |
| `draft`             | Draft      | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L668-L681 |
| `pending`           | Pending    | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L683-L696 |
| `private`           | Private    | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L698-L710 |
| `trash`             | Trash      | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L712-L725 |
| `auto-draft`        | auto-draft | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L727-L735 |
| `inherit`           | inherit    | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L737-L745
| `request-pending`   | Pending    | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L747-L760 |
| `request-confirmed` | Confirmed  | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L762-L775 |
| `request-failed`    | Failed     | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L777-L790 |
| `request-completed` | Completed  | https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L792-L805 |

## New post status `expired`

This plugin registers the post status `expired`.

## Links

- https://schema.org/expires
- https://wordpress.org/plugins/auto-post-expiration/
