# Configuration

You can customise Expanded Singles’ settings using a PHP configuration file. This is optional: each setting has a default, so you only need to include the values you want to change.

To override a setting, create `expanded-singles.php` in your Craft project’s `/config` directory and return an array of setting names and values. For example, the following will open the entry directly:

```php
<?php

return [
    'redirectToEntry' => true,
];
```

All other settings keep their defaults. Add any further settings you want to change to the same array. The options below explain the available settings and their defaults. You can also manage these settings in the control panel under **Settings → Expanded Singles**.

## Configuration Options

::: reference
### `expandSingles`

**Type:** `bool` · **Default:** `true`

Expands the Singles link on the Entries' page to list them like Channels and Structures.
:::

::: reference
### `redirectToEntry`

**Type:** `bool` · **Default:** `false`

Automatically redirects to edit the Single Entry when clicking on the item in the sidebar.
:::
