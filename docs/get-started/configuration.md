# Configuration

There are two methods to managing plugin settings and configurations for Expanded Singles.

## Configuration File

Create an `expanded-singles.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

```php
<?php

return [
    'expandSingles' => true,
    'redirectToEntry' => false,
];
```

### Configuration options

- `expandSingles` - Expands the Singles link on the Entries page to list them like Channels and Structures.
- `redirectToEntry` - Automatically redirects to edit the Single Entry when clicking on the item in the sidebar.

## Control Panel

You can also make change and configuration settings through the Control Panel by visiting Settings → Expanded Singles.
