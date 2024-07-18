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

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Nolej/classes/class.ilNolejPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Nolej/classes/class.ilObjNolejGUI.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Nolej/classes/class.ilNolejActivityManagementGUI.php");

/**
 * Page Component GUI
 *
 * @ilCtrl_isCalledBy ilNolejPageComponentPluginGUI: ilPCPluggedGUI
 * @ilCtrl_isCalledBy ilNolejPageComponentPluginGUI: ilUIPluginRouterGUI
 * @ilCtrl_Calls ilNolejPageComponentPluginGUI: ilPasswordAssistanceGUI
 */
class ilNolejPageComponentPluginGUI extends ilPageComponentPluginGUI
{
    const CMD_CREATE = "create";
    const CMD_SAVE = "save";
    const CMD_EDIT = "edit";
    const CMD_UPDATE = "update";
    const CMD_CANCEL = "cancel";
    const CMD_VIEW_PAGE = "viewPage";

    /** @var ilCtrl $ctrl */
    protected $ctrl;

    /** @var ilTemplate $tpl */
    protected $tpl;

    /** @var ilDBInterface */
    protected $db;

    /**
     * ilNolejPageComponentPluginGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
        $this->tpl = $DIC->ui()->mainTemplate();

        if (class_exists("ilNolejPlugin")) {
            $DIC->language()->loadLanguageModule(ilNolejPlugin::PREFIX);
        }

        parent::__construct();
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            default:
                // perform valid commands
                $cmd = $this->ctrl->getCmd();
                switch ($cmd) {
                    case self::CMD_CREATE:
                    case self::CMD_SAVE:
                    case self::CMD_EDIT:
                    case self::CMD_UPDATE:
                    case self::CMD_CANCEL:
                    case self::CMD_VIEW_PAGE:
                        $this->$cmd();
                        break;
                    default:
                        // Command not recognized
                        return;
                }
                break;
        }
    }

    /**
     * Language handler
     *
     * @param string $key
     * @return string
     */
    protected function txt(string $key): string
    {
        global $DIC;
        if (!class_exists("ilNolejPlugin")) {
            return "-" . $key . "-";
        }
        return $DIC->language()->txt(ilNolejPlugin::PREFIX . "_" . $key);
    }

    /**
     * Create
     */
    public function insert(): void
    {
        $form = $this->initForm(true);
        $this->tpl->setContent($form->getHTML());

        // Skip form
        // $a_properties = array();
        // $this->createElement($a_properties);
        // $this->returnToParent();
    }

    /**
     * Save new pc element
     */
    public function create(): void
    {
        $form = $this->initForm(true);

        if ($form->checkInput()) {
            if ($this->saveForm($form, true)) {
                $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_created"), true);
                $this->returnToParent();
            }
            $form->setValuesByPost();
        }
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Init the properties form and load the stored values
     */
    public function edit(): void
    {
        $form = $this->initForm(false);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Update
     */
    public function update(): void
    {
        $form = $this->initForm(false);
        if ($form->checkInput()) {
            if ($this->saveForm($form, false)) {
                $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
                $this->returnToParent();
            }
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * View Page
     */
    public function viewPage(): void
    {
        // $content = new ilNolejPageComponent($this->plugin, $properties["settings_id"]);
        // $renderer = new ilExternalContentRenderer($content);
        // $renderer->render();
    }

    /**
     * Init creation editing form
     * @param bool $a_create true: create component, false: edit component
     * @return ilPropertyFormGUI
     */
    protected function initForm($a_create = false)
    {
        return $this->initModulesListForm($a_create);
    }

    /**
     * @param bool $a_create true: create component, false: edit component
     * @return ilPropertyFormGUI
     */
    protected function initModulesListForm($a_create = false)
    {
        $properties = $this->getProperties();
        $form = new ilPropertyFormGUI();

        $modules = new ilRadioGroupInputGUI(
            $this->txt("module_select"),
            "document_id"
        );
        $modules->setRequired(true);

        $result = $this->db->queryF(
            "SELECT document_id, title"
            . " FROM " . ilNolejPlugin::TABLE_DOC
            . " WHERE status = %s",
            ["integer"],
            [ilNolejActivityManagementGUI::STATUS_COMPLETED]
        );

        while ($row = $this->db->fetchAssoc($result)) {
            $module = new ilRadioOption($row["title"], $row["document_id"]);
            $selected = (!$a_create && $properties["document_id"] == $row["document_id"]);
            $this->appendActivitiesListForm($module, $row["document_id"], $selected);
            $modules->addOption($module);
        }

        if (!$a_create) {
            $modules->setValue($properties["document_id"]);
        }

        $form->addItem($modules);

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton($a_create ? self::CMD_CREATE : self::CMD_UPDATE, $this->txt("cmd_choose"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cmd_cancel"));
        return $form;
    }

    /**
     * @param ilRadioOption $module
     * @param string $documentId
     * @param bool $moduleSelected
     * @return void
     */
    protected function appendActivitiesListForm($module, $documentId, $moduleSelected)
    {
        $properties = $this->getProperties();
        $activities = new ilRadioGroupInputGUI(
            $this->txt("activities_select"),
            "content_id"
        );
        $activities->setRequired(true);

        $result = $this->db->queryF(
            "SELECT content_id, type, `generated`"
            . " FROM " . ilNolejPlugin::TABLE_H5P
            . " WHERE document_id = %s"
            . " ORDER BY `generated` DESC",
            ["text"],
            [$documentId]
        );

        while ($row = $this->db->fetchAssoc($result)) {
            $activity = new ilRadioOption(
                $this->txt("activities_" . $row["type"]),
                $row["content_id"]
            );
            $activity->setInfo(
                ilDatePresentation::formatDate(
                    new ilDateTime($row["generated"], IL_CAL_UNIX)
                )
            );
            $activities->addOption($activity);
        }

        if ($moduleSelected) {
            $activities->setValue($properties["content_id"]);
        }
        $module->addSubItem($activities);
    }

    /**
     * Save the form values
     * @param ilPropertyFormGUI $form
     * @param bool $a_create
     * @return bool success
     */
    protected function saveForm($form, $a_create)
    {
        $documentId = $form->getInput("document_id");
        $contentId = $form->getInput("content_id");

        $a_properties = array(
            "document_id" => $documentId,
            "content_id" => $contentId
        );
        if ($a_create) {
            return $this->createElement($a_properties);
        }

        return $this->updateElement($a_properties);
    }

    /**
     * Cancel
     */
    public function cancel(): void
    {
        $this->returnToParent();
    }

    /**
     * Get HTML for element
     *
     * @param string page mode (edit, presentation, print, preview, offline)
     * @return string html code
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $a_plugin_version): string
    {
        if ($a_mode != "edit") {
            if (!isset($a_properties["content_id"])) {
                return "<p>Activity not found!</p>";
            }

            return ilObjNolejGUI::getH5PHtml((int) $a_properties["content_id"]);
        }

        if (!isset($a_properties["content_id"])) {
            return "<p>Activity not found!</p>";
        }

        $result = $this->db->queryF(
            "SELECT d.title, c.type"
            . " FROM " . ilNolejPlugin::TABLE_DOC . " d"
            . " INNER JOIN " . ilNolejPlugin::TABLE_H5P . " c"
            . " ON c.document_id = d.document_id"
            . " WHERE c.content_id = %s",
            ["integer"],
            [$a_properties["content_id"]]
        );

        if ($row = $this->db->fetchAssoc($result)) {
            return sprintf(
                "<p>" . $this->txt("activities_selected") . "</p>",
                $this->txt("activities_" . $row["type"]),
                $row["title"]
            );
        }

        return "<p>Activity does not exist!</p>";
    }

}
