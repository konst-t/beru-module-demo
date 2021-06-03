<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Localization\Loc,
	Iplogic\Beru\YMAPI,
	Iplogic\Beru\ProfileTable,
	Iplogic\Beru\OrderTable;

Loc::loadMessages(__FILE__);


/* fatal errors check, creat control object and get table data */
$checkParams = [
	"PROFILE" => true
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");


/* add extends class if needed */
class ListEx extends Iplogic\Beru\Admin\TableList
{

	public  $ready_to_ship = [],
			$shipped = [];


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
		if (isset($this->arFilter[">=SHIPMENT_TIMESTAMP"])) {
			if ($udate = $this->getUnixDate($this->arFilter[">=SHIPMENT_TIMESTAMP"]))
				$this->arFilter[">=SHIPMENT_TIMESTAMP"] = $udate;
			else
				unset ($this->arFilter[">=SHIPMENT_TIMESTAMP"]);
		}
		if (isset($this->arFilter["<=SHIPMENT_TIMESTAMP"])) {
			if ($udate = $this->getUnixDate($this->arFilter["<=SHIPMENT_TIMESTAMP"],true))
				$this->arFilter["<=SHIPMENT_TIMESTAMP"] = $udate;
			else
				unset ($this->arFilter["<=SHIPMENT_TIMESTAMP"]);
		}
	}

	public function GroupActionEx($ID) {
		switch($_REQUEST['action']) {
			case "ready_to_ship":
				$order = OrderTable::getById($ID);
				// if($order["STATE"]!="S_PROCESSING_STARTED" || $order["BOXES_SENT"]!="Y") return;
				$this->ready_to_ship[$order["EXT_ID"]] = [
					"ID" => $order["ID"],
					"EXT_ID" => $order["EXT_ID"],
					"ORDER_ID" => $order["ORDER_ID"]
				];
			break;
			case "shipped":
				$order = OrderTable::getById($ID);
				// if($order["STATE"]!="S_PROCESSING_READY_TO_SHIP" || $order["BOXES_SENT"]!="Y") return;
				$this->shipped[$order["EXT_ID"]] = [
					"ID" => $order["ID"],
					"EXT_ID" => $order["EXT_ID"],
					"ORDER_ID" => $order["ORDER_ID"]
				];
			break;
		}
	}

}


/* opts */
$arOpts = [
	[
		"NAME" => "ext_id",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_EXT_ID"),
		"FILTER" => [
			"COMPARE" => "%",
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "iplogic_beru_order_detail.php?PROFILE_ID=".$PROFILE_ID."&ID=##id##&lang=".LANG,
				"TYPE" => "HREF",
			],
		],
	],
	[
		"NAME" => "order_id",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_ORDER_ID"),
		"FILTER" => [
			"COMPARE" => "%",
		],
		"VIEW" => [
			"AddViewField" => [
				"PARAM" => "sale_order_view.php?ID=##order_id##&lang=".LANG,
				"TYPE" => "HREF",
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
					Loc::getMessage("IPL_MA_STATUS_NEW"),
					Loc::getMessage("IPL_MA_STATUS_PROCESSING_READY_TO_SHIP"),
					Loc::getMessage("IPL_MA_STATUS_PROCESSING_SHIPPED"),
					Loc::getMessage("IPL_MA_STATUS_CANCELLED_SHOP_FAILED"),
					Loc::getMessage("IPL_MA_STATUS_PROCESSING_STARTED"),
					Loc::getMessage("IPL_MA_STATUS_DELIVERED"),
					Loc::getMessage("IPL_MA_STATUS_DELIVERY"),
					Loc::getMessage("IPL_MA_STATUS_PICKUP"),
					Loc::getMessage("IPL_MA_STATUS_CANCELLED_BY_MARKETPLACE"),
					Loc::getMessage("IPL_MA_STATUS_UNPAID_WAITING_USER_INPUT"),
					Loc::getMessage("IPL_MA_STATUS_UNKNOWN"),
				],
				"reference_id" => [
					"S_NEW",
					"S_PROCESSING_READY_TO_SHIP",
					"S_PROCESSING_SHIPPED",
					"S_CANCELLED_SHOP_FAILED",
					"S_PROCESSING_STARTED",
					"S_DELIVERED",
					"S_DELIVERY",
					"S_PICKUP",
					"S_CANCELLED_BY_MARKETPLACE",
					"S_UNPAID_WAITING_USER_INPUT",
					"S_UNKNOWN",
				]
			],
			"DEFAULT" => Loc::getMessage("IPL_MA_ALL"),
		],
		"REPLACE" => [
			"S_NEW" 								=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATUS_NEW")."</span>",
			"S_PROCESSING_STARTED" 					=> "<span style='color:#4527cb;'>".Loc::getMessage("IPL_MA_STATUS_PROCESSING_STARTED")."</span>",
			"S_PROCESSING_READY_TO_SHIP" 			=> "<span style='color:#4527cb;'>".Loc::getMessage("IPL_MA_STATUS_PROCESSING_READY_TO_SHIP")."</span>",
			"S_PROCESSING_SHIPPED" 					=> "<span style='color:#4527cb;'>".Loc::getMessage("IPL_MA_STATUS_PROCESSING_SHIPPED")."</span>",
			"S_CANCELLED_SHOP_FAILED" 				=> "<span style='color:#686868;'>".Loc::getMessage("IPL_MA_STATUS_CANCELLED_SHOP_FAILED")."</span>",
			"S_DELIVERED" 							=> "<span style='color:#38a915;'>".Loc::getMessage("IPL_MA_STATUS_DELIVERED")."</span>",
			"S_DELIVERY" 							=> "<span style='color:#38a915;'>".Loc::getMessage("IPL_MA_STATUS_DELIVERY")."</span>",
			"S_PICKUP" 								=> "<span style='color:#38a915;'>".Loc::getMessage("IPL_MA_STATUS_PICKUP")."</span>",
			"S_CANCELLED" 							=> "<span style='color:#686868;'>".Loc::getMessage("IPL_MA_STATUS_CANCELLED_BY_MARKETPLACE")."</span>",
			"S_UNPAID_WAITING_USER_INPUT" 			=> "<span style='color:#4527cb;'>".Loc::getMessage("IPL_MA_STATUS_UNPAID_WAITING_USER_INPUT")."</span>",
			"S_UNKNOWN" 							=> "<span style='color:#4527cb;'>".Loc::getMessage("IPL_MA_STATUS_UNKNOWN")."</span>",
		],
		"VIEW" => [
			"AddField" => [
				"PARAM" => "##state##",
			],
		],
	],
	[
		"NAME" => "state_code",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_STATE_CODE"),
		"FILTER" => [],
		"HEADER_KEY" => [
			"default" => false, 
		],
	],
	[
		"NAME" => "human_time",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_TIME"),
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
		"NAME" => "shipment_date",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_SHIPMENT_DATE"),
	],
	[
		"NAME" => "shipment_timestamp",
		"VIEW" => "hidden",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_SHIPMENT_DATE"),
		"FILTER" => [
			"VIEW" => "date-from-to",
		],
	],
	[
		"NAME" => "fake",
		"CAPTION" => Loc::getMessage("IPL_MA_CAPTION_FAKE"),
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
			"AddField" => [
				"PARAM" => "##fake##",
			],
		],
		"REPLACE" => [
			"Y" => Loc::getMessage("IPL_MA_YES"),
			"N"  => "",
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
$arContextMenu = [];



/* context menu for each line */
$arItemContextMenu = [
	[
		"TEXT" => Loc::getMessage("IPL_MA_DETAIL"),
		"TITLE" => Loc::getMessage("IPL_MA_DETAIL"),
		"ACTION" => [
			"TYPE" => "REDIRECT",
			"HREF" => "iplogic_beru_order_detail.php?PROFILE_ID=".$PROFILE_ID."&ID=##ID##&lang=".LANG,
		],
		"DEFAULT" => true
	],
];



/* lang messages in classes */
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



/* prepare control object */
$adminControl = new ListEx($moduleID);
$adminControl->arOpts = $arOpts;
$adminControl->Mess = $Messages;
$adminControl->arContextMenu = $arContextMenu;
$adminControl->arItemContextMenu = $arItemContextMenu;
$adminControl->defaultBy = 'ID';
$adminControl->defaultOrder = "DESC";
$adminControl->gaCopy = "N";
$adminControl->gaActivate = "N";
$adminControl->gaDeactivate = "N";
$adminControl->sTableClass = "\Iplogic\Beru\OrderTable";
$adminControl->filterFormAction = "/bitrix/admin/iplogic_beru_order_list.php?PROFILE_ID=".$PROFILE_ID;


/* nonstandard group actions */
$adminControl->arGroupActions = [
	"ready_to_ship" => Loc::getMessage("IPL_MA_READY_TO_SHIP"),
	"shipped" => Loc::getMessage("IPL_MA_SHIPPED"),
];


if ($adminControl->POST_RIGHT == "D") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));


/* exec actions */
$adminControl->initList("tbl_order");
$adminControl->EditAction();
$adminControl->GroupAction();


if (count($adminControl->ready_to_ship)) { 
	$statusKey = "S_PROCESSING_READY_TO_SHIP";
	$newStatusCode = "PROCESSING READY_TO_SHIP";
	$oredersParts = array_chunk($adminControl->ready_to_ship, 30);
	foreach($oredersParts as $oredersPart) {
		$orders = [];
		foreach($oredersPart as $order) {
			$orders[] = [
				"id" => $order["EXT_ID"],
				"status" => "PROCESSING",
				"substatus" => "READY_TO_SHIP",
			];
		} 
		$api = new YMAPI($arProfile["ID"]);
		$res = $api->setOrderStatuses($orders);
		if ($res["status"]==200) {
			foreach($res["body"]["result"]["orders"] as $res_order) { 
				if ($res_order["updateStatus"] == "OK") {
					$order = $adminControl->ready_to_ship[$res_order["id"]];
					$arOUFields = [
						"STATE" => $statusKey,
						"STATE_CODE" => $newStatusCode,
						"READY_TIME" => time(),
					];
					OrderTable::update($order["ID"],$arOUFields);
					$arStFields = [
						'STATUS_ID' => $arProfile["STATUSES"][$statusKey],
					];
					\CSaleOrder::Update($order["ORDER_ID"], $arStFields);
				}
				else {
					$adminControl->addError(Loc::getMessage("IPL_MA_SAVE_ERROR_UPDATE")." [#".$res_order["id"]."]<br>".$res_order["errorDetails"]);
				}
			}
		}
		else {
			$adminControl->addError(Loc::getMessage("IPL_MA_SAVE_ERROR_UPDATE")."<br>".$res["body"]["errors"][0]["code"]." - ".$res["body"]["errors"][0]["message"]);
		}
	}
}
if (count($adminControl->shipped)) {
	$statusKey = "S_PROCESSING_SHIPPED";
	$newStatusCode = "PROCESSING SHIPPED";
	$oredersParts = array_chunk($adminControl->shipped, 30);
	foreach($oredersParts as $oredersPart) {
		$orders = [];
		foreach($oredersPart as $order) {
			$orders[] = [
				"id" => $order["EXT_ID"],
				"status" => "PROCESSING",
				"substatus" => "SHIPPED",
			];
		} 
		$api = new YMAPI($arProfile["ID"]);
		$res = $api->setOrderStatuses($orders);
		if ($res["status"]==200) {
			foreach($res["body"]["result"]["orders"] as $res_order) { 
				if ($res_order["updateStatus"] == "OK") {
					$order = $adminControl->shipped[$res_order["id"]];
					$arOUFields = [
						"STATE" => $statusKey,
						"STATE_CODE" => $newStatusCode,
					];
					OrderTable::update($order["ID"],$arOUFields);
					$arStFields = [
						'STATUS_ID' => $arProfile["STATUSES"][$statusKey],
					];
					\CSaleOrder::Update($order["ORDER_ID"], $arStFields);
				}
				else {
					$adminControl->addError(Loc::getMessage("IPL_MA_SAVE_ERROR_UPDATE")." [#".$res_order["id"]."]<br>".$res_order["errorDetails"]);
				}
			}
		}
		else {
			$adminControl->addError(Loc::getMessage("IPL_MA_SAVE_ERROR_UPDATE")."<br>".$res["body"]["errors"][0]["code"]." - ".$res["body"]["errors"][0]["message"]);
		}
	}
}



/* get list and put it in control object */
$rsData = OrderTable::getList(['order' => $adminControl->arSort, 'filter' => $adminControl->arFilter, 'select' => $adminControl->arSelect]);
$adminControl->prepareData($rsData);



/* starting output */
$APPLICATION->SetTitle(Loc::getMessage('IPL_MA_LIST_TITLE')." #".$PROFILE_ID." (".$arProfile["NAME"].")");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


/* fatal errors */
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