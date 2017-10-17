<?php
namespace verbb\expandedsingles\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Expands the Singles link on the Entries page to list them like Channels
     * and Structures.
     *
     * @var boolean
     */
    public $expandSingles = true;

    /**
     * Automatically redirects to edit the Single Entry when clicking on the
     * item in the sidebar.
     *
     * @var boolean
     */
    public $redirectToEntry = false;

}
