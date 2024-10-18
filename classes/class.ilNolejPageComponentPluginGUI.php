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
 * Page Component GUI.
 * @ilCtrl_isCalledBy ilNolejPageComponentPluginGUI: ilPCPluggedGUI
 */
class ilNolejPageComponentPluginGUI extends ilPageComponentPluginGUI
{
    public const CMD_INSERT = "insert";
    public const CMD_CREATE = "create";
    public const CMD_SAVE = "save";
    public const CMD_EDIT = "edit";
    public const CMD_UPDATE = "update";
    public const CMD_CANCEL = "cancel";

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilGlobalPageTemplate */
    protected $tpl;

    /** @var ilDBInterface */
    protected $db;

    /**
     * ilNolejPageComponentPluginGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
        $this->tpl = $DIC->ui()->mainTemplate();

        if (class_exists("ilNolejPlugin")) {
            $this->lng->loadLanguageModule(ilNolejPlugin::PREFIX);
        }
    }

    /**
     * Delegates incoming commands.
     * @throws ilException if command is not known
     * @return void
     */
    public function executeCommand(): void
    {
        global $DIC;

        if (!$this->plugin->isActive()) {
            throw new ilException("Plugin not active.");
        }

        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case strtolower(ilNolejActivitySelectorGUI::class):
                $refinery = $DIC->refinery();
                $request_wrapper = $DIC->http()->wrapper()->query();
                $selection_cmd = $request_wrapper->retrieve("selection_cmd", $refinery->kindlyTo()->string());
                $gui = $this->getNolejSelectorGUI("", $selection_cmd);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd();
                switch ($cmd) {
                    case self::CMD_INSERT:
                    case self::CMD_CREATE:
                    case self::CMD_SAVE:
                    case self::CMD_EDIT:
                    case self::CMD_UPDATE:
                    case self::CMD_CANCEL:
                        $this->$cmd();
                        break;

                    default:
                        // Command not recognized.
                        throw new ilException("Unknown command: '$cmd'");
            }
        }
    }

    /**
     * Create new page component element.
     * @return void
     */
    public function insert(): void
    {
        $form = $this->initSelectorForm(self::CMD_INSERT, self::CMD_CREATE);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save new page component element.
     * @return void
     */
    public function create(): void
    {
        // Save the form data.
        if ($this->saveData(true)) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_created"), true);
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_obj_not_created"), true);
        }

        // Return to the page editor.
        $this->returnToParent();
    }

    /**
     * Init the properties form and load the stored values.
     * @return void
     */
    public function edit(): void
    {
        $form = $this->initSelectorForm(self::CMD_EDIT, self::CMD_UPDATE);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init selector form.
     * @param string $cmd
     * @param string $cmdToCall
     * @return ilPropertyFormGUI
     */
    protected function initSelectorForm($cmd = "", $cmdToCall = "")
    {
        $form = new ilPropertyFormGUI();
        $exp = $this->getNolejSelectorGUI($cmd, $cmdToCall);
        $exp->handleCommand();

        $selector = new ilNonEditableValueGUI($this->plugin->txt("select_obj"), "", true);
        $selector->setValue($exp->getHTML());
        $form->addItem($selector);

        return $form;
    }

    /**
     * Update page component element.
     * @return void
     */
    public function update(): void
    {
        // Save the form data.
        if ($this->saveData(false)) {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_obj_not_modified"), true);
        }

        // Return to the page editor.
        $this->returnToParent();
    }

    /**
     * Get Nolej tree explorer.
     * @param string $cmd
     * @param string $selection_cmd
     * @return ilRepositorySelectorExplorerGUI
     */
    protected function getNolejSelectorGUI($cmd = "", $selection_cmd = "")
    {
        $target = $this->ctrl->getLinkTarget($this, $selection_cmd);
        $tree = new ilNolejActivitySelectorGUI($this, $cmd, null, $selection_cmd, $target);
        return $tree;
    }

    /**
     * Save the form values.
     * @param ilPropertyFormGUI $form
     * @param bool $a_create
     * @return bool success
     */
    protected function saveData($a_create)
    {
        global $DIC;

        $refinery = $DIC->refinery();
        $request_wrapper = $DIC->http()->wrapper()->query();

        $documentId = $request_wrapper->retrieve("document_id", $refinery->kindlyTo()->string());
        $contentId = $request_wrapper->retrieve("content_id", $refinery->kindlyTo()->int());

        $a_properties = [
            "document_id" => $documentId,
            "content_id" => $contentId,
        ];

        return $a_create
            ? $this->createElement($a_properties)
            : $this->updateElement($a_properties);
    }

    /**
     * Cancel the creation or the update and return to the editor.
     * @return void
     */
    public function cancel(): void
    {
        $this->returnToParent();
    }

    /**
     * Get HTML for page component element depending on the context.
     * @param string $a_mode page mode (edit, presentation, print, preview, offline)
     * @param array $a_properties
     * @param string $a_plugin_version
     * @return string html code
     */
    public function getElementHTML(
        string $a_mode,
        array $a_properties,
        string $a_plugin_version
    ): string {
        $nolej = ilNolejPlugin::getInstance();

        if ($a_mode != "edit") {
            if (!isset($a_properties["content_id"])) {
                return "<p>Activity not found!</p>";
            }

            return ilNolejPlugin::renderH5P((int) $a_properties["content_id"]);
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
                "<p>" . $nolej->txt("activities_selected") . "</p>",
                $nolej->txt("activities_" . $row["type"]),
                $row["title"]
            );
        }

        return "<p>Activity does not exist!</p>";
    }
}
