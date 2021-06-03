<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Iplogic\Beru\ProfileTable,
	\Iplogic\Beru\TaskTable,
	\Iplogic\Beru\ProductTable;


$checkParams = [
	"PROFILE" => true
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

Loc::loadMessages(__FILE__);


class ListEx extends Iplogic\Beru\Admin\TableList
{

	public function GroupActionEx($ID) {

		switch($_REQUEST['action']) {
			case "set_price":
				TaskTable::addPriceUpdateTask($ID, $_REQUEST['PROFILE_ID']);
			break;
			case "gr_hide":
				TaskTable::hideProductTask($ID, $_REQUEST['PROFILE_ID']);
			break;
			case "gr_unhide":
				TaskTable::showProductTask($ID, $_REQUEST['PROFILE_ID']);
			break;
		}
	}


	protected function filterMod() {
		$this->arFilter["PROFILE_ID"] = $_REQUEST['PROFILE_ID'];
	}

}


$arOpts = [
	[
		"NAME" => "sku_id",
		"CAPTION" => "SKU ID",
		"FILTER" => [
			"COMPARE" => "%",
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "iplogic_beru_product_detail.php?PROFILE_ID=".$PROFILE_ID."&ID=##id##&lang=".LANG,
				"TYPE" => "HREF",
			],
		],
	],
	[
		"NAME" => "market_sku",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_MARKET_SKU"),
		"FILTER" => [
			"COMPARE" => "%",
		],
	],
	[
		"NAME" => "product_id",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_PRODUCT_ID"),
		"FILTER" => [
			"COMPARE" => "%",
		],
	],
	[
		"NAME" => "name",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_NAME"),
		"FILTER" => [
			"COMPARE" => "%",
		],
	],
	[
		"NAME" => "vendor",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_VENDOR"),
		"FILTER" => [
			"COMPARE" => "%",
		],
		"HEADER_KEY" => [
			"default" => false,
		],
	],
	[
		"NAME" => "availability",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_AVAILABILITY"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => [
					Loc::getMessage("IPL_MA_ACTIVE"),
					Loc::getMessage("IPL_MA_INACTIVE"),
				],
				"reference_id" => [
					"Y",
					"N",
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "<div class=\"availability marker ##availability##\"></div>",
				"TYPE" => "HTML",
			],
		],
	],
	[
		"NAME" => "state",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_STATE"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => [
					Loc::getMessage("IPL_MA_STATE_FEED_ONLY"),
					Loc::getMessage("IPL_MA_STATE_READY"),
					Loc::getMessage("IPL_MA_STATE_IN_WORK"),
					Loc::getMessage("IPL_MA_STATE_NEED_INFO"),
					Loc::getMessage("IPL_MA_STATE_NEED_CONTENT"),
					Loc::getMessage("IPL_MA_STATE_REJECTED"),
					Loc::getMessage("IPL_MA_STATE_SUSPENDED"),
					Loc::getMessage("IPL_MA_STATE_OTHER"),
				],
				"reference_id" => [
					"FEED_ONLY",
					"READY",
					"IN_WORK",
					"NEED_INFO",
					"NEED_CONTENT",
					"REJECTED",
					"SUSPENDED",
					"OTHER",
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"REPLACE" => [
			"FEED_ONLY" 	=> "<span style='color:#c95a00;'>".Loc::getMessage("IPL_MA_STATE_FEED_ONLY")."</span>",
			"READY" 		=> "<span style='color:#1cc43b;'>".Loc::getMessage("IPL_MA_STATE_READY")."</span>",
			"IN_WORK" 		=> "<span style='color:#1d2bec;'>".Loc::getMessage("IPL_MA_STATE_IN_WORK")."</span>",
			"NEED_INFO" 	=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_NEED_INFO")."</span>",
			"NEED_CONTENT" 	=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_NEED_CONTENT")."</span>",
			"REJECTED" 		=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_REJECTED")."</span>",
			"SUSPENDED" 	=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_SUSPENDED")."</span>",
			"OTHER" 		=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_OTHER")."</span>",
		],
		"VIEW" => [
			"AddField" => [
				"PARAM" => "##state##",
			],
		],
	],
	[
		"NAME" => "reject_reason",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_REJECT_REASON"),
		"FILTER" => [
			"COMPARE" => "%",
		],
		"HEADER_KEY" => [
			"default" => false,
		],
	],
	[
		"NAME" => "price",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_PRICE"),
		"FILTER" => [
			"COMPARE" => "%",
		],
	],
	[
		"NAME" => "hidden",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_HIDDEN"),
		"FILTER" => [
			"VIEW" => "select",
			"VALUES" => [
				"reference" => [
					Loc::getMessage("IPL_MA_HIDDEN"),
					Loc::getMessage("IPL_MA_SHOW"),
				],
				"reference_id" => [
					"Y",
					"N",
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "<div class=\"hidden marker ##hidden##\"></div>",
				"TYPE" => "HTML",
			],
		],
	],
	[
		"NAME" => "api",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_API"),
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
		"HEADER_KEY" => [
			"default" => false, 
		],
	],
	[
		"NAME" => "feed",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_FEED"),
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
		"HEADER_KEY" => [
			"default" => false, 
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

$arContext = [
	[
		"TEXT"=>Loc::getMessage("IPL_MA_REFRESH"),
		"LINK"=>"iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&refresh=Y&lang=".LANG,
		"TITLE"=>Loc::getMessage("IPL_MA_REFRESH_TITLE"),
	],
	[
		"TEXT"=>Loc::getMessage("IPL_MA_CACHE"),
		"LINK"=>"iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&cache=Y&lang=".LANG,
		"TITLE"=>Loc::getMessage("IPL_MA_CACHE_TITLE"),
	],
];

$arItemContext = [
	[
		"TEXT" => Loc::getMessage("IPL_MA_DETAIL"),
		"TITLE" => Loc::getMessage("IPL_MA_DETAIL"),
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_product_detail.php?PROFILE_ID=".$PROFILE_ID."&ID=##ID##&lang=".LANG,
		],
		"DEFAULT" => true
	],
	[
		"TEXT" => Loc::getMessage("IPL_MA_SET_PRICE"),
		"TITLE" => Loc::getMessage("IPL_MA_SET_PRICE"),
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&ID=##ID##&action=send_price&lang=".LANG,
		]
	],
	[
		"TEXT" => Loc::getMessage("IPL_MA_HIDE"),
		"TITLE" => Loc::getMessage("IPL_MA_HIDE"),
		//"ICON" => "edit",
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&ID=##ID##&action=hide&lang=".LANG,
		]
	],
	[
		"TEXT" => Loc::getMessage("IPL_MA_UNHIDE"),
		"TITLE" => Loc::getMessage("IPL_MA_UNHIDE"),
		//"ICON" => "edit",
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&ID=##ID##&action=show&lang=".LANG,
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
$adminControl->arContextMenu = $arContext;
$adminControl->arItemContextMenu = $arItemContext;
$adminControl->defaultBy = 'ID';
$adminControl->defaultOrder = "DESC";
$adminControl->gaCopy = "N";
$adminControl->gaDelete = "N";
$adminControl->gaActivate = "N";
$adminControl->gaDeactivate = "N";
$adminControl->sTableClass = "\Iplogic\Beru\ProductTable";
$adminControl->filterFormAction = "/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID;

$adminControl->arGroupActions = [
	"set_price" => Loc::getMessage("IPL_MA_SET_PRICE"),
	"gr_hide" => Loc::getMessage("IPL_MA_HIDE"),
	"gr_unhide" => Loc::getMessage("IPL_MA_UNHIDE"),
];


if ($adminControl->POST_RIGHT == "D") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$adminControl->initList("tbl_product");

$adminControl->EditAction();
$adminControl->GroupAction();

if ($request->get("refresh") == "Y"){
	exec("wget -b -q -O - https://".Option::get($moduleID,"domen") ."/bitrix/services/iplogic/mkpapi/products.php");
	LocalRedirect("/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG);
}
if ($request->get("cache") == "Y"){
	$rsData = ProductTable::getList(['order' => $adminControl->arSort, 'filter' => $adminControl->arFilter, 'select' => ["ID"]]);
	while ($prod = $rsData->Fetch()) {
		$rsTask = TaskTable::getList(["filter"=>["TYPE"=>"PU","STATE"=>"WT","ENTITY_ID"=>$prod["ID"]]]);
		if (!$rsTask->Fetch()) {
			$arFields = [
				"PROFILE_ID" 		=> $PROFILE_ID,
				"UNIX_TIMESTAMP" 	=> time(),
				"TYPE" 				=> "PU",
				"STATE" 			=> "WT",
				"ENTITY_ID" 		=> $prod["ID"],
				"TRYING" 			=> 0
			];
			TaskTable::add($arFields);
		}
	}
	LocalRedirect("/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG);
}
if ($request->get("action") == "hide"){
	TaskTable::hideProductTask($request->get("ID"), $PROFILE_ID);
	LocalRedirect("/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG);
}
if ($request->get("action") == "show"){
	TaskTable::showProductTask($request->get("ID"), $PROFILE_ID);
	LocalRedirect("/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG);
}
if ($request->get("action") == "send_price"){
	TaskTable::addPriceUpdateTask($request->get("ID"), $PROFILE_ID);
	LocalRedirect("/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG);
}

$rsData = ProductTable::getList(['order' => $adminControl->arSort, 'filter' => $adminControl->arFilter, 'select' => $adminControl->arSelect]);
$adminControl->prepareData($rsData);

$APPLICATION->SetTitle(Loc::getMessage('IPL_MA_LIST_TITLE')." #".$PROFILE_ID." (".$arProfile["NAME"].")");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($fatalErrors != ""){
	CAdminMessage::ShowMessage($fatalErrors);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

echo '<style>
.marker { width:12px; height:12px; border-radius:6px; -moz-border-radius:6px; -webkit-border-radius:6px; }
.marker.availability.Y { background-color:#55d80e; }
.marker.availability.N { background-color:red; }
.marker.hidden.Y { background-color:#8f4d2c; }
.marker.hidden.N { background-color:none; }
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

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>