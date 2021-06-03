<?
/** @global CMain$APPLICATION */
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	\Bitrix\Main\Config\Option,
	Iplogic\Beru\ProfileTable;

$moduleID = 'iplogic.beru';

$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/menu.css");

if ($USER->IsAdmin())
{
	Loc::loadMessages(__FILE__);

	Loader::includeModule($moduleID);

	$a = explode("/",$_SERVER['PHP_SELF']);
	$strCurrentPage = $a[count($a)-1];

	$bHiddenProfile = false;
	if($_REQUEST["PROFILE_ID"]>0) {
		$bHiddenProfile = true;
		$H_PROFILE_ID = $_REQUEST["PROFILE_ID"];
	}
	if($strCurrentPage == "iplogic_beru_profile_edit.php" && $_REQUEST["ID"]>0) {
		$bHiddenProfile = true;
		$H_PROFILE_ID = $_REQUEST["ID"];
	}

	$arProfiles = [];
	$profiles = ProfileTable::getList(['order' => ["SORT"=>"desc"],'filter' => ["ACTIVE"=>"Y"]]);
	while($arProfile=$profiles->Fetch()) {
		$arProfiles[] = $arProfile;
		if ($arProfile["ID"] == $_REQUEST["PROFILE_ID"] || $arProfile["ID"] == $_REQUEST["ID"]) {
			$bHiddenProfile = false;
		}
	}

	$arMoreUrl = ["iplogic_beru_profile_edit.php?mode=new"];
	if ($bHiddenProfile) {
		$arMoreUrl[] = "iplogic_beru_profile_edit.php?ID=".$H_PROFILE_ID;
		$arMoreUrl[] = "iplogic_beru_accordances_edit.php?PROFILE_ID=".$H_PROFILE_ID;
		$arMoreUrl[] = "iplogic_beru_condition_list.php?PROFILE_ID=".$H_PROFILE_ID;
		$arMoreUrl[] = "iplogic_beru_condition_edit.php?PROFILE_ID=".$H_PROFILE_ID;
	}

	$arConMenu = [
		[
			"text" => Loc::getMessage("IPL_MA_MENU_PROFILE"),
			"title" => Loc::getMessage("IPL_MA_MENU_PROFILE"),
			"url" => "iplogic_beru_profile_list.php?lang=".LANGUAGE_ID,
			"more_url" => $arMoreUrl
		],
	];

	foreach($arProfiles as $arProfile) {
		$arInnerMenu = [];
		if ($arProfile["USE_API"] == "Y") {
			$arInnerMenu[] = [
				"text" => Loc::getMessage("IPL_MA_MENU_ORDERS"),
				"title" => Loc::getMessage("IPL_MA_MENU_ORDERS"),
				"url" => "iplogic_beru_order_list.php?PROFILE_ID=".$arProfile["ID"]."&lang=".LANGUAGE_ID,
				"more_url" => [
					"iplogic_beru_order_list.php?PROFILE_ID=".$arProfile["ID"],
					"iplogic_beru_order_detail.php?PROFILE_ID=".$arProfile["ID"],
				]
			];
			$arInnerMenu[] = [
				"text" => Loc::getMessage("IPL_MA_MENU_PRODUCTS"),
				"title" => Loc::getMessage("IPL_MA_MENU_PRODUCTS"),
				"url" => "iplogic_beru_product_list.php?PROFILE_ID=".$arProfile["ID"]."&lang=".LANGUAGE_ID,
				"more_url" => [
					"iplogic_beru_product_list.php?PROFILE_ID=".$arProfile["ID"],
					"iplogic_beru_product_detail.php?PROFILE_ID=".$arProfile["ID"],
				]
			];
			$arInnerMenu[] = [
				"text" => Loc::getMessage("IPL_MA_MENU_STICKERS"),
				"title" => Loc::getMessage("IPL_MA_MENU_STICKERS"),
				"url" => "iplogic_beru_stickers.php?PROFILE_ID=".$arProfile["ID"]."&lang=".LANGUAGE_ID,
				"more_url" => [
					"iplogic_beru_stickers.php?PROFILE_ID=".$arProfile["ID"],
				]
			];
			$arInnerMenu[] = [
				"text" => Loc::getMessage("IPL_MA_MENU_ACTS"),
				"title" => Loc::getMessage("IPL_MA_MENU_ACTS"),
				"url" => "iplogic_beru_acts.php?PROFILE_ID=".$arProfile["ID"]."&lang=".LANGUAGE_ID,
				"more_url" => [
					"iplogic_beru_acts.php?PROFILE_ID=".$arProfile["ID"],
				]
			];
		}
		$arConMenu[] = [
			"text" => $arProfile["NAME"],
			"title" => $arProfile["NAME"],
			"url" => "iplogic_beru_profile_edit.php?ID=".$arProfile["ID"]."&lang=".LANGUAGE_ID,
			"items_id" => "menu_beru_".$arProfile["ID"],
			"items" => $arInnerMenu,
			"more_url" => [
				"iplogic_beru_profile_edit.php?ID=".$arProfile["ID"],
				"iplogic_beru_accordances_edit.php?PROFILE_ID=".$arProfile["ID"],
				"iplogic_beru_condition_list.php?PROFILE_ID=".$arProfile["ID"],
				"iplogic_beru_condition_edit.php?PROFILE_ID=".$arProfile["ID"],
			]
		];
	}

	$newErrMark = "";
	$newErr = \Iplogic\Beru\ErrorTable::newCount();
	if ($newErr>0) {
		$newErrMark = " (".$newErr.")";
	}

	$arConMenu[] = [
		"text" => Loc::getMessage("IPL_MA_MENU_ERRORS").$newErrMark,
		"title" => Loc::getMessage("IPL_MA_MENU_ERRORS"),
		"url" => "iplogic_beru_error_list.php?lang=".LANGUAGE_ID,
		"more_url" => [
			"iplogic_beru_error_list.php",
			"iplogic_beru_error_detail.php",
		]
	];

	if ( Option::get($moduleID, 'use_log', 'Y') == "Y" && Option::get($moduleID, 'log_in_menu', 'Y') == "Y" ) {
		$arConMenu[] = [
			"text" => Loc::getMessage("IPL_MA_MENU_LOG"),
			"title" => Loc::getMessage("IPL_MA_MENU_LOG"),
			"url" => "iplogic_beru_log_list.php?lang=".LANGUAGE_ID,
			"more_url" => [
				"iplogic_beru_log_list.php",
				"iplogic_beru_log_detail.php",
			]
		];
	}
	$arConMenu[] = [
		"text" => Loc::getMessage("IPL_MA_MENU_HELP"),
		"title" => Loc::getMessage("IPL_MA_MENU_HELP"),
		"url" => "javascript:window.open('https://iplogic.ru/doc/course/index.php?COURSE_ID=2&INDEX=Y', '_blank');void(0);",
	];

	$arModMenu = [
		"parent_menu" => "global_menu_services",
		"section" => "beru",
		"sort" => Option::get($moduleID, 'menu_sort_index', 10),
		"text" => Loc::getMessage("IPL_MA_MENU_BERU_CONTROL"),
		"title" => Loc::getMessage("IPL_MA_MENU_BERU_TITLE"),
		"icon" => "iplogic_beru_menu_logo",
		"items_id" => "menu_beru",
		"items" => $arConMenu
	]; 
	return $arModMenu;
}
else
{
	return false;
}