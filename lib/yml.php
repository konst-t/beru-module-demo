<?
namespace Iplogic\Beru;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\ConditionTable,
	\Iplogic\Beru\ProfileTable,
	\Iplogic\Beru\ProductTable;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");

class YML 
{

	public static $moduleID = "iplogic.beru";
	public 
		$arProfile,
		$root_dir,
		$error;
	protected
		$IDs,
		$arProducts = [];

	function __construct($profileID, $root_dir = "") 
	{
		$this->arProfile = ProfileTable::getById($profileID); 
		$this->root_dir = $root_dir;
		return;
	}

	public function generateNew() { 
		$this->getProducts();
		$xml = new \DomDocument('1.0',LANG_CHARSET);
		$yml_catalog = simplexml_import_dom($xml->createElement('yml_catalog'));
		$yml_catalog->addAttribute('date', date('d.m.Y H:i:s'));
		$shop = $yml_catalog->addChild('shop');
		$shop->addChild('name', Control::prepareText($this->arProfile["YML_NAME"]));
		$shop->addChild('company', Control::prepareText($this->arProfile["COMPANY"]));
		$shop->addChild('url', Control::prepareText($this->arProfile["YML_URL"]));
		$shop->addChild('platform', Control::prepareText(Loc::getMessage('CMS_NAME')));
		$shop->addChild('version', Control::getBusVersion());
		$shop->addChild('agency', 'iPloGic');
		$shop->addChild('email', 'info@iplogic.ru');
		$currencies = $shop->addChild('currencies');
		$currency = $currencies->addChild('currency');
		$currency->addAttribute('id', "RUR");
		$currency->addAttribute('rate', "1");
		$categories = $shop->addChild('categories');
		$arCategories = $this->sectionList();
		foreach($arCategories as $arCategory) {
			$category = $categories->addChild('category', Control::prepareText($arCategory["NAME"]));
			$category->addAttribute('id', $arCategory["ID"]);
			if ($arCategory["IBLOCK_SECTION_ID"]>0) {
				$category->addAttribute('parentId', $arCategory["IBLOCK_SECTION_ID"]);
			}
		}

		$shop->addChild('enable_auto_discounts', ($this->arProfile["ENABLE_AUTO_DISCOUNTS"]=="Y" ? "yes" : "no"));

		$offers = $shop->addChild('offers'); 
		foreach($this->arProducts as $arOffer) {
			$offer = $offers->addChild('offer'); 
			$offer->addAttribute('id', $arOffer["PRODUCT_ID"]);
			$offer->addChild('name', Control::prepareText($arOffer["DETAILS"]["NAME"]));
			if ($arOffer["DETAILS"]["VENDOR"] != "")
				$offer->addChild('vendor', Control::prepareText($arOffer["DETAILS"]["VENDOR"]));
			$offer->addChild('url', $arOffer["DETAILS"]["URL"]);
			$offer->addChild('price', $arOffer["DETAILS"]["PRICE"]);
			if ($arOffer["DETAILS"]["OLD_PRICE"] != "" && $arOffer["DETAILS"]["OLD_PRICE"]>0)
				$offer->addChild('oldprice', $arOffer["DETAILS"]["OLD_PRICE"]);
			$offer->addChild('currencyId', 'RUR');
			$offer->addChild('picture', $arOffer["DETAILS"]["IMG"]);
			if ($arOffer["DETAILS"]["DESCRIPTION"] != "") {
				$description = $offer->addChild('description');
				$child_node = dom_import_simplexml($description); 
				$child_owner = $child_node->ownerDocument; 
				$child_node->appendChild($child_owner->createCDATASection(Control::prepareText($arOffer["DETAILS"]["DESCRIPTION"])));
			}
			if ($arOffer["DETAILS"]["BARCODE"] != "")
				$offer->addChild('barcode', Control::prepareText($arOffer["DETAILS"]["BARCODE"]));
			$offer->addChild('shop-sku', Control::prepareText($arOffer["SKU_ID"]));
			if ($arOffer["MARKET_SKU"] != "")
				$offer->addChild('market-sku', $arOffer["MARKET_SKU"]);
			if ($this->arProfile["VAT"] != "NONE")
				$offer->addChild('vat', $this->arProfile["VAT"]);
			$offer->addChild('weight', $arOffer["DETAILS"]["WEIGHT"]);
			$offer->addChild('dimensions', $arOffer["DETAILS"]["DIMENSIONS"]);
			$offer->addChild('count', $arOffer["DETAILS"]["STOCK_FIT"]);


		}

		$xml->appendChild(dom_import_simplexml($yml_catalog));
		$xml->formatOutput = true;
		return $xml->save($this->root_dir.$this->arProfile["YML_FILENAME"]);
	}


	private function sectionList() { 
		foreach($this->arProducts as $prod) {
			$det = $prod["DETAILS"];
			if ($det["SECTION_ID"]>0) {
				$sects[] = $det["SECTION_ID"];
			}
		}
		$sections = array_unique($sects);
		$newSec = $sections;
		$arSectionsTmp = [];
		while (count($newSec)>0) {
			$rsSec = \CIblockSection::getList([],["ID"=>$newSec],false,["ID","NAME","IBLOCK_SECTION_ID","DEPTH_LEVEL"]);
			$newSec = [];
			while($arSec = $rsSec->Fetch()) {
				if ($arSec["IBLOCK_SECTION_ID"]=="") {
					$arSec["IBLOCK_SECTION_ID"] = 0;
				}
				else {
					if (!in_array($arSec["IBLOCK_SECTION_ID"], $sections)){
						$newSec[] = $arSec["IBLOCK_SECTION_ID"];
						$sections[] = $arSec["IBLOCK_SECTION_ID"];
					}
				}
				$arSectionsTmp[] = $arSec;
			}
		} 
		return $arSectionsTmp;
	}


	public function getFeedProductsIDs() { 
		$this->IDs = [];
		Loader::includeModule('iblock');
		Loader::includeModule('catalog');
		$el = new \CIblockElement();
		$offerIBlockID = false;
		$arProdIBlock = \CCatalogSKU::GetInfoByProductIBlock($this->arProfile["IBLOCK_ID"]);
		if (is_array($arProdIBlock)) {
			$offerIBlockID = $arProdIBlock["IBLOCK_ID"];
		}
		$rsConditions = ConditionTable::getList(["filter"=>["PROFILE_ID"=>$this->arProfile["ID"],"ACTIVE"=>"Y", "TYPE"=>"IY"], "order"=>["SORT"=>"ASC"]]);
		while($arCondition = $rsConditions->Fetch()) {

			$arIDs = [];
			if ($arCondition["ACTION"] == "EXCLUDE_IN_YML" && !count($this->IDs))
				continue;
			$arSelect = ["ID"];
			switch ($arCondition["PROP_TYPE"]) {

				case "element_fields":
					$arFilter = [
						"IBLOCK_ID" => $this->arProfile["IBLOCK_ID"],
						self::getCondition($arCondition["COND"]).$arCondition["PROP"] => self::treatConditionValue($arCondition)
					];
					$rsData = $el->getList([], $arFilter, false, false, $arSelect);
					while($prod = $rsData->Fetch()) {
						$arIDs[] = $prod["ID"];
					}
					if( $offerIBlockID ) {
						$arFilter = [
							"IBLOCK_ID" => $offerIBlockID,
							self::getCondition($arCondition["COND"]).$arCondition["PROP"] => self::treatConditionValue($arCondition)
						];
						$rsData = $el->getList([], $arFilter, false, false, $arSelect);
						while($prod = $rsData->Fetch()) {
							$arIDs[] = $prod["ID"];
						}
					}
					break;

				case "product_fields":
					$arFilter = [
						self::getCondition($arCondition["COND"]).$arCondition["PROP"] => self::treatConditionValue($arCondition)
					];
					$rsData = \CCatalogProduct::GetList([], $arFilter, false, false, $arSelect);
					while($prod = $rsData->Fetch()) {
						$arIDs[] = $prod["ID"];
					}
					break;

				case "common_props":
					$rsProp = \CIBlockProperty::GetList([],["IBLOCK_ID" => $this->arProfile["IBLOCK_ID"], "CODE" => $arCondition["PROP"]]);
					$arProp = $rsProp->Fetch(); 
					$filterProp = self::getCondition($arCondition["COND"])."PROPERTY_".$arCondition["PROP"];
					if ($arProp["PROPERTY_TYPE"] == "L") {
						$filterProp = $filterProp."_VALUE";
					}
					$arFilter = [
						"IBLOCK_ID" => $this->arProfile["IBLOCK_ID"],
						$filterProp => self::treatConditionValue($arCondition)
					];
					$rsData = $el->getList([], $arFilter, false, false, $arSelect);
					while($prod = $rsData->Fetch()) {
						$arIDs[] = $prod["ID"];
					}
					if( $offerIBlockID ) {
						$arFilter = [
							"IBLOCK_ID" => $offerIBlockID,
							$filterProp => self::treatConditionValue($arCondition)
						];
						$rsData = $el->getList([], $arFilter, false, false, $arSelect);
						while($prod = $rsData->Fetch()) {
							$arIDs[] = $prod["ID"];
						}
					}
					break;

				case "product_props":
					$rsProp = \CIBlockProperty::GetList([],["IBLOCK_ID" => $this->arProfile["IBLOCK_ID"], "CODE" => $arCondition["PROP"]]);
					$arProp = $rsProp->Fetch(); 
					$filterProp = self::getCondition($arCondition["COND"])."PROPERTY_".$arCondition["PROP"];
					if ($arProp["PROPERTY_TYPE"] == "L") {
						$filterProp = $filterProp."_VALUE";
					}
					$arFilter = [
						"IBLOCK_ID" => $this->arProfile["IBLOCK_ID"],
						$filterProp => self::treatConditionValue($arCondition)
					];
					$rsData = $el->getList([], $arFilter, false, false, $arSelect);
					while($prod = $rsData->Fetch()) {
						$arIDs[] = $prod["ID"];
					}
					break;

				case "offer_props":
					if( $offerIBlockID ) {
						$rsProp = \CIBlockProperty::GetList([],["IBLOCK_ID" => $offerIBlockID, "CODE" => $arCondition["PROP"]]);
						$arProp = $rsProp->Fetch(); 
						$filterProp = self::getCondition($arCondition["COND"])."PROPERTY_".$arCondition["PROP"];
						if ($arProp["PROPERTY_TYPE"] == "L") {
							$filterProp = $filterProp."_VALUE";
						}
						$arFilter = [
							"IBLOCK_ID" => $offerIBlockID,
							$filterProp => self::treatConditionValue($arCondition)
						];
						$rsData = $el->getList([], $arFilter, false, false, $arSelect);
						while($prod = $rsData->Fetch()) {
							$arIDs[] = $prod["ID"];
						}
					}
					break;

				case "prices":
					if ($arCondition["COND"]!="NOT_EMPTY" && $arCondition["COND"]!="EMPTY") {
						$rsPrices = \Bitrix\Catalog\PriceTable::getList([
							"select" => ["PRODUCT_ID"],
							"filter" => [
								"=CATALOG_GROUP_ID" => $arCondition["PROP"],
								self::getCondition($arCondition["COND"])."PRICE" => self::treatConditionValue($arCondition)
							]
						]);
						while($price = $rsPrices->Fetch()) { 
							$arIDs[] = $price["PRODUCT_ID"];
						}
					}
					else {
						$rsPrices = \Bitrix\Catalog\PriceTable::getList([
							"select" => ["PRODUCT_ID"],
							"filter" => [
								"=CATALOG_GROUP_ID" => $arCondition["PROP"]
							]
						]);
					}
					if ($arCondition["COND"]=="EMPTY") {
						while($price = $rsPrices->Fetch()) { 
							$allProducts[] = $price["PRODUCT_ID"];
						}
						$rsData = $el->GetList([], ["IBLOCK_ID" => $this->arProfile["IBLOCK_ID"]], false, false, ["ID"]);
						while($elem = $rsData->Fetch()) { 
							if (!in_array($elem["ID"],$allProducts)) {
								$arIDs[] = $elem["ID"];
							}
						}
						if ($offerIBlockID) {
							$rsData = $el->GetList([], ["IBLOCK_ID" => $offerIBlockID], false, false, ["ID"]);
							while($elem = $rsData->Fetch()) { 
								if (!in_array($elem["ID"],$allProducts)) {
									$arIDs[] = $elem["ID"];
								}
							}
						}
					}
					if ($arCondition["COND"]=="NOT_EMPTY") {
						while($price = $rsPrices->Fetch()) { 
							$arIDs[] = $price["PRODUCT_ID"];
						}
					}
					break;

				case "stores":
					if ($arCondition["COND"]!="NOT_EMPTY" && $arCondition["COND"]!="EMPTY") {
						$rsStores = \Bitrix\Catalog\StoreProductTable::getList([
							"select" => ["PRODUCT_ID"],
							"filter" => [
								"=STORE_ID" => $arCondition["PROP"],
								self::getCondition($arCondition["COND"])."AMOUNT" => self::treatConditionValue($arCondition)
							]
						]);
						while($store = $rsStores->Fetch()) { 
							$arIDs[] = $store["PRODUCT_ID"];
						}
					}
					else {
						$rsStores = \Bitrix\Catalog\StoreProductTable::getList([
							"select" => ["PRODUCT_ID"],
							"filter" => [
								"=STORE_ID" => $arCondition["PROP"]
							]
						]);
					}
					if ($arCondition["COND"]=="EMPTY") {
						while($store = $rsStores->Fetch()) { 
							$allProducts[] = $store["PRODUCT_ID"];
						}
						$rsData = $el->GetList([], ["IBLOCK_ID" => $this->arProfile["IBLOCK_ID"]], false, false, ["ID"]);
						while($elem = $rsData->Fetch()) { 
							if (!in_array($elem["ID"],$allProducts)) {
								$arIDs[] = $elem["ID"];
							}
						}
						if ($offerIBlockID) {
							$rsData = $el->GetList([], ["IBLOCK_ID" => $offerIBlockID], false, false, ["ID"]);
							while($elem = $rsData->Fetch()) { 
								if (!in_array($elem["ID"],$allProducts)) {
									$arIDs[] = $elem["ID"];
								}
							}
						}
					}
					if ($arCondition["COND"]=="NOT_EMPTY") {
						while($store = $rsStores->Fetch()) { 
							$arIDs[] = $store["PRODUCT_ID"];
						}
					}
					break;
			}
			$this->modifyIDsArray($arCondition["ACTION"],$arIDs);
			unset($rsData);
			unset($rsStores);
			unset($rsPrices);
			unset($arIDs);
		}  
		if ($this->arProfile["YML_FROM_MARKET"]){
			$rsData = ProductTable::getList(["filter"=>["PROFILE_ID"=>$this->arProfile["ID"],"API"=>"Y"],"select"=>["ID"]]);
			while($arP = $rsData->Fetch()) {
				$arIDs[] = $arP["ID"];
			}
			$this->modifyIDsArray("INCLUDE_IN_YML",$arIDs);
		}
		return $this->IDs;
	}


	protected function modifyIDsArray($action, $arMod) { 
		if ($action == "INCLUDE_IN_YML") { 
			foreach($arMod as $id) {
				if (!in_array($id, $this->IDs))
					$this->IDs[] = $id;
			}
		}
		else { 
			foreach($this->IDs as $key=>$id) {
				if (in_array($id, $arMod))
					unset($this->IDs[$key]);
			}
		}
	}


	protected static function getCondition($cond) {
		switch ($cond) {
			case "INCLUDE":
				$pref = "%";
				break;
			case "MORE":
				$pref = ">";
				break;
			case "LESS":
				$pref = "<";
				break;
			case "NOT_EMPTY":
				$pref = "!=";
				break;
			case "EMPTY":
				$pref = "=";
				break;
			default:
				$pref = "";
			break;
		}
		return $pref;
	}


	protected static function treatConditionValue($cond) {
		switch ($cond["COND"]) {
			case "IN_ARRAY":
				$val = [];
				$ar = explode(",", $cond["VALUE"]);
				foreach($ar as $e) {
					$val[] = trim($e);
				}
				break;
			case "EMPTY":
			case "NOT_EMPTY":
				$val = false;
				break;
			default:
				$val = $cond["VALUE"];
				break;
		}
		return $val;
	}


	protected function getProducts() {
		$rsProducts = ProductTable::getList(["filter"=>["FEED"=>"Y", "PROFILE_ID"=>$this->arProfile["ID"]]]);
		while($arProduct = $rsProducts->Fetch()) {
			$arProduct["DETAILS"] = unserialize($arProduct["DETAILS"]);
			$this->arProducts[] = $arProduct;
		}
	}

}