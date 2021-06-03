<?
$module_id = "iplogic.beru";

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application,
	\Bitrix\Main\Loader;

Loader::includeModule($module_id);

$docRoot = $_SERVER['DOCUMENT_ROOT'];
$RIGHT = $APPLICATION->GetGroupRight($module_id);

IncludeModuleLangFile($docRoot.BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$request = Application::getInstance()->getContext()->getRequest();

if($RIGHT >= "R") {

	include($docRoot.BX_ROOT."/modules/".$module_id."/conf_check.php");

	$arMainOptions = [
		["domen", Loc::getMessage("IPL_DOMEN"),"", ["text", 15]],
		["keep_temp_files_days", Loc::getMessage("IPL_KEEP_TEMP_FILES_DAYS"),30, ["text", 5]],
		["menu_sort_index", Loc::getMessage("IPL_MENU_SORT_INDEX"), 10, ["text", 5]],
		Loc::getMessage("IPL_LOGGING"),
		["use_log", Loc::getMessage("IPL_USE_LOG"), "Y", ["checkbox"]],
		["dont_log_ok", Loc::getMessage("IPL_DONT_LOG_OK"), "N", ["checkbox"]],
		["log_in_menu", Loc::getMessage("IPL_LOG_IN_MENU"), "Y", ["checkbox"]],
		["keep_log_days", Loc::getMessage("IPL_KEEP_LOG_DAYS"), 0, ["text", 5]],
		Loc::getMessage("IPL_TASKS"),
		["task_trying_num", Loc::getMessage("IPL_TASK_TRYING_NUM"),"3", ["text", 5]],
		["task_trying_period", Loc::getMessage("IPL_TASK_TRYING_PERIOD"),"60", ["text", 5]],
		["allow_multichain_tasks", Loc::getMessage("IPL_ALLOW_MULTICHAIN_TASKS"), "N", ["checkbox"]],
		Loc::getMessage("IPL_PRODUCTS"),
		["products_check_period", Loc::getMessage("IPL_PRODUCTS_CHECK_PERIOD"),"1", ["text", 5]],
		["products_add_num", Loc::getMessage("IPL_PRODUCTS_ADD_NUM"),50, ["text", 5]],
		["products_check_disable", Loc::getMessage("IPL_PRODUCTS_CHECK_DISABLE"), "N", ["checkbox"]],
	];

	$strConfigCheck = '
		<h3>'.Loc::getMessage("IPL_SERVER").'</h3>
		'.Loc::getMessage("IPL_SSL").': '.
			($arConfigCheck["SSL"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br><br>
		'.Loc::getMessage("IPL_PERM").':<br>
		/bitrix/modules/ - '.
			($arConfigCheck["PERM"]["modules"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		/bitrix/admin/ - '.
			($arConfigCheck["PERM"]["admin"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		/bitrix/css/ - '.
			($arConfigCheck["PERM"]["css"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		/bitrix/images/ - '.
			($arConfigCheck["PERM"]["images"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		/bitrix/services/ - '.
			($arConfigCheck["PERM"]["services"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		/urlrewrite.php - '.
			($arConfigCheck["PERM"]["urlrewrite"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br><br><br>
		<h3>'.Loc::getMessage("IPL_PHP").'</h3>
		'.Loc::getMessage("IPL_PHP_VERSION").': '.
			($arConfigCheck["PHP"]["VALID"] ? '<span style="color:#05d700;">'.$arConfigCheck["PHP"]["VERSION"].'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.$arConfigCheck["PHP"]["VERSION"].'</span>').
		'<br><br>
		'.Loc::getMessage("IPL_EXEC").': '.
			($arConfigCheck["PHP"]["exec"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br><br>
		'.Loc::getMessage("IPL_ZIP").': '.
			($arConfigCheck["PHP"]["zip"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br><br>
		'.Loc::getMessage("IPL_GD").': '.
			($arConfigCheck["PHP"]["gd"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br><br><br>
		<h3>'.Loc::getMessage("IPL_BITRIX").'</h3>
		'.Loc::getMessage("IPL_MAIN_VER").': '.$arConfigCheck["MOD_VER"]["main"].
		'<br><br>
		'.Loc::getMessage("IPL_IBLOCK").': '.
			($arConfigCheck["MOD"]["iblock"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		'.Loc::getMessage("IPL_IBLOCK_VER").': '.$arConfigCheck["MOD_VER"]["iblock"].
		'<br><br>
		'.Loc::getMessage("IPL_CATALOG").': '.
			($arConfigCheck["MOD"]["catalog"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		'.Loc::getMessage("IPL_IBLOCK_VER").': '.$arConfigCheck["MOD_VER"]["catalog"].
		'<br><br>
		'.Loc::getMessage("IPL_SALE").': '.
			($arConfigCheck["MOD"]["sale"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').
		'<br>
		'.Loc::getMessage("IPL_IBLOCK_VER").': '.$arConfigCheck["MOD_VER"]["sale"].
		'<br><br>
		'./*Loc::getMessage("IPL_CRON").': '.
			($arConfigCheck["CRON"] ? '<span style="color:#05d700;">'.Loc::getMessage("IPL_YES").'</span>' : '<span style="color:#ff0000;font-weight:bold;">'.Loc::getMessage("IPL_NO").'</span>').*/
		'<br><br>
	';

	$aTabs = [
		["DIV" => "edit1", "TAB" => Loc::getMessage("MAIN_TAB_SET"), "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"), "OPTIONS" => $arMainOptions],
		["DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_REQ"), "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_REQ"), "CONTENT" => $strConfigCheck],
		["DIV" => "edit10", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")],
	];
	$tabControl = new CAdminTabControl("tabControl", $aTabs);

	if($request->isPost() && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT=="W" && check_bitrix_sessid())
	{
		require_once($docRoot."/bitrix/modules/perfmon/prolog.php");
		if(strlen($RestoreDefaults)>0) {
			Option::delete($module_id);
			Option::getDefaults($module_id);
		}
		else
		{
			foreach($aTabs as $aTab){
				if($aTab["OPTIONS"]){
					foreach($aTab["OPTIONS"] as $arOption) {
						__AdmSettingsSaveOption($module_id, $arOption);
					}
				}
			}
		}
		ob_start();
		$Update = $Update.$Apply;
		require_once($docRoot . '/bitrix/modules/main/admin/group_rights.php');
		ob_end_clean();

		if ($request->get("back_url_settings") != "")
		{
			if( $request->get("Apply") != "" || $request->get("RestoreDefaults") != "" )
				LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($request->get("back_url_settings"))."&".$tabControl->ActiveTabParam());
			else
				LocalRedirect($request->get("back_url_settings"));
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
		}
	}

	?>
	<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
		<?
		$tabControl->Begin();
		$arNotes = array();
		foreach($aTabs as $aTab){
			if($aTab["OPTIONS"]){
				$tabControl->BeginNextTab();
				__AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
			}
		}
		$tabControl->BeginNextTab();
		require_once($docRoot."/bitrix/modules/main/admin/group_rights.php");
		$tabControl->Buttons();
		?>
		<input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=Loc::getMessage("MAIN_SAVE")?>" title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
		<?if(strlen($request->get("back_url_settings"))>0):?>
			<input <?if ($RIGHT<"W") echo "disabled" ?> type="button" name="Cancel" value="<?=Loc::getMessage("MAIN_OPT_CANCEL")?>" title="<?=Loc::getMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($request->get("back_url_settings")))?>'">
			<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($request->get("back_url_settings"))?>">
		<?endif?>
		<input type="submit" name="RestoreDefaults" title="<?echo Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo Loc::getMessage("MAIN_RESTORE_DEFAULTS")?>">
		<?=bitrix_sessid_post();?>
		<?$tabControl->End();?>
	</form>
	 <?
		CJSCore::Init(array("jquery"));
	 ?>
	 <script>
		$(document).ready(function(){

		});
	</script>
	<?
	if(!empty($arNotes))
	{
		echo BeginNote();
		foreach($arNotes as $i => $str)
		{
			?><span class="required"><sup><?echo $i+1?></sup></span><?echo $str?><br><?
		}
		echo EndNote();
	}
}
else {
	echo Loc::getMessage("MODULE_OPTIONS_DENIED");
}
?>
