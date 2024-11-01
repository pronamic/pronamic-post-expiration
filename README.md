# Pronamic Post Expiration

Easily manage and automate post expiration in WordPress.

## WordPress post statuses

| Post status         | Label      | Link |
| ------------------- | ---------- | ---- |
| `publish`           | Published  | [`wp-includes/post.php#L640-L652`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L640-L652) |
| `future`            | Scheduled  | [`wp-includes/post.php#L654-L666`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L654-L666) |
| `draft`             | Draft      | [`wp-includes/post.php#L668-L681`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L668-L681) |
| `pending`           | Pending    | [`wp-includes/post.php#L683-L696`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L683-L696) |
| `private`           | Private    | [`wp-includes/post.php#L698-L710`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L698-L710) |
| `trash`             | Trash      | [`wp-includes/post.php#L712-L725`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L712-L725) |
| `auto-draft`        | auto-draft | [`wp-includes/post.php#L727-L735`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L727-L735) |
| `inherit`           | inherit    | [`wp-includes/post.php#L737-L745`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L737-L745) |
| `request-pending`   | Pending    | [`wp-includes/post.php#L747-L760`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L747-L760) |
| `request-confirmed` | Confirmed  | [`wp-includes/post.php#L762-L775`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L762-L775) |
| `request-failed`    | Failed     | [`wp-includes/post.php#L777-L790`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L777-L790) |
| `request-completed` | Completed  | [`wp-includes/post.php#L792-L805`](https://github.com/WordPress/wordpress-develop/blob/6.6.2/src/wp-includes/post.php#L792-L805) |

## Post status `pronamic_expired`

This plugin registers the post status `pronamic_expired`.

## Post type support

```php
\register_post_type(
	'your_post_type',
	[
		// …
		'supports'           => [
			'title',
			'editor',
			'thumbnail',
			// …
			'pronamic_expiration',
		],
		// …
	]
);
```

### Advanced support options

```php
\register_post_type(
	'your_post_type',
	[
		// …
		'supports'           => [
			'title',
			'editor',
			'thumbnail',
			// …
			'pronamic_expiration' => [
				'post_status' => 'pronamic_expired',
				'show_ui'     => true,
				'source'      => 'your-plugin-slug',
			],
		],
		// …
	]
);
```

## Links

- https://schema.org/expires
- https://wordpress.org/plugins/auto-post-expiration/
