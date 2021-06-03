<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Localization\Loc,
	Iplogic\Beru\ProfileTable,
	Iplogic\Beru\ConditionTable,
	Iplogic\Beru\TaskTable;

$checkParams = [
	"PROFILE" => true
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

Loc::loadMessages(__FILE__);



class ListEx extends Iplogic\Beru\Admin\TableList
{

	protected function filterMod() {
		$this->arFilter["PROFILE_ID"] = $_REQUEST['PROFILE_ID'];
	}

}


$arOpts = [
	[
		"NAME" => "active",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_ACTIVE"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => [
					Loc::getMessage("IPL_MA_YES"),
					Loc::getMessage("IPL_MA_NO"),
				],
				"reference_id" => [
					"Y",
					"N",
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"VIEW" => [
			"AddCheckField" =>[],
		],
	],
	[
		"NAME" => "sort",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_SORT"),
		"FILTER" => [],
		"VIEW" => [
			"AddInputField" => [],
		],
	],
	[
		"NAME" => "description",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_DESCRIPTION"),
	],
	[
		"NAME" => "id",
		"CAPTION" => "ID",
		"PROPERTY" => "N", 
		"UNIQ" => "Y",
		"FILTER" => [
			"VIEW" => "text", 

		],
		"HEADER_KEY" => [
			"align" => "right", 
			"default" => false, 
		],
	],
];

/* context menu */
$arContextMenu = [
	[
		"TEXT" => Loc::getMessage("IPL_MA_COND_ADD"),
		"TITLE" => Loc::getMessage("IPL_MA_COND_ADD_TITLE"),
		"ICON" => "btn_new",
		"MENU" => [
			[
				"TEXT" => Loc::getMessage("IPL_MA_IN_FID_COND"),
				"TITLE" => Loc::getMessage("IPL_MA_IN_FID_COND"),
				"ICON" => "btn_new",
				"ACTION" => [
					"TYPE" => "REDIRECT",
					"HREF" => "iplogic_beru_condition_edit.php?PROFILE_ID=".$PROFILE_ID."&type=IY&lang=".LANG,
				]
			],
		],
	],
	["SEPARATOR"=>"Y"],
	[
		"TEXT"  => Loc::getMessage("IPL_MA_PROFILE_SETTINGS"),
		"TITLE" => Loc::getMessage("IPL_MA_PROFILE_SETTINGS_TITLE"),
		"LINK"  => "iplogic_beru_profile_edit.php?ID=".$PROFILE_ID."&lang=".LANG,
	],
	[
		"TEXT"  => Loc::getMessage("IPL_MA_ACCORDANCES"),
		"TITLE" => Loc::getMessage("IPL_MA_ACCORDANCES_TITLE"),
		"LINK"  => "iplogic_beru_accordances_edit.php?PROFILE_ID=".$PROFILE_ID."&lang=".LANG,
	],
];



/* context menu for each line */
$arItemContextMenu = [
	[
		"TEXT" => Loc::getMessage("MAIN_ADMIN_LIST_EDIT"),
		"TITLE" => Loc::getMessage("MAIN_ADMIN_LIST_EDIT"),
		"ICON" => "edit",
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_condition_edit.php?PROFILE_ID=".$PROFILE_ID."&ID=##ID##&lang=".LANG,
		],
		"DEFAULT"=>true,
	],
	[
		"TEXT" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"TITLE" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"ICON" => "delete",
		"ACTION" => [
			"TYPE" => "DELETE",
			"PARAMS" => "PROFILE_ID=".$PROFILE_ID,
		]
	],
];

$Messages = [
	"DELETE_CONF" => Loc::getMessage("IPL_MA_DELETE_CONF"),
	"SELECTED" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
	"CHECKED" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
	"DELETE" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), 
	"ACTIVATE" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), 
	"DEACTIVATE" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), 
	"EDIT" => Loc::getMessage("MAIN_ADMIN_LIST_EDIT"), 
	"SAVE_ERROR_NO_ITEM" => Loc::getMessage("IPL_MA_SAVE_ERROR_NO_ITEM"),
	"SAVE_ERROR_UPDATE" => Loc::getMessage("IPL_MA_SAVE_ERROR_UPDATE"),
	"SAVE_ERROR_DELETE" => Loc::getMessage("IPL_MA_SAVE_ERROR_DELETE"),

];

$adminControl = new ListEx($moduleID);
$adminControl->arOpts = $arOpts;
$adminControl->Mess = $Messages;
$adminControl->arContextMenu = $arContextMenu;
$adminControl->arItemContextMenu = $arItemContextMenu;
$adminControl->gaCopy = "N";
$adminControl->sTableClass = "\Iplogic\Beru\ConditionTable";
$adminControl->filterFormAction = "/bitrix/admin/iplogic_beru_condition_list.php?PROFILE_ID=".$PROFILE_ID;

$adminControl->arGroupActions = [];


if ($adminControl->POST_RIGHT == "D") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$adminControl->initList("tbl_condition");

$adminControl->EditAction();
$adminControl->GroupAction();

if(($adminControl->isGroupAction() || $adminControl->isEditAction()) && $adminControl->POST_RIGHT=="W") {
	TaskTable::scheduleFeedProductsRefresh($PROFILE_ID);
}

$rsData = ConditionTable::getList(['order' => $adminControl->arSort, 'filter' => $adminControl->arFilter, 'select' => $adminControl->arSelect]);
$adminControl->prepareData($rsData);

$APPLICATION->SetTitle(Loc::getMessage('IPL_MA_LIST_TITLE')." #".$PROFILE_ID." (".$arProfile["NAME"].")");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($fatalErrors != ""){
	CAdminMessage::ShowMessage($fatalErrors);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}


/* ok message */
if($request->get("mess") === "ok")
	CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage("SAVED"), "TYPE"=>"OK"));


/* action errors */
if( count($adminControl->errors) ) {
	foreach($adminControl->errors as $error) {
		CAdminMessage::ShowMessage($error);
	}
}

$adminControl->renderList();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>