<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Localization\Loc,
	\Iplogic\Beru\ApiLogTable as ApiLog,
	\Iplogic\Beru\ProfileTable;

$POST_RIGHT = $APPLICATION->GetGroupRight($moduleID);

$checkParams = [
	"ID" => true,
	"CLASS" => "\Iplogic\Beru\ApiLogTable"
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

Loc::loadMessages(__FILE__);


$arStates = [
	"OK" => '<span style="color:#55d80e;">'.Loc::getMessage("IPL_MA_STATE_OK")."</span>",
	"RJ" => '<span style="color:red;">'.Loc::getMessage("IPL_MA_STATE_RJ")."</span>",
	"EX" => '<span style="color:#ffae00;">'.Loc::getMessage("IPL_MA_STATE_EX")."</span>",
	"DF" => '<span style="color:#1d2bec;">'.Loc::getMessage("IPL_MA_STATE_DF")."</span>",
];
$arTypes = [
	"IC" => Loc::getMessage("IPL_MA_TYPE_IC"),
	"OG" => Loc::getMessage("IPL_MA_TYPE_OG"),
];


$rsProfiles = ProfileTable::getList();
while($arProfile = $rsProfiles->Fetch()){
	$arProfiles[$arProfile["ID"]] = $arProfile["NAME"]." [".$arProfile["ID"]."]";
}


$aTabs = [
	["DIV" => "edit1", "TAB" => Loc::getMessage("IPL_MA_DETAIL"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_DETAIL_TITLE")],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$aMenu = [
	[
		"TEXT"  => Loc::getMessage("IPL_MA_LIST"),
		"TITLE" => Loc::getMessage("IPL_MA_LIST_TITLE"),
		"LINK"  => "iplogic_beru_log_list.php?lang=".LANG,
		"ICON"  => "btn_list",
	],
	[
		"SEPARATOR" => "Y"
	],
	[
		"TEXT"  => Loc::getMessage("IPL_MA_DELETE"),
		"TITLE" => Loc::getMessage("IPL_MA_DELETE_TITLE"),
		"LINK"  => "javascript:deleteConfirm();",
	]
];

if ($adminControl->POST_RIGHT == "D") {
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
else {

	if( $request->get("action") == "delete" 
		&& $APPLICATION->GetGroupRight($moduleID)=="W"
		&& $fatalErrors == ""
	) {
		$result = ApiLog::delete($ID);
		if ($result->isSuccess()) {
			LocalRedirect("/bitrix/admin/iplogic_beru_log_list.php?lang=".LANG);
		}
		else {
			$message = new CAdminMessage(Loc::getMessage("IPL_MA_ERROR_DELETE")." (".$result->getErrorMessages().")");
		}
	}

	$APPLICATION->SetTitle(Loc::getMessage("IPL_MA_LOG_DETAIL_TITLE")." #".$ID);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if ($fatalErrors != ""){
		CAdminMessage::ShowMessage($fatalErrors);
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}

	if($message)
		echo $message->Show();


	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	$tabControl->Begin();
	$tabControl->BeginNextTab();

	echo Loc::getMessage("IPL_MA_PRIFILE").": <a href=\"/bitrix/admin/iplogic_beru_profile_edit.php?ID=".
		 $arFields["PROFILE_ID"]."&lang=".LANGUAGE_ID."\">".$arProfiles[$arFields["PROFILE_ID"]]."</a><br><br>";

	echo Loc::getMessage("IPL_MA_TIME").": ".$arFields["HUMAN_TIME"]."<br><br>";

	echo Loc::getMessage("IPL_MA_STATE").": ".$arStates[$arFields["STATE"]]."<br><br>";

	echo Loc::getMessage("IPL_MA_TYPE").": ".$arTypes[$arFields["TYPE"]]."<br><br>";

	echo "URL: ".$arFields["URL"]."<br><br>";

	echo "<h3>".Loc::getMessage("IPL_MA_REQUEST")."</h3>";

	echo "TYPE: ".$arFields["REQUEST_TYPE"]."<br><br>";

	if ($arFields["REQUEST_H"]) echo "HEADERS:<br><br>".$arFields["REQUEST_H"]."<br><br>";

	echo "BODY:<br><br>".$arFields["REQUEST"]."<br><br>";


	echo "<h3>".Loc::getMessage("IPL_MA_RESPOND")."</h3>";

	if ($arFields["STATUS"]) echo Loc::getMessage("IPL_MA_STATUS").": <b>".$arFields["STATUS"]."</b><br><br>";

	if ($arFields["ERROR"]) echo Loc::getMessage("IPL_MA_ERROR").": ".$arFields["ERROR"]."<br><br>";

	if ($arFields["RESPOND_H"]) echo "HEADERS:<br><br>".$arFields["RESPOND_H"]."<br><br>";

	if ($arFields["RESPOND"]) echo "BODY:<br><br>".$arFields["RESPOND"]."<br><br>";

	$tabControl->End();

	echo ("<script>
		function deleteConfirm() {
			if (window.confirm('".Loc::getMessage("IPL_MA_DELETE_CONFIRM")."')) {
				window.location.href='iplogic_beru_log_detail.php?ID=".$ID."&action=delete&lang=".LANG."';
			}
		}
	</script>");

}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>