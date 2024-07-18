<?php

/**
 * This file is part of Nolej Page Component Plugin for ILIAS,
 * developed by OC Open Consulting to integrate ILIAS with Nolej
 * software by Neuronys.
 *
 * @author Vincenzo Padula <vincenzo@oc-group.eu>
 * @copyright 2023 OC Open Consulting SB Srl
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * NolejPageComponent Page Component Plugin
 */
class ilNolejPageComponentPlugin extends ilPageComponentPlugin
{

    /**
     * Get plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return "NolejPageComponent";
    }

    /**
     * Check if parent type is valid
     * @see getParentType() of classes extending ilPageObject
     * @see NolejPageComponent::getReturnUrl()
     * @return bool
     */
    public function isValidParentType(string $a_parent_type): bool
    {
        // $a_parent_type can be auth, cat, crs, ...
        return true;
    }

    /**
     * This function is called when the page content is cloned
     * @param array 	$a_properties		properties saved in the page, (should be modified if neccessary)
     * @param string	$a_plugin_version	plugin version of the properties
     */
    public function onClone(array &$a_properties, string $a_plugin_version): void
    {
        // $settings_id = $a_properties["settings_id"];
        // if (!empty($settings_id))
        // {
        // 	$oldSettings = new ilExternalContentSettings($settings_id);
        // 	$newSettings = new ilExternalContentSettings();
        // 	$oldSettings->clone($newSettings);
        // 	$newSettings->setObjId($this->getParentId());
        // 	$newSettings->save();
        // 	$a_properties["settings_id"] = $newSettings->getSettingsId();
        // }
    }

    /**
     * This function is called before the page content is deleted
     * @param array $a_properties properties saved in the page (will be deleted afterwards)
     * @param string $a_plugin_version plugin version of the properties
     */
    public function onDelete(array $a_properties, string $a_plugin_version, bool $move_operation = false): void
    {
        // $settings_id = $a_properties["settings_id"];
        // if (!empty($settings_id))
        // {
        // 	$exco_settings = new ilExternalContentSettings($settings_id);
        // 	$exco_settings->delete();
        // }
    }
}
