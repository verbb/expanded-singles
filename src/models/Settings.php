<?php
namespace verbb\expandedsingles\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * Expands the Singles link on the Entries page to list them like Channels
     * and Structures.
     */
    public bool $expandSingles = true;

    /**
     * Automatically redirects to edit the Single Entry when clicking on the
     * item in the sidebar.
     */
    public bool $redirectToEntry = false;

}
