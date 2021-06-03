<?

$moduleID = 'iplogic.beru';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Application,
	\Iplogic\Beru\ProfileTable;

$checkParams = [
	"PROFILE" => true
];

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/prolog.php");

Loc::loadMessages(__FILE__);

class TableFormEx extends Iplogic\Beru\Admin\TableForm
{

	protected function __ShowPropChoose($prop_name, $arProps){ 
		$type_options = "";
		$prop_options = "";
		foreach($arProps["TYPES"] as $val => $name){
			if($val == "custom_set") {
				foreach($name as $key => $caption) {
					$selected = false;
					if ($this->arOpts[$prop_name]["VALUE_TYPE"] == $key) $selected = true;
					$type_options .= "<option value=\"".$key."\"".($selected ? ' selected' : '').">".$caption."</option>";
				}
				continue;
			}
			$selected = false;
			if ($this->arOpts[$prop_name]["VALUE_TYPE"] == $val) $selected = true;
			$type_options .= "<option value=\"".$val."\"".($selected ? ' selected' : '').">".$name."</option>";
			$standartArray = $this->getStandartArray($val);
			if (is_array($standartArray)) {
				if (count($standartArray)) {
					foreach($standartArray as $key => $capt) {
						$selected = false;
						if ($this->arOpts[$prop_name]["VALUE_TYPE"] == $val && $arProps["VALUE"] == $key) $selected = true;
						$prop_options .= "<option value=\"".$key."\"".($selected ? ' selected' : '')." data-type=\"".$val."\">".$capt."</option>";
					}
				}
			}
		}
		if ($arProps["ID"] < 1) {
			$arProps["ID"] = "NEW_".randString(8);
		}
		$result .= "<select name=\"prop[".$arProps["ID"]."][TYPE]\" data-select-type=\"choose-prop-type\">".$type_options."</select>&nbsp;&nbsp;";
		$result .= "<select name=\"prop[".$arProps["ID"]."][VALUE]\">".$prop_options."</select>";
		$result .= "<input type=\"text\" name=\"prop[".$arProps["ID"]."][TEXT_VALUE]\" value=\"".$arProps["VALUE"]."\" style=\"display:none;\">";
		$result .= "<input type=\"hidden\" name=\"prop[".$arProps["ID"]."][NAME]\" value=\"".$prop_name."\">";
		return $result;
	}


	public function getRequestData() {
		global $request;
		foreach($request->get("prop") as $prop) {
			$this->arOpts[$prop["NAME"]]["VALUE"] = $prop["VALUE"];
			$this->arOpts[$prop["NAME"]]["VALUE_TYPE"] = $prop["TYPE"];
		}
	}


	protected function addButtons() {
		global $PROFILE_ID;
		$this->tabControl->Buttons(["disabled"=>($this->POST_RIGHT<"W"), "back_url"=>"/bitrix/admin/iplogic_beru_profile_list.php?&lang=".LANG]);
	}

}



CJSCore::Init(array("jquery"));

$LID = Iplogic\Beru\Admin\TableForm::getLID();

$IBlockFields = [
	"ID" 				=> Loc::getMessage("IBLOCK_FIELDS_ID"),
	"CODE" 				=> Loc::getMessage("IBLOCK_FIELDS_CODE"),
	"XML_ID" 			=> Loc::getMessage("IBLOCK_FIELDS_XML_ID"),
	"NAME" 				=> Loc::getMessage("IBLOCK_FIELDS_NAME"),
	"SORT" 				=> Loc::getMessage("IBLOCK_FIELDS_SORT"),
	"PREVIEW_PICTURE" 	=> Loc::getMessage("IBLOCK_FIELDS_PREVIEW_PICTURE"),
	"PREVIEW_TEXT" 		=> Loc::getMessage("IBLOCK_FIELDS_PREVIEW_TEXT"),
	"DETAIL_PICTURE" 	=> Loc::getMessage("IBLOCK_FIELDS_DETAIL_PICTURE"),
	"DETAIL_TEXT" 		=> Loc::getMessage("IBLOCK_FIELDS_DETAIL_TEXT"),
	"DETAIL_PAGE_URL" 	=> Loc::getMessage("IBLOCK_FIELDS_DETAIL_PAGE_URL"),
];
$ProductFields = [
	"QUANTITY" 			=> Loc::getMessage("PRODUCT_FIELDS_QUANTITY"),
	"AVAILABLE" 		=> Loc::getMessage("PRODUCT_FIELDS_AVAILABLE"),
	"WEIGHT" 			=> Loc::getMessage("PRODUCT_FIELDS_WEIGHT"),
	"WIDTH" 			=> Loc::getMessage("PRODUCT_FIELDS_WIDTH"),
	"LENGTH" 			=> Loc::getMessage("PRODUCT_FIELDS_LENGTH"),
	"HEIGHT" 			=> Loc::getMessage("PRODUCT_FIELDS_HEIGHT"),
];

$aTabs = [
	["DIV" => "edit1", "TAB" => Loc::getMessage("IPL_MA_GENERAL"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_GENERAL_TITLE")],
	["DIV" => "edit2", "TAB" => Loc::getMessage("IPL_MA_API"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_API_TITLE")],
	["DIV" => "edit3", "TAB" => Loc::getMessage("IPL_MA_FEED"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage("IPL_MA_FEED_TITLE")],
];
$arOpts = [


	/* GENERAL */
	"SHOP_SKU_ID" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_SHOP_SKU_ID"),
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		],
	],

	"MARKET_SKU_ID" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_MARKET_SKU_ID"),
		"TYPES" 	=> [
			"empty" 			=> Loc::getMessage("IPL_MA_NOSHOW"),
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],


	"PRICE" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_PRICE"),
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"custom_set" 		=> [
				"min_discount_price" 	=> Loc::getMessage("IPL_MA_MIN_DISCOUNT_PRICE"),
			],
			"prices" 			=> Loc::getMessage("IPL_MA_PRICES"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],

	"STOCK_FIT" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_STOCK_FIT"),
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"product_fields" 	=> Loc::getMessage("IPL_MA_PRODUCT_FIELDS"),
			"stores" 			=> Loc::getMessage("IPL_MA_STORES"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],


	"WEIGHT" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_WEIGHT"),
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"custom_set" 		=> [
				"from_product" 	=> Loc::getMessage("IPL_MA_FROM_PRODUCT"),
			],
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		],
	],

	"DIMENSIONS" => [
		"TAB"       => "edit1",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_DIMENSIONS"),
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"custom_set" 		=> [
				"from_product" 	=> Loc::getMessage("IPL_MA_FROM_PRODUCT"),
			],
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		],
	],


	/* API */

	"CHANGE_TIME" => [
		"TAB"       => "edit2",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => Loc::getMessage("IPL_MA_CHANGE_TIME"),
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"custom_set" 		=> [
				"current_time" 			=> Loc::getMessage("IPL_MA_CURRENT_TIME"),
				"element_last_change" 	=> Loc::getMessage("IPL_MA_ELEMENT_LAST_CHANGE"),
			],
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],



	/* YML */

	"NAME" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;name&gt;</span>",
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],

	"VENDOR" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;vendor&gt;</span>",
		"TYPES" 	=> [
			"empty" 			=> Loc::getMessage("IPL_MA_NOSHOW"),
			"custom_set" 		=> [
				"permanent_text" 	=> Loc::getMessage("IPL_MA_PERMANENT_TEXT"),
			],
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],

	"OLD_PRICE" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;oldprice&gt;</span>",
		"TYPES" 	=> [
			"empty" 			=> Loc::getMessage("IPL_MA_NOSHOW"),
			"custom_set" 		=> [
				"min_discount_price" 	=> Loc::getMessage("IPL_MA_MIN_DISCOUNT_PRICE"),
			],
			"prices" 			=> Loc::getMessage("IPL_MA_PRICES"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		]
	],

	"IMG" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;picture&gt;</span>",
		"REQURIED"  => "Y",
		"TYPES" 	=> [
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		],
	],

	"DESCRIPTION" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;description&gt;</span>",
		"TYPES" 	=> [
			"empty" 			=> Loc::getMessage("IPL_MA_NOSHOW"),
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		],
	],

	"BARCODE" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;barcode&gt;</span>",
		"TYPES" 	=> [
			"empty" 			=> Loc::getMessage("IPL_MA_NOSHOW"),
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
		],
	],

	"DISABLED" => [
		"TAB"       => "edit3",
		"TYPE"      => "prop_choose", 
		"DEFAULT"   => "",
		"NAME"      => "<span style=\"font-size:16px;\">&lt;disabled&gt;</span>",
		"TYPES" 	=> [
			"empty" 			=> Loc::getMessage("IPL_MA_NOSHOW"),
			"element_fields" 	=> Loc::getMessage("IPL_MA_ELEMENT_FIELDS"),
			"common_props" 		=> Loc::getMessage("IPL_MA_COMMON_PROPS"),
			"product_props" 	=> Loc::getMessage("IPL_MA_PROD_PROPS"),
			"offer_props" 		=> Loc::getMessage("IPL_MA_OFFER_PROPS"),
			"prices" 			=> Loc::getMessage("IPL_MA_PRICES"),
			"stores" 			=> Loc::getMessage("IPL_MA_STORES"),
		],
	],




];


if ($arProfile["MARKETPLACE"] != "B") {
	unset($arOpts["GET_TOKEN"]);
	unset($arOpts["BASE_URL"]);
}
if ($arProfile["USE_API"]!="Y")
	unset($aTabs[1]);
if ($arProfile["USE_FEED"]!="Y")
	unset($aTabs[2]);

$aTabs = array_values($aTabs);


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
		"LINK"  => "iplogic_beru_profile_list.php?lang=".LANG,
		"ICON"  => "btn_list",
	],
	["SEPARATOR"=>"Y"],
	[
		"TEXT"  => Loc::getMessage("IPL_MA_PROFILE_SETTINGS"),
		"TITLE" => Loc::getMessage("IPL_MA_PROFILE_SETTINGS_TITLE"),
		"LINK"  => "iplogic_beru_profile_edit.php?ID=".$arProfile["ID"]."&lang=".LANG,
	]
];
if ($arProfile["USE_FEED"] == "Y") {
	$aMenu[] = [
		"TEXT"  => Loc::getMessage("IPL_MA_CONDITIONS"),
		"TITLE" => Loc::getMessage("IPL_MA_CONDITIONS"),
		"LINK"  => "iplogic_beru_condition_list.php?PROFILE_ID=".$arProfile["ID"]."&lang=".LANG,
	];
}


$Messages = [
	"NOT_CHOSEN" => Loc::getMessage("NOT_CHOSEN"),
	"ALL" => Loc::getMessage("ALL"),
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

	foreach ($arProfile["PROP"] as $arProp) {
		$adminControl->arOpts[$arProp["NAME"]]["VALUE"] = $arProp["VALUE"];
		$adminControl->arOpts[$arProp["NAME"]]["VALUE_TYPE"] = $arProp["TYPE"];
		$adminControl->arOpts[$arProp["NAME"]]["ID"] = $arProp["ID"];
	}

	if( $request->isPost() 
		&& ($request->get("save")!="" || $request->get("apply")!="") 
		&& $APPLICATION->GetGroupRight($moduleID)=="W" 
		&& check_bitrix_sessid() 
		&& $fatalErrors == ""
	) {

		$adminControl->getRequestData();

		if( !count($adminControl->errors) ) {
			$res = true;
			foreach($request->get("prop") as $id => $arFields) {
				$arFields["ID"] = $id;
				$_res = ProfileTable::setAccordance($PROFILE_ID, $arFields);
				if(!$_res) $res = false;
			}
			if($res) {
				if ($request->get("apply") != "")
					LocalRedirect("/bitrix/admin/iplogic_beru_accordances_edit.php?PROFILE_ID=".$PROFILE_ID."&mess=ok&lang=".LANG."&".$adminControl->ActiveTabParam());
				else
					LocalRedirect("/bitrix/admin/iplogic_beru_profile_list.php?lang=".LANG);
			}
			else {
				$adminControl->errors = array_merge($adminControl->errors, $obProfile->errors);
			}
		}

	}

	$APPLICATION->SetTitle(Loc::getMessage("IPL_MA_PROFILE_EDIT_TITLE")." ".$arProfile["NAME"]." #".$PROFILE_ID." (".$arProfile["NAME"].")");

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
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>