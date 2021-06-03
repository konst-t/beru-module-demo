<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Application,
	\Iplogic\Beru\ConditionTable,
	\Iplogic\Beru\ProfileTable,
	\Iplogic\Beru\TaskTable;

Loc::loadMessages(__FILE__);

$checkParams = [
	"PROFILE" => true,
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

class TableFormEx extends Iplogic\Beru\Admin\TableForm {
	protected function addButtons() {
		global $PROFILE_ID;
		$this->tabControl->Buttons(["disabled"=>($this->POST_RIGHT<"W"), "back_url"=>"/bitrix/admin/iplogic_beru_condition_list.php?PROFILE_ID=".$PROFILE_ID."&lang=".LANG]);
	}
}

$TYPE = $request->get("type");
$arTypes = ["IY","DV"];

if ($ID<1 && !in_array($TYPE, $arTypes)) {
	$fatalErrors = Loc::getMessage("WRONG_PARAMETERS")."<br>";
}
if ($ID>0) {
	if (!$arFields = ConditionTable::getById($ID)) {
		$fatalErrors = Loc::getMessage("WRONG_PARAMETERS")."<br>";
	}
	$TYPE = $arFields["TYPE"];
}

CJSCore::Init(array("jquery"));

$LID = Iplogic\Beru\Admin\TableForm::getLID();

$IBlockFields = [
	"ID" 				=> Loc::getMessage("IBLOCK_FIELDS_ID"),
	"CODE" 				=> Loc::getMessage("IBLOCK_FIELDS_CODE"),
	"XML_ID" 			=> Loc::getMessage("IBLOCK_FIELDS_XML_ID"),
	"NAME" 				=> Loc::getMessage("IBLOCK_FIELDS_NAME"),
	"SORT" 				=> Loc::getMessage("IBLOCK_FIELDS_SORT"),
	"ACTIVE" 			=> Loc::getMessage("IBLOCK_FIELDS_ACTIVE"),
	"PREVIEW_PICTURE" 	=> Loc::getMessage("IBLOCK_FIELDS_PREVIEW_PICTURE"),
	"PREVIEW_TEXT" 		=> Loc::getMessage("IBLOCK_FIELDS_PREVIEW_TEXT"),
	"DETAIL_PICTURE" 	=> Loc::getMessage("IBLOCK_FIELDS_DETAIL_PICTURE"),
	"DETAIL_TEXT" 		=> Loc::getMessage("IBLOCK_FIELDS_DETAIL_TEXT"),
	"DETAIL_PAGE_URL" 	=> Loc::getMessage("IBLOCK_FIELDS_DETAIL_PAGE_URL"),
	"SECTION_ID" 		=> Loc::getMessage("IBLOCK_FIELDS_SECTION_ID"),
	"SECTION_ACTIVE" 	=> Loc::getMessage("IBLOCK_FIELDS_SECTION_ACTIVE"),
	"CATALOG_TYPE" 		=> Loc::getMessage("IBLOCK_FIELDS_CATALOG_TYPE"),
];
$ProductFields = [
	"QUANTITY" 			=> Loc::getMessage("PRODUCT_FIELDS_QUANTITY"),
	"AVAILABLE" 		=> Loc::getMessage("PRODUCT_FIELDS_AVAILABLE"),
	"WEIGHT" 			=> Loc::getMessage("PRODUCT_FIELDS_WEIGHT"),
	"WIDTH" 			=> Loc::getMessage("PRODUCT_FIELDS_WIDTH"),
	"LENGTH" 			=> Loc::getMessage("PRODUCT_FIELDS_LENGTH"),
	"HEIGHT" 			=> Loc::getMessage("PRODUCT_FIELDS_HEIGHT"),
];

$arConditions = [
	"EQUAL" 			=> Loc::getMessage("IPL_MA_EQUAL"),
	"INCLUDE" 			=> Loc::getMessage("IPL_MA_INCLUDE"),
	"IN_ARRAY" 			=> Loc::getMessage("IPL_MA_IN_ARRAY"),
	"MORE" 				=> Loc::getMessage("IPL_MA_MORE"),
	"LESS" 				=> Loc::getMessage("IPL_MA_LESS"),
	"NOT_EMPTY" 		=> Loc::getMessage("IPL_MA_NOT_EMPTY"),
	"EMPTY" 			=> Loc::getMessage("IPL_MA_EMPTY"),
];
$arCondTypes = [
	"IY" => Loc::getMessage("IPL_MA_IY"),
	"DV" => Loc::getMessage("IPL_MA_DV"),
];

$aTabs = [
	["DIV" => "edit1", "TAB" => Loc::getMessage("IPL_MA_GENERAL"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_GENERAL_TITLE")],
];
$arOpts = [
	/* GENERAL */
	"TYPE" => [
		"TAB"       => "edit1",
		"TYPE"      => "hidden",
		"DEFAULT"   => $TYPE,
	],
	"PROFILE_ID" => [
		"TAB"       => "edit1",
		"TYPE"      => "hidden",
		"DEFAULT"   => $PROFILE_ID,
	],
	"ACTIVE" => [
		"TAB"       => "edit1", 
		"TYPE"      => "checkbox", 
		"DEFAULT"   => 'Y',
		"NAME"      => Loc::getMessage("IPL_MA_ACTIVE"),
	],
	"SORT" => [
		"TAB"       => "edit1", 
		"TYPE"      => "text", 
		"DEFAULT"   => 100,
		"NAME"      => Loc::getMessage("IPL_MA_SORT"),
	],
	"PROP" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_IF"),
		"TYPES" 	=> [
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"product_fields" 	=> Loc::getMessage("IPL_MA_PRODUCT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PRODUCT_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
			"prices" 			=> Loc::getMessage("IPL_MA_PRICES"),
			"stores" 			=> Loc::getMessage("IPL_MA_STORES"),
		]
	],
	"COND" => [
		"TAB"       => "edit1", 
		"TYPE"      => "select", 
		"DEFAULT"   => "EQUAL",
		"NAME"      => "",
		"OPTIONS"   => $arConditions,
	],
	"VALUE" => [
		"TAB"       => "edit1", 
		"TYPE"      => "textarea", 
		"DEFAULT"   => "",
		"NAME"      => "",
		"CLASS"     => "value-text-field",
	],
];

if($TYPE == "IY") {
	$arOpts["ACTION"] = [
		"TAB"       => "edit1", 
		"TYPE"      => "select", 
		"DEFAULT"   => "INCLUDE_IN_YML",
		"NAME"      => Loc::getMessage("IPL_MA_THEN"),
		"OPTIONS"   => [
			"INCLUDE_IN_YML" 	=> Loc::getMessage("IPL_MA_INCLUDE_IN_YML"),
			"EXCLUDE_IN_YML" 	=> Loc::getMessage("IPL_MA_EXCLUDE_IN_YML"),
		],
	];
}

$arProdIBlock = \CCatalogSKU::GetInfoByProductIBlock($arProfile["IBLOCK_ID"]);
if (is_array($arProdIBlock)) {
	$offerIBlockID = $arProdIBlock["IBLOCK_ID"];
	if($offerIBlockID<1){
		foreach($arOpts as $key=>$opt){
			unset($arOpts[$key]["TYPES"]["common_props"]);
			unset($arOpts[$key]["TYPES"]["offer_props"]);
		}
	}
}

$aMenu = [
	[
		"TEXT"  => Loc::getMessage("IPL_MA_LIST"),
		"TITLE" => Loc::getMessage("IPL_MA_LIST_TITLE"),
		"LINK"  => "iplogic_beru_condition_list.php?PROFILE_ID=".$PROFILE_ID."&lang=".LANG,
		"ICON"  => "btn_list",
	]
];


$adminControl = new TableFormEx($moduleID);
$adminControl->arTabs = $aTabs;
$adminControl->arOpts = $arOpts;
$adminControl->Mess = $Messages;
$adminControl->arElementFields = $IBlockFields;
$adminControl->arProductFields = $ProductFields;
$adminControl->arContextMenu = $aMenu;
$adminControl->captionWidth = 30;
$adminControl->catalogIBlockID = $arProfile["IBLOCK_ID"];
$adminControl->initDetailPage();

if ($adminControl->POST_RIGHT == "D") {
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
else {

	$adminControl->setFields($arFields);

	if( $request->isPost() 
		&& ($request->get("save")!="" || $request->get("apply")!="") 
		&& $APPLICATION->GetGroupRight($moduleID)=="W" 
		&& check_bitrix_sessid() 
		&& $fatalErrors == ""
	) {

		$adminControl->getRequestData();

		if( !count($adminControl->errors) ) {
			$arFields = $adminControl->extractQueryValues(); 
			$arFields["PROP_TYPE"] = $request->get("PROP_TYPE"); 


			$arProps = $adminControl->getStandartArray($arFields["PROP_TYPE"]);
			$arFields["DESCRIPTION"] =  Loc::getMessage("IPL_MA_IF")." ".
										strtolower(Loc::getMessage("IPL_MA_".strtoupper($arFields["PROP_TYPE"])))." ";
			if ($arFields["PROP"]!="") {
				$arFields["DESCRIPTION"] .= "\"".$arProps[$arFields["PROP"]]."\" ";
			}
			$arFields["DESCRIPTION"] .= strtolower($arConditions[$arFields["COND"]]). " ";
			if ($arFields["COND"]!="NOT_EMPTY" && $arFields["COND"]!="EMPTY") {
				$arFields["DESCRIPTION"] .= "\"".$arFields["VALUE"]."\" ";
			}
			$arFields["DESCRIPTION"] .= Loc::getMessage("IPL_MA_THEN")." ".
										strtolower(Loc::getMessage("IPL_MA_".strtoupper($arFields["ACTION"])))." ";

			if($ID>0) {
				$res = ConditionTable::update($ID,$arFields);
				if (!$res->isSuccess()) {
					$adminControl->errors[] = implode("<br>",$res->getErrorMessages());
					$res = false;
				}
			}
			else {
				$res = ConditionTable::add($arFields);
				$ID = $res->getId();
				if (!$res->isSuccess()) {
					$adminControl->errors[] = implode("<br>",$res->getErrorMessages());
					$res = false;
				}
			}
			if($res) {
				TaskTable::scheduleFeedProductsRefresh($PROFILE_ID);
				if ($request->get("apply") != "")
					LocalRedirect("/bitrix/admin/iplogic_beru_condition_edit.php?PROFILE_ID=".$PROFILE_ID."&ID=".$ID."&mess=ok&lang=".LANG."&".$adminControl->ActiveTabParam());
				else
					LocalRedirect("/bitrix/admin/iplogic_beru_condition_list.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG);
			}
		}

	}

	$APPLICATION->SetTitle(($ID>0 ? Loc::getMessage("IPL_MA_CONDITION_EDIT_TITLE") : Loc::getMessage("IPL_MA_CONDITION_NEW_TITLE")).$PROFILE_ID." (".$arProfile["NAME"].")");

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	if ($fatalErrors != ""){
		CAdminMessage::ShowMessage($fatalErrors);
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}

	if($request->get("mess") === "ok")
		CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage("SAVED"), "TYPE"=>"OK"));

	elseif( count($adminControl->errors) ) {
		foreach($adminControl->errors as $error) {
			CAdminMessage::ShowMessage($error);
		}
	}

	$adminControl->buildPage();

	$adminControl->getPropChooseScript();
?><script>
	$(document).ready(function(){
		$("select[name='COND']").on("change", function(){
			$('.value-text-field').show();
			if ($(this).val()=="EMPTY" || $(this).val()=="NOT_EMPTY") {
				$('.value-text-field').hide();
			}
		});
		$("select[name='COND']").change();
	});
</script><?
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>