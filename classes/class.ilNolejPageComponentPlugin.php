<?php

/**
 * This file is part of Nolej Page Component Plugin for ILIAS,
 * developed by OC Open Consulting to integrate ILIAS with Nolej
 * software by Neuronys.
 *
 * @author Vincenzo Padula <vincenzo@oc-group.eu>
 * @copyright 2024 OC Open Consulting SB Srl
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * NolejPageComponent Page Component Plugin.
 */
class ilNolejPageComponentPlugin extends ilPageComponentPlugin
{
    /**
     * Get plugin name.
     * @return string
     */
    public function getPluginName(): string
    {
        return "NolejPageComponent";
    }

    /**
     * Check if the current parent type ("auth", "cat", "crs", etc.) is
     * valid for this page component.
     *
     * @param string $a_parent_type parent type
     * @return bool
     */
    public function isValidParentType(string $a_parent_type): bool
    {
        // $a_parent_type can be auth, cat, crs, ...
        return true;
    }

    /**
     * This function is called when the page content is cloned.
     * @param array $a_properties properties saved in the page, (should be modified if neccessary)
     * @param string $a_plugin_version plugin version of the properties
     */
    public function onClone(array &$a_properties, string $a_plugin_version): void
    {
        // Nothing to clone.
        parent::onClone($a_properties, $a_plugin_version);
    }

    /**
     * This function is called before the page content is deleted.
     * @param array $a_properties properties saved in the page (will be deleted afterwards)
     * @param string $a_plugin_version plugin version of the properties
     * @param bool $move_operation
     * @return void
     */
    public function onDelete(array $a_properties, string $a_plugin_version, bool $move_operation = false): void
    {
        // Nothing to delete.
        parent::onDelete($a_properties, $a_plugin_version, $move_operation);
    }
}
