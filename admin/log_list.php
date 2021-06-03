<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Localization\Loc,
	Iplogic\Beru\ProfileTable,
	Iplogic\Beru\ApiLogTable;

$checkParams = [];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

Loc::loadMessages(__FILE__);


/* add extends class if needed */
class ListEx extends Iplogic\Beru\Admin\TableList
{


	protected function filterMod() {
		if (isset($this->arFilter[">=UNIX_TIMESTAMP"])) {
			if ($udate = $this->getUnixDate($this->arFilter[">=UNIX_TIMESTAMP"]))
				$this->arFilter[">=UNIX_TIMESTAMP"] = $udate;
			else
				unset ($this->arFilter[">=UNIX_TIMESTAMP"]);
		}
		if (isset($this->arFilter["<=UNIX_TIMESTAMP"])) {
			if ($udate = $this->getUnixDate($this->arFilter["<=UNIX_TIMESTAMP"],true))
				$this->arFilter["<=UNIX_TIMESTAMP"] = $udate;
			else
				unset ($this->arFilter["<=UNIX_TIMESTAMP"]);
		}
	}


}



/* get service data and preforms*/
$rsProfiles = ProfileTable::GetList();
while($arProfile = $rsProfiles->Fetch()){
	$arProfiles[$arProfile["ID"]] = $arProfile["NAME"]." [".$arProfile["ID"]."]";
	$profile_reference[] = $arProfile["NAME"]." [".$arProfile["ID"]."]";
	$profile_reference_id[] = $arProfile["ID"];
}



/* opts */
$arOpts = [
	[
		"NAME" => "state",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_STATE"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => [
					Loc::getMessage("IPL_MA_STATE_OK"),
					Loc::getMessage("IPL_MA_STATE_RJ"),
					Loc::getMessage("IPL_MA_STATE_EX"),
					Loc::getMessage("IPL_MA_STATE_DF"),
				],
				"reference_id" => [
					"OK",
					"RJ",
					"EX",
					"DF"
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "<div class=\"state_marker ##state##\"></div>",
				"TYPE" => "HTML",
			],
		],
	],
	[
		"NAME" => "human_time",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_TIME"),
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "/bitrix/admin/iplogic_beru_log_detail.php?ID=##id##&lang=".LANGUAGE_ID,
				"TYPE" => "HREF",
			],
		],
	],
	[
		"NAME" => "unix_timestamp",
		"VIEW" => "hidden",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_TIME"),
		"FILTER" => [
			"VIEW" => "date-from-to",
		],
	],
	[
		"NAME" => "profile_id",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_PROFILE"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => $profile_reference,
				"reference_id" => $profile_reference_id
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "/bitrix/admin/iplogic_beru_profile_edit.php?ID=##profile_id_real##&lang=".LANGUAGE_ID,
				"TYPE" => "HREF",
			],
		],
		"REPLACE" => $arProfiles,
	],
	[
		"NAME" => "type",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_TYPE"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => [
					Loc::getMessage("IPL_MA_TYPE_IC"),
					Loc::getMessage("IPL_MA_TYPE_OG"),
				],
				"reference_id" => [
					"IC",
					"OG",
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"VIEW" => [
			"AddField" => [
				"PARAM" => "##type##",
			],
		],
		"REPLACE" => [
			"IC" => Loc::getMessage("IPL_MA_TYPE_IC"),
			"OG" => Loc::getMessage("IPL_MA_TYPE_OG"),
		]
	],
	[
		"NAME" => "url",
		"CAPTION" => "URL",
		"FILTER" => [
			"COMPARE" => "%",
		],
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
		"TEXT"=>Loc::getMessage("IPL_MA_CLEAN"),
		"LINK"=>"javascript:deleteConfirm();",
		"TITLE"=>Loc::getMessage("IPL_MA_CLEAN_TITLE"),
		"ICON"=>"btn_delete",
	],
];



/* context menu for each line */
$arItemContextMenu = [
	[
		"TEXT" => Loc::getMessage("IPL_MA_DETAIL"),
		"TITLE" => Loc::getMessage("IPL_MA_DETAIL"),
		//"ICON" => "edit",
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_log_detail.php?ID=##ID##&lang=".LANG,
		],
		"DEFAULT" => true
	],
	[
		"TEXT" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"TITLE" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"ICON" => "delete",
		"ACTION" => [
			"TYPE" => "DELETE",
		]
	],
];


/* lang messages in classes */
$Messages = [
	"SELECTED" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
	"CHECKED" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
	"DELETE" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), 
	"ACTIVATE" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), 
	"DEACTIVATE" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), 
	"EDIT" => Loc::getMessage("MAIN_ADMIN_LIST_EDIT"), 
	"SAVE_ERROR_NO_ITEM" => Loc::getMessage("IPL_MA_SAVE_ERROR_NO_ITEM"),
	"SAVE_ERROR_UPDATE" => Loc::getMessage("IPL_MA_SAVE_ERROR_UPDATE"),
	"SAVE_ERROR_DELETE" => Loc::getMessage("IPL_MA_SAVE_ERROR_DELETE"),
	"DELETE_CONF" => Loc::getMessage("IPL_MA_DELETE_CONF"),
];



/* prepare control object */
$adminControl = new ListEx($moduleID);
$adminControl->arOpts = $arOpts;
$adminControl->Mess = $Messages;
$adminControl->arContextMenu = $arContextMenu;
$adminControl->arItemContextMenu = $arItemContextMenu;
$adminControl->defaultBy = 'UNIX_TIMESTAMP';
$adminControl->defaultOrder = "DESC";
$adminControl->gaCopy = "N";
$adminControl->gaActivate = "N";
$adminControl->gaDeactivate = "N";
$adminControl->sTableClass = "\Iplogic\Beru\ApiLogTable";


/* nonstandard group actions */
$adminControl->arGroupActions = [];



if ($adminControl->POST_RIGHT == "D") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));


/* exec actions */
$adminControl->initList("tbl_iplogic_beru_log");
$adminControl->EditAction();
$adminControl->GroupAction();
if ($request->get("clean") == "Y"){
	if(!ApiLogTable::clear()){
		$adminControl->errors[] = Loc::getMessage("IPL_MA_SAVE_ERROR_DELETE");
	}
	else {
		LocalRedirect("/bitrix/admin/iplogic_beru_log_list.php?lang=".LANG);
	}
}


/* get list and put it in control object */
$rsData = ApiLogTable::getList(['order' => $adminControl->arSort, 'filter' => $adminControl->arFilter, 'select' => $adminControl->arSelect]);
$adminControl->prepareData($rsData);



/* starting output */
$APPLICATION->SetTitle(Loc::getMessage('IPL_MA_LIST_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

echo '<style>
.state_marker { width:12px; height:12px; border-radius:6px; -moz-border-radius:6px; -webkit-border-radius:6px; }
.state_marker.RJ { background-color:red; }
.state_marker.OK { background-color:#55d80e; }
.state_marker.EX { background-color:#ffae00; }
.state_marker.DF { background-color:#1d2bec; }
</style>';


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

echo ("<script>
	function deleteConfirm() {
		if (window.confirm('".Loc::getMessage("IPL_MA_CLEAR_CONFIRM")."')) {
			window.location.href='iplogic_beru_log_list.php?clean=Y&lang=".LANG."';
		}
	}
</script>");

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>