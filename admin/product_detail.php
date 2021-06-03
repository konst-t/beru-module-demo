<?
$moduleID = 'iplogic.beru';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Localization\Loc,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\ProductTable,
	\Iplogic\Beru\TaskTable;

Loc::loadMessages(__FILE__);


/* fatal errors check, creat control object and get table data */
$checkParams = [
	"PROFILE" => true,
	"ID" => true,
	"CLASS" => "\Iplogic\Beru\ProductTable"
];


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

if ($ID > 0){
	$arFields["DETAILS"] = unserialize($arFields["DETAILS"]);
}


$adminControl = new \Iplogic\Beru\Admin\Info($moduleID);


/* get service data and preforms*/
$res = CIblockElement::getById($arFields["PRODUCT_ID"]);
$arElement = $res->Fetch();

$arState = [
	"FEED_ONLY" 	=> "<span style='color:#c95a00;'>".Loc::getMessage("IPL_MA_STATE_FEED_ONLY")."</span>",
	"READY" 		=> "<span style='color:#1cc43b;'>".Loc::getMessage("IPL_MA_STATE_READY")." [READY]</span>",
	"IN_WORK" 		=> "<span style='color:#1d2bec;'>".Loc::getMessage("IPL_MA_STATE_IN_WORK")." [IN_WORK]</span>",
	"NEED_INFO" 	=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_NEED_INFO")." [NEED_INFO]</span>",
	"NEED_CONTENT" 	=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_NEED_CONTENT")." [NEED_CONTENT]</span>",
	"REJECTED" 		=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_REJECTED")." [REJECTED]</span>",
	"SUSPENDED" 	=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_SUSPENDED")." [SUSPENDED]</span>",
	"OTHER" 		=> "<span style='color:red;'>".Loc::getMessage("IPL_MA_STATE_OTHER")." [OTHER]</span>",
];

$info = "SKU ID: <b>".$arFields["SKU_ID"]."</b><br><br>".
		Loc::getMessage("IPL_MA_NAME").": ".$arFields["NAME"]."<br><br>".
		Loc::getMessage("IPL_MA_PROFILE").": <a href=\"/bitrix/admin/iplogic_beru_profile_edit.php?ID=".
			$arFields["PROFILE_ID"]."&lang=".LANGUAGE_ID."\">".$arProfile["NAME"]."</a><br><br>".
		Loc::getMessage("IPL_MA_MARKET_SKU").": ";
if ($arFields["MARKET_SKU"] == "") {
	$info .= Loc::getMessage("IPL_MA_NO");
}
else {
	$info .= $arFields["MARKET_SKU"];
}
$info.= "<br><br>".Loc::getMessage("IPL_MA_PRODUCT_ID").": ";
if ($arFields["PRODUCT_ID"] < 1) {
	$info .= Loc::getMessage("IPL_MA_NO");
}
elseif ($arElement) {
	$info .= "<a href=\"iblock_element_edit.php?IBLOCK_ID=".$arElement["IBLOCK_ID"]."&type=".$arElement["IBLOCK_TYPE_ID"].
		"&ID=".$arFields["PRODUCT_ID"]."&lang=".LANGUAGE_ID."\">".$arFields["PRODUCT_ID"]."</a>";
}
else {
	$info .= $arFields["PRODUCT_ID"];
}
$info.= "<br><br>".Loc::getMessage("IPL_MA_VENDOR").": ".$arFields["VENDOR"]."<br><br>".Loc::getMessage("IPL_MA_AVAILABILITY").": ";
if ($arFields["AVAILABILITY"] == "Y") {
	$info .= "<span style=\"color:green;\">".Loc::getMessage("IPL_MA_YES")."</span>";
}
else {
	$info .= "<span style=\"color:red;\">".Loc::getMessage("IPL_MA_NO")."</span>";
}
$info.= "<br><br>";

$info.= Loc::getMessage("IPL_MA_STATE").": ".$arState[$arFields["STATE"]]."<br><br>";
if ($arFields["REJECT_REASON"] != "") {
	$info.= Loc::getMessage("IPL_MA_REJECT_REASON").": ".$arFields["REJECT_REASON"]."<br><br>";
}
if ($arFields["REJECT_NOTES"] != "") {
	$info.= Loc::getMessage("IPL_MA_REJECT_NOTES").": ".$arFields["REJECT_NOTES"]."<br><br>";
}
$info.= Loc::getMessage("IPL_MA_PRICE").": ";
if ($arFields["PRICE"] == "") {
	$info .= Loc::getMessage("IPL_MA_NO");
}
else {
	$info .= $arFields["PRICE"];
}
if ($arFields["DETAILS"] != "") {
	$info.= "<br><br>".Loc::getMessage("IPL_MA_DETAILS").": <hr>".Control::toHtml(print_r($arFields["DETAILS"],true))."<br><br>";
}



/* tabs and opts */
$arTabs = [
	["DIV" => "edit1", "TAB" => Loc::getMessage("IPL_MA_DETAIL"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_DETAIL_TITLE")],
];
$arOpts = [
	[
		"TAB" 	=> 0,
		"INFO" 	=> $info
	],
];



/* context menu */
$arContextMenu = [
	[
		"TEXT"  => Loc::getMessage("IPL_MA_LIST"),
		"TITLE" => Loc::getMessage("IPL_MA_LIST_TITLE"),
		"LINK"  => "iplogic_beru_product_list.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&lang=".LANG,
		"ICON"  => "btn_list",
	],
	[
		"SEPARATOR" => "Y"
	],
];
$arContextMenu[] = [
	"TEXT"  => Loc::getMessage("IPL_MA_UPDATE_CACHE"),
	"TITLE" => Loc::getMessage("IPL_MA_UPDATE_CACHE_TITLE"),
	"LINK"  => "iplogic_beru_product_detail.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&ID=".$ID."&action=update_cache&lang=".LANG,
];
if ($arFields["DETAILS"]["PRICE"]!="" && $arFields["DETAILS"]["PRICE"]>0) {
	$arContextMenu[] = [
		"TEXT"  => Loc::getMessage("IPL_MA_SEND_PRICE"),
		"TITLE" => Loc::getMessage("IPL_MA_SEND_PRICE_TITLE"),
		"LINK"  => "iplogic_beru_product_detail.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&ID=".$ID."&action=send_price&lang=".LANG,
	];
}



/* lang messages in classes */
$Messages = [
	"DELETE_CONF" => Loc::getMessage("IPL_MA_DELETE_CONF"),
];



/* prepare control object */
$adminControl->arTabs = $arTabs;
$adminControl->arOpts = $arOpts;
$adminControl->Mess = $Messages;
$adminControl->arContextMenu = $arContextMenu;
$adminControl->initDetailPage();



/* executing */
if ($adminControl->POST_RIGHT == "D") {
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
else {



	/* actions */
	if( $APPLICATION->GetGroupRight($moduleID)=="W" && $fatalErrors == "" ) {
		if( $request->get("action") == "delete" ) {
			$result = ProductTable::delete($ID);
			if ($result->isSuccess()) {
				LocalRedirect("/bitrix/admin/iplogic_beru_product_list.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&mess=ok&lang=".LANG);
			}
			else {
				$message = new CAdminMessage(Loc::getMessage("IPL_MA_ERROR_DELETE")." (".$result->getErrorMessages().")");
			}
		}
		if( $request->get("action") == "update_cache" ) {
			$result = ProductTable::updateCache($ID);
			if ($result) {
				LocalRedirect("/bitrix/admin/iplogic_beru_product_detail.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&ID=".$ID."&mess=ok&lang=".LANG);
			}
			else {
				$message = new CAdminMessage(Loc::getMessage("IPL_MA_ERROR_UPDATE")." (".$result->getErrorMessages().")");
			}
		}
		if ($request->get("action") == "hide"){
			TaskTable::hideProductTask($request->get("ID"), $PROFILE_ID);
			LocalRedirect("/bitrix/admin/iplogic_beru_product_detail.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&ID=".$ID."&mess=ok&lang=".LANG);
		}
		if ($request->get("action") == "show"){
			TaskTable::showProductTask($request->get("ID"), $PROFILE_ID);
			LocalRedirect("/bitrix/admin/iplogic_beru_product_detail.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&ID=".$ID."&mess=ok&lang=".LANG);
		}
		if ($request->get("action") == "send_price"){
			TaskTable::addPriceUpdateTask($request->get("ID"), $PROFILE_ID);
			LocalRedirect("/bitrix/admin/iplogic_beru_product_detail.php?PROFILE_ID=".$arFields["PROFILE_ID"]."&ID=".$ID."&mess=ok&lang=".LANG);
		}
	}


	/* starting output */
	$APPLICATION->SetTitle(Loc::getMessage("IPL_MA_PAGE_TITLE")." SKU: ".$arFields["SKU_ID"]);
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
	if($message)
		echo $message->Show();


	/* content */
	$adminControl->buildPage();
	echo ("<script>
		function deleteConfirm() {
			if (window.confirm('".Loc::getMessage("IPL_MA_DELETE_CONF")."')) {
				window.location.href='iplogic_beru_product_detail.php?PROFILE_ID=".$PROFILE_ID."&ID=".$ID."&action=delete&lang=".LANG."';
			}
		}
	</script>");

}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>