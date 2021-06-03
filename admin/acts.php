<?
$moduleID = 'iplogic.beru';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Iplogic\Beru\BoxTable,
	\Iplogic\Beru\OrderTable,
	\Iplogic\Beru\ProfileTable;

Loc::loadMessages(__FILE__);

CJSCore::Init(array("jquery"));

/* fatal errors check, creat control object and get table data */
$checkParams = [
	"PROFILE" => true
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

$adminControl = new \Iplogic\Beru\Admin\Info($moduleID);

/* get service data and preforms*/
$info .= "<a href=\"javascript:void(0);\" class=\"check-all\">".Loc::getMessage("IPL_MA_CHECK_ALL")."</a>&nbsp;&nbsp;";
$info .= "<a href=\"javascript:void(0);\" class=\"check-actual\">".Loc::getMessage("IPL_MA_CHECK_ACTUAL")."</a>&nbsp;&nbsp;";
$info .= "<a href=\"javascript:void(0);\" class=\"uncheck-all\">".Loc::getMessage("IPL_MA_UNCHECK_ALL")."</a><br><br>";
$noorders = true;
$a = strptime(date('d-m-Y'), '%d-%m-%Y');
$timestampmin = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
$timestampmax = mktime(23, 59, 59, $a['tm_mon']+1, $a['tm_mday']/*+1*/, $a['tm_year']+1900);
$filter = ["PROFILE_ID" => $PROFILE_ID, "STATE" => "S_PROCESSING_READY_TO_SHIP", "FAKE"=>"N"];
$rsOrders = OrderTable::getList(["filter"=>$filter, "order"=>["UNIX_TIMESTAMP"=>"DESC"]]);
while($arOrder = $rsOrders->Fetch()) { 
	$noorders = false;
	$actual = false;
	if (/*$arOrder["SHIPMENT_TIMESTAMP"]>=$timestampmin && */$arOrder["SHIPMENT_TIMESTAMP"]<=$timestampmax && $arOrder["STATE"]=="S_PROCESSING_READY_TO_SHIP") {
		$actual = true;
	}
	$order = $arOrder["EXT_ID"]." [".$arOrder["ORDER_ID"]."]";
	$info .= "<input type=\"checkbox\" data-id=\"".$arOrder["ID"]."\" id=\"bc".$arOrder["EXT_ID"]."\" name=\"order[".$arOrder["ID"]."]\" class=\"order-choose".($actual ? " actual" : "")."\" checked>&nbsp;
		<label for=\"bc".$arOrder["EXT_ID"]."\">".($actual ? "<b>".$order."</b>" : $order)."</label>&nbsp;";
	$info .= "<br>";
}
$info .= "<br><button type=\"button\" class=\"generate\">".Loc::getMessage("IPL_MA_GENERATE")."</button>";
if ($noorders) {
	$info = Loc::getMessage("IPL_MA_NOORDERS");
}



/* tabs and opts */
$arTabs = [
	["DIV" => "edit1", "TAB" => Loc::getMessage("IPL_MA_ORDERS"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_ORDERS_TITLE")],
];
$arOpts = [
	[
		"TAB" 	=> 0,
		"INFO" 	=> $info
	],
];



/* context menu */
$arContextMenu = [];


/* lang messages in classes */
$Messages = [];



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

	/* starting output */
	$APPLICATION->SetTitle(Loc::getMessage("IPL_MA_PAGE_TITLE")." #".$arProfile["ID"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


	/* fatal errors */
	if ($fatalErrors != ""){
		CAdminMessage::ShowMessage($fatalErrors);
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}


	/* action errors */
	if($message)
		echo $message->Show();


	/* ok message */
	if($request->get("mess") === "ok")
		CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage("SAVED"), "TYPE"=>"OK"));
	if($request->get("ref") != "")
		echo "<a href=\"".urldecode($request->get("ref"))."\">".urldecode($request->get("ref"))."</a><br><br><br>";


	/* content */
	$adminControl->buildPage();
	echo ("<script>
		$(document).ready(function(){
			$('.check-all').on('click', function(){
				$('.order-choose').attr('checked','checked');
			});
			$('.check-actual').on('click', function(){
				$('.order-choose').removeAttr('checked');
				$('.order-choose.actual').attr('checked','checked');
			});
			$('.uncheck-all').on('click', function(){
				$('.order-choose').removeAttr('checked');
			});
			$('.generate').on('click', function(){
				var ids = [];
				$('.order-choose').each(function(){
					if ($(this).prop('checked')) {
						ids.push($(this).attr('data-id'));
					}
				});
				if (ids.length==0) {
					alert('".Loc::getMessage("IPL_MA_EMPTY_LIST")."');
				}
				else {
					var ref = 'https://".Option::get($moduleID,"domen")."/bitrix/services/iplogic/mkpapi/act.php?ids='+ids.join('_')+'&profile_id=".$arProfile["ID"]."';
					window.open(ref, '_blank');
				}
			});
		});
	</script>");

}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>