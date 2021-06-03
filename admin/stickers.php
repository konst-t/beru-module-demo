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

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");


$adminControl = new \Iplogic\Beru\Admin\Info($moduleID);


/* get service data and preforms*/
$info .= "<a href=\"javascript:void(0);\" class=\"check-all\">".Loc::getMessage("IPL_MA_CHECK_ALL")."</a>&nbsp;&nbsp;";
$info .= "<a href=\"javascript:void(0);\" class=\"check-actual\">".Loc::getMessage("IPL_MA_CHECK_ACTUAL")."</a>&nbsp;&nbsp;";
$info .= "<a href=\"javascript:void(0);\" class=\"uncheck-all\">".Loc::getMessage("IPL_MA_UNCHECK_ALL")."</a><br><br>";
$noboxes = true;
$a = strptime(date('d-m-Y'), '%d-%m-%Y');
$timestampmin = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
$timestampmax = mktime(23, 59, 59, $a['tm_mon']+1, $a['tm_mday']/*+1*/, $a['tm_year']+1900);
$filter = ["PROFILE_ID" => $PROFILE_ID, "STATE" => ["S_PROCESSING_STARTED","S_PROCESSING_READY_TO_SHIP"], "FAKE"=>"N"];
$rsOrders = OrderTable::getList(["filter"=>$filter, "order"=>["UNIX_TIMESTAMP"=>"DESC"]]);
while($arOrder = $rsOrders->Fetch()) {
	$actual = false;
	//if ($arOrder["SHIPMENT_TIMESTAMP"]>=$timestampmin && $arOrder["SHIPMENT_TIMESTAMP"]<=$timestampmax && $arOrder["STATE"]=="S_PROCESSING_READY_TO_SHIP") {
	if ($arOrder["SHIPMENT_TIMESTAMP"]<=$timestampmax && $arOrder["STATE"]=="S_PROCESSING_READY_TO_SHIP") {
		$actual = true;
	}
	$rsBoxes = BoxTable::getList(["filter"=>["ORDER_ID"=>$arOrder["ID"]],"order"=>["NUM"=>"ASC"]]);
	while ($arBox = $rsBoxes->Fetch()) {
		$noboxes = false;
		$box = $arOrder["EXT_ID"]."-".$arBox["NUM"];
		$info .= "<input type=\"checkbox\" id=\"bc".$box."\" name=\"box[".$arBox["ID"]."]\" class=\"box-choose".($actual ? " actual" : "")."\" checked>&nbsp;<label for=\"bc".$box."\">".($actual ? "<b>".$box."</b>" : $box)."</label>&nbsp;";
		$info .= "<a href=\"/bitrix/services/iplogic/mkpapi/sticker.php?box=".$arBox["ID"]."\" target=\"_blank\">".Loc::getMessage("IPL_MA_DOWNLOAD")."</a>";
		$info .= "<br>";
	}
}
$info .= "<br><input type=\"checkbox\" id=\"add_act\" name=\"add_act\" value=\"1\" checked>&nbsp;<label for=\"add_act\">".Loc::getMessage("IPL_MA_ADD_ACT")."</label><br>";
$info .= "<br><input type=\"submit\" name=\"generate\" value=\"".Loc::getMessage("IPL_MA_GENERATE")."\">";
if ($noboxes) {
	$info = Loc::getMessage("IPL_MA_NOBOXES");
}



/* tabs and opts */
$arTabs = [
	["DIV" => "edit1", "TAB" => Loc::getMessage("IPL_MA_BOXES"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_BOXES_TITLE")],
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

	/* actions */
	if( $APPLICATION->GetGroupRight($moduleID)=="W"
		&& $fatalErrors == ""
	) {

		if( $request->isPost() && check_bitrix_sessid() ) {
			if ($request->get("generate")!="") {
				$error = false;
				$boxesToGen = $request->get("box");
				if (count($boxesToGen)) {
					$dirName = "stickers".time();
					$stickersDir = $_SERVER['DOCUMENT_ROOT']."/".Option::get("main", "upload_dir", "upload")."/tmp/iplogic.beru/".$dirName;
					if(!mkdir($stickersDir,BX_DIR_PERMISSIONS,true)) {
						$error = Loc::getMessage("IPL_MA_CANT_CREATE_DIR");
					}
					$boxIDs = [];
					foreach($boxesToGen as $id => $foo) {
						if ($request->get("add_act") == 1) {
							$boxIDs[] = $id;
						}
						$res = file_get_contents("https://".Option::get($moduleID,"domen")."/bitrix/services/iplogic/mkpapi/sticker.php?box=".$id."&filename=".urlencode($stickersDir."/".$id.".png"));
						if($res != "OK")
							$error = Loc::getMessage("IPL_MA_FILE_CREATING_ERROR");
					}
					if ($request->get("add_act") == 1) {
						$rsBoxes = BoxTable::getList(["filter"=>["ID"=>$boxIDs]]);
						$orderIDs = [];
						while ($arBox = $rsBoxes->Fetch()) {
							if (!in_array($arBox["ORDER_ID"], $orderIDs)) {
								$orderIDs[] = $arBox["ORDER_ID"];
							}
						}
						$stIDs = implode("_",$orderIDs);
						$res = file_get_contents("https://".Option::get($moduleID,"domen")."/bitrix/services/iplogic/mkpapi/act.php?ids=".$stIDs."&profile_id=".$PROFILE_ID."&dir=".urlencode($stickersDir));
						if($res != "OK")
							$error = Loc::getMessage("IPL_MA_FILE_CREATING_ERROR");
					}
					if(!extension_loaded('zip')) {
						$error = Loc::getMessage("IPL_MA_ZIP_EXTENSION_ERROR");
					}
					else {
						$zip = new ZipArchive();
						$zip->open($stickersDir.".zip", ZIPARCHIVE::CREATE);
						$files = scandir($stickersDir);
						foreach($files as $file){
							if ($file == '.' || $file == '..' )
								continue;
							$f = $stickersDir.DIRECTORY_SEPARATOR.$file;
							$zip->addFile($f,$file);
						}
						$zip->close();
					}
					DeleteDirFilesEx("/".Option::get("main", "upload_dir", "upload")."/tmp/iplogic.beru/".$dirName);
				}
				else {
					$error = Loc::getMessage("IPL_MA_EMPTY_LIST");
				}
				if (!$error) {
					$ref = urlencode("https://".Option::get($moduleID,"domen")."/".Option::get("main", "upload_dir", "upload")."/tmp/iplogic.beru/".$dirName.".zip");
					LocalRedirect("/bitrix/admin/iplogic_beru_stickers.php?PROFILE_ID=".$PROFILE_ID."&ref=".$ref."&mess=ok&lang=".LANG."&".$adminControl->ActiveTabParam());
				}
				else {
					$message = new CAdminMessage($error);
				}
			}
		}

	}


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
				$('.box-choose').attr('checked','checked');
			});
			$('.check-actual').on('click', function(){
				$('.box-choose').removeAttr('checked');
				$('.box-choose.actual').attr('checked','checked');
			});
			$('.uncheck-all').on('click', function(){
				$('.box-choose').removeAttr('checked');
			});
		});
	</script>");

}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>