<?

namespace Iplogic\Beru;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application,
	\Bitrix\Main\Web\Json,
	\Iplogic\Beru\YMAPI,
	\Iplogic\Beru\ApiLogTable as ApiLog,
	\Iplogic\Beru\ProfileTable as Profile,
	\Iplogic\Beru\OrderTable as Order,
	\Iplogic\Beru\ProductTable as Product,
	\Iplogic\Beru\TaskTable as Task;


IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");


class Control {

	public static $moduleID = "iplogic.beru";
	public 
		$arProfile,
		$serverMethod,
		$error = [];

	function __construct($profileID = false) {
		if ($profileID) {
			$this->arProfile = Profile::getById($profileID);
		}
		return;
	}

	public static function getOption($option, $default = "") {
		return Option::get(self::$moduleID, $option, $default, SITE_ID);
	}

	public static function getBusVersion() {
		return SM_VERSION;
	}

	public static function toHtml($s) {
		$s = str_replace(' ','&nbsp;',$s);
		$s = nl2br($s);
		$s = str_replace('\r','\n',$s);
		$s = str_replace('\n','',$s);
		return($s);
	}

	public static function prepareText($str, $charset = true, $cdata = false) {
		if(!$cdata) {
			$bad  = ["<", ">", "'", '"', "&"];
			$good = ["&lt;", "&gt;", "&apos;", "&quot;", "&amp;"];
			$str = str_replace($bad, $good, $str);
		}
		if ($charset && LANG_CHARSET != "UTF-8")
			$str = iconv( "cp1251","UTF-8", $str);
		return $str;
	}

	public static function fixUnicode($str) {
		if (LANG_CHARSET != "UTF-8")
			$str = iconv( "UTF-8", "windows-1251", $str);
		return $str;
	}

	public static function fixUnicodeRecursive($array) {
		if (LANG_CHARSET == "UTF-8")
			return $array;
		foreach ($array as $key => $value){
			if(is_array($value)){
				$array[$key] = self::fixUnicodeRecursive($array[$key]);
			}else{
				$array[$key] = iconv("UTF-8", "windows-1251", $value);
			}
		}
		return $array;
	}

	public static function prepareRequestText($str) {
		if (LANG_CHARSET != "UTF-8")
			$str = iconv( "windows-1251", "UTF-8", $str);
		return $str;
	}

	public static function prepareRequestRecursive($array) {
		if (LANG_CHARSET == "UTF-8")
			return $array;
		foreach ($array as $key => $value){
			if(is_array($value)){
				$array[$key] = self::prepareRequestRecursive($array[$key]);
			}else{
				$array[$key] = iconv("windows-1251", "UTF-8", $value);
			}
		}
		return $array;
	}

	public static function jsonEncode($ar) {
		$ar = self::prepareRequestRecursive($ar);
		return Json::encode((object)$ar);
	}

	public function initMethodFromUrl() {
		global $APPLICATION;
		$rsData = Profile::getList(["filter"=>["ACTIVE"=>"Y"]]);
		while ( $arProfile = $rsData->Fetch() ) {
			$length = strlen($arProfile["BASE_URL"]);
			if (substr($APPLICATION->GetCurPage(false),0,$length) == $arProfile["BASE_URL"]) {
				$this->serverMethod = substr($APPLICATION->GetCurPage(false),$length-1);
				$this->arProfile = $arProfile; 
				$prefix = "YM";
				$actionfile = trim($prefix.str_replace("/","_",$this->serverMethod),"_").".php";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::$moduleID."/services/".$actionfile)){  
					$this->error = [
						"405",
						"Method Not Allowed",
						"Request method '".$this->serverMethod."' not supported"
					];
					return false;
				}
				if (!$this->checkAuthorization())
					return false;
				$this->arProfile = Profile::getById($arProfile["ID"]);
				return $actionfile;
			}
		}
		$this->error = [
			"403",
			"Forbidden",
			"Profile not found"
		];
		return false;
	}

	public function getErrorArray() {
		return [
			"error" => [
				"code" => (int)$this->error[0],
				"message" => $this->error[2],
			],
			"errors" => [
				[
					"code" => $this->error[1],
					"message" => $this->error[2],
				]
			],
		];
	}

	public function getallheaders() {
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			$name = str_replace("REDIRECT_", "", $name);
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			} else if ($name == "CONTENT_TYPE") {
				$headers["Content-Type"] = $value;
			} else if ($name == "CONTENT_LENGTH") {
				$headers["Content-Length"] = $value; 
			}
		}
		return $headers;
	}

	public function checkAuthorization() {
		$headers = $this->getallheaders();
		foreach ($headers as $key => $message) {
			unset($headers[$key]);
			$headers[strtolower($key)] = $message;
		}
		$token = $headers["authorization"];
		if ($token == ""){
			$this->error = [
				"401",
				"Forbidden",
				"OAuth token is not specified"
			];
			return false;
		}
		if ($token != $this->arProfile["GET_TOKEN"]){
			$this->error = [
				"403",
				"Forbidden",
				"Wrong token"
			];
			return false;
		}
		return true;
	}


	public function getProductId($SKU_ID) {
		$arProdIBlock = \CCatalogSKU::GetInfoByProductIBlock($this->arProfile["IBLOCK_ID"]);
		if (is_array($arProdIBlock)) {
			$offerIBlockID = $arProdIBlock["IBLOCK_ID"];
		}
		if (!isset($this->arProfile["PROP"]["SHOP_SKU_ID"]))
			return false;
		$ident = $this->arProfile["PROP"]["SHOP_SKU_ID"];
		$arFilter = ["ACTIVE"=>"Y"];
		if ($ident["TYPE"] == "element_fields")
			$arFilter[$ident["VALUE"]] = $SKU_ID;
		else
			$arFilter["PROPERTY_".$ident["VALUE"]] = $SKU_ID;
		$arEl = false;
		if ($offerIBlockID) {
			$arFilter["IBLOCK_ID"] = $offerIBlockID;
			$rsData = \CIBlockElement::getList([],$arFilter);
			$arEl = $rsData->Fetch(); 
		}
		if (!$arEl) {
			$arFilter["IBLOCK_ID"] = $this->arProfile["IBLOCK_ID"];
			$rsData = \CIBlockElement::getList([],$arFilter);
			$arEl = $rsData->Fetch();
		}
		if (!$arEl)
			return false;
		return $arEl["ID"];
	}


	public function getSKUByProductID($ID) {
		$SKU_ID = "";
		$rsData = \CIBlockElement::getList([],["ID"=>$ID]);
		$arEl = $rsData->Fetch(); 
		if (!$arEl)
			return false;
		$ident = $this->arProfile["PROP"]["SHOP_SKU_ID"];
		if ($ident["TYPE"] == "element_fields") {
			$SKU_ID = $arEl[$ident["VALUE"]];
		}
		else {
			$rsProp = \CIBlockElement::GetProperty($arEl["IBLOCK_ID"], $arEl["ID"]);
			while($arProp = $rsProp->Fetch()) {
				if ($arProp["CODE"] == $ident["VALUE"]) {
					$SKU_ID = $arProp["VALUE"];
				}
			}
		}

		if($SKU_ID != "") {
			return $this->getSKU($SKU_ID);
		}
		return false;

	}


	public function getSKU($SKU_ID, $arSelect = [], $no_cache = false) {
		if (!$no_cache) {
			if($product = Product::getBySkuId($SKU_ID,$this->arProfile["ID"])) {
				if ($product["DETAILS"] != "")
					return unserialize($product["DETAILS"]);
			}
		}
		$service = ["CML2_LINK", "VAT", "ELEMENT_NAME", "ELEMENT_XML_ID", "SECTION_ID","URL"];
		if (!count($arSelect)) {
			foreach($this->arProfile["PROP"] as $p) {
				$arSelect[] = $p["NAME"];
			}
			$arSelect = array_merge($service, $arSelect);
		}
		$accord = [];
		foreach($arSelect as $key => $prop) {
			if (!isset($this->arProfile["PROP"][$prop]) && !in_array($prop, $service)) {
				unset($arSelect[$key]);
			}
			else{
				$prop = $this->arProfile["PROP"][$prop];
				if (is_array($accord[$prop["TYPE"]])) {
					if (!in_array($prop["VALUE"], $accord[$prop["TYPE"]]))
						$accord[$prop["TYPE"]][] = $prop["VALUE"];
				}
				else
					$accord[$prop["TYPE"]][] = $prop["VALUE"];
			}
		}
		$offerIBlockID = false;
		$arProdIBlock = \CCatalogSKU::GetInfoByProductIBlock($this->arProfile["IBLOCK_ID"]);
		if (is_array($arProdIBlock)) {
			$offerIBlockID = $arProdIBlock["IBLOCK_ID"];
		}
		$ident = $this->arProfile["PROP"]["SHOP_SKU_ID"];
		$arFilter = ["ACTIVE"=>"Y"];
		if ($ident["TYPE"] == "element_fields")
			$arFilter[$ident["VALUE"]] = $SKU_ID;
		else
			$arFilter["PROPERTY_".$ident["VALUE"]] = $SKU_ID;
		$arEl = false;
		if ($offerIBlockID) {
			$arFilter["IBLOCK_ID"] = $offerIBlockID;
			$rsData = \CIBlockElement::getList([],$arFilter);
			$arEl = $rsData->Fetch(); 
		}
		if (!$arEl) {
			$arFilter["IBLOCK_ID"] = $this->arProfile["IBLOCK_ID"];
			$rsData = \CIBlockElement::getList([],$arFilter);
			$arEl = $rsData->Fetch();
		}
		if (!$arEl)
			return false;
		$is_offer = false;
		if ($arEl["IBLOCK_ID"] == $offerIBlockID){
			$is_offer = true;
		}

		$po = "PRODUCT";
		if ($is_offer)
			$po = "OFFER";
		$rsProp = \CIBlockElement::GetProperty($arEl["IBLOCK_ID"], $arEl["ID"]);
		while($arProp = $rsProp->Fetch()) {
			$arEl["PROPERTIES"][$po][$arProp["CODE"]]["TYPE"] = $arProp["PROPERTY_TYPE"];
			if ($arProp["CODE"] == "CML2_LINK")
				$CML2_LINK = $arProp["VALUE"];
			$value = $arProp["VALUE"];
			if ($arProp["PROPERTY_TYPE"] == "L")
				$value = $arProp["VALUE_ENUM"];
			if ($arProp["MULTIPLE"] == "Y")
				$arEl["PROPERTIES"][$po][$arProp["CODE"]]["VALUE"][] = $value;
			else
				$arEl["PROPERTIES"][$po][$arProp["CODE"]]["VALUE"] = $value;
		}
		if ($is_offer) {
			$po = "PRODUCT";
			$rsProp = \CIBlockElement::GetProperty($this->arProfile["IBLOCK_ID"], $CML2_LINK);
			while($arProp = $rsProp->Fetch()) {
				$arEl["PROPERTIES"][$po][$arProp["CODE"]]["TYPE"] = $arProp["PROPERTY_TYPE"];
				$value = $arProp["VALUE"];
				if ($arProp["PROPERTY_TYPE"] == "L")
					$value = $arProp["VALUE_ENUM"];
				if ($arProp["MULTIPLE"] == "Y")
					$arEl["PROPERTIES"][$po][$arProp["CODE"]]["VALUE"][] = $value;
				else
					$arEl["PROPERTIES"][$po][$arProp["CODE"]]["VALUE"] = $value;
			}
		}

		if ($is_offer) {
			if ($CML2_LINK > 0) {
				$rsParent = \CIBlockElement::getById($CML2_LINK);
				$arParent = $rsParent->Fetch();
				$section = $arParent["IBLOCK_SECTION_ID"];
				$code = $arParent["CODE"];
				$url = $arParent["DETAIL_PAGE_URL"];
			}
		}
		else {
			$section = $arEl["IBLOCK_SECTION_ID"];
			$code = $arEl["CODE"];
			$url = $arEl["DETAIL_PAGE_URL"];
		}
		if(stristr($url, '#SECTION_CODE#') !== FALSE) {
			$res = \CIblockSection::getList([],["ID"=>$section, "CHECK_PERMISSIONS"=>"N"],false,["CODE"]);
			$sec = $res->Fetch();
			$section_code = $sec["CODE"];
		}
		if(stristr($url, '#SECTION_CODE_PATH#') !== FALSE) {
			$section_p = [];
			while($section > 0) {
				$res = \CIblockSection::getList([],["ID"=>$section, "CHECK_PERMISSIONS"=>"N"],false,["CODE","IBLOCK_SECTION_ID"]);
				$sec = $res->Fetch();
				$section_p[] = $sec["CODE"];

				if ($sec["IBLOCK_SECTION_ID"]=="") {
					$section = 0;
				}
				else {
					$section = $sec["IBLOCK_SECTION_ID"];
				}
			}
			$section_path = implode("/", $section_p);
		}
		$url = str_replace([
			"#SITE_DIR#",
			"#CODE#",
			"#ELEMENT_ID#",
			"#ELEMENT_CODE#",
			"#SECTION_ID#",
			"#SECTION_CODE#",
			"#SECTION_CODE_PATH#"
		],[
			"https://".self::getOption("domen"),
			$code,
			$arEl["ID"],
			$code,
			$section,
			$section_code,
			$section_path
		],$url);
		if( array_key_exists("stores", $accord)) {
			$rsStore = \CCatalogStoreProduct::GetList([], ['PRODUCT_ID' =>$arEl["ID"]], false, false, ['AMOUNT','STORE_ID']);
			while ($arStore = $rsStore->Fetch()){
				$arStores[$arStore['STORE_ID']] = $arStore['AMOUNT'];
			}
		}
		if( array_key_exists("product_fields", $accord) || in_array("VAT", $arSelect) || array_key_exists("from_product", $accord) ) {
			$arProduct = \CCatalogProduct::GetByID($arEl["ID"]); 
		}
		if( array_key_exists("prices", $accord)) {
			$arPrices_ = \Bitrix\Catalog\PriceTable::getList(["filter" => ["PRODUCT_ID" => $arEl["ID"]]])->fetchAll();
			foreach($arPrices_ as $price) {
				$arPrices[$price["CATALOG_GROUP_ID"]] = $price["PRICE"];
			}
		}
		if( array_key_exists("min_discount_price", $accord)) {
			$arMinPrice = \CCatalogProduct::GetOptimalPrice($arEl["ID"]);
			$minPrice = $arMinPrice["RESULT_PRICE"]["DISCOUNT_PRICE"];
		}
		$arResult = ["SHOP_SKU_ID"=>$SKU_ID, "PRODUCT_ID" => $arEl["ID"], "IS_OFFER" => $is_offer, "SECTION_ID" => $section, "URL" => $url];
		foreach($arSelect as $prop) {
			if ($is_offer && $prop == "CML2_LINK") {
				if ($CML2_LINK > 0)
					$arResult["CML2_LINK"] = $CML2_LINK;
				else {
					$rsProp = \CIBlockElement::GetProperty($arEl["IBLOCK_ID"], $arEl["ID"]);
					while($arProp = $rsProp->Fetch()) {
						if ($arProp["CODE"] == "CML2_LINK")
							$arResult["CML2_LINK"] = $arProp["VALUE"];
					}
				}
			}

			switch ($this->arProfile["PROP"][$prop]["TYPE"]) {

				case 'element_fields':
					$arResult[$prop] = $arEl[$this->arProfile["PROP"][$prop]["VALUE"]];
					if ($this->arProfile["PROP"][$prop]["VALUE"] == "PREVIEW_PICTURE"
						|| $this->arProfile["PROP"][$prop]["VALUE"] == "DETAIL_PICTURE"){
						$arResult[$prop] = "https://".self::getOption("domen").\CFile::GetPath($arResult[$prop]);
					}
					break;

				case 'product_props':
					$arResult[$prop] = $arEl["PROPERTIES"]["PRODUCT"][$this->arProfile["PROP"][$prop]["VALUE"]]["VALUE"];
					$propType = $arEl["PROPERTIES"]["PRODUCT"][$this->arProfile["PROP"][$prop]["VALUE"]]["TYPE"];
					break;

				case 'offer_props':
					$arResult[$prop] = $arEl["PROPERTIES"]["OFFER"][$this->arProfile["PROP"][$prop]["VALUE"]]["VALUE"];
					$propType = $arEl["PROPERTIES"]["OFFER"][$this->arProfile["PROP"][$prop]["VALUE"]]["TYPE"];
					break;

				case 'common_props':
					$arResult[$prop] = $arEl["PROPERTIES"]["OFFER"][$this->arProfile["PROP"][$prop]["VALUE"]]["VALUE"];
					$propType = $arEl["PROPERTIES"]["OFFER"][$this->arProfile["PROP"][$prop]["VALUE"]]["TYPE"];
					if (!$arResult[$prop]) {
						$arResult[$prop] = $arEl["PROPERTIES"]["PRODUCT"][$this->arProfile["PROP"][$prop]["VALUE"]]["VALUE"];
						$propType = $arEl["PROPERTIES"]["PRODUCT"][$this->arProfile["PROP"][$prop]["VALUE"]]["TYPE"];
					}
					break;

				case 'stores':
					if ($arStores[$this->arProfile["PROP"][$prop]["VALUE"]]>0)
						$arResult[$prop] = $arStores[$this->arProfile["PROP"][$prop]["VALUE"]];
					else
						$arResult[$prop] = 0;
					break;

				case 'product_fields':
					$arResult[$prop] = $arProduct[$this->arProfile["PROP"][$prop]["VALUE"]];
					break;

				case 'current_time':
					$arResult[$prop] = self::timeFix(date(DATE_ISO8601, time()));
					break;

				case 'element_last_change':
					$arResult[$prop] = self::timeFix(date(DATE_ISO8601, $arEl["TIMESTAMP_X_UNIX"]));
					break;

				case 'prices':
					$arResult[$prop] = $arPrices[$this->arProfile["PROP"][$prop]["VALUE"]];
					break;

				case 'min_discount_price':
					$arResult[$prop] = $minPrice;
					break;
				case 'from_product':
					if ($prop == "WEIGHT") {
						$arResult[$prop] = str_replace(",",".",($arProduct["WEIGHT"]/1000));
					}
					if ($prop == "DIMENSIONS") {
						$arResult[$prop] = str_replace(",",".",($arProduct["LENGTH"]/10)."/".($arProduct["WIDTH"]/10)."/".($arProduct["HEIGHT"]/10));
					}
					break;
			}
			if ($propType && $arResult[$prop]!="") {
				if(is_array($arResult[$prop]))
					foreach($arResult[$prop] as $key=>$val)
						$arResult[$prop][$key] = $this->replaceIdProps($propType, $val);
				else {
					$arResult[$prop] = $this->replaceIdProps($propType, $arResult[$prop]);
				}
			}
			unset($propType);
			if ($prop == "VAT") {
				$vat_id = $arProduct["VAT_ID"];
				if ( $vat_id > 0 ){
					$arVAT = \CCatalogVat::GetByID($vat_id)->Fetch();
					$arResult[$prop] = round($arVAT["RATE"]);
				}
			}
			if ($prop == "ELEMENT_NAME")
				$arResult[$prop] = $arEl["NAME"];
			if ($prop == "ELEMENT_XML_ID")
				$arResult[$prop] = $arEl["XML_ID"];
			if ($prop == "DISABLED") {
				$disabled = "false";
				if ($arResult[$prop] == "N")
					$arResult[$prop] = "true";
				if ($arResult[$prop] == 0)
					$arResult[$prop] = "true";
			}

		}
		return $arResult;
	}


	public function timeFix($time) {
		return $time = substr($time,0,22).":00";
	}


	protected function replaceIdProps($propType, $val) {
		if ($propType == "E") {
			$res = \CIBlockElement::GetByID($val);
			if($arConEl = $res->GetNext())
				$val = $arConEl["NAME"];
		}
		elseif ($propType == "F"){
			$val = \CFile::GetPath($val);
		}
		return $val;
	}


	private static function clearOldFiles() {
		$root = str_replace("/bitrix/modules/".self::$moduleID."/lib", "", __DIR__);
		$dir = $root."/upload/tmp/".self::$moduleID;
		if ($d = @opendir($dir)) {
			while (($file = readdir($d)) !== false) {
				if ($file != "." && $file != ".."){
					$ftime = filemtime($dir.'/'.$file); 
					if (time()-$ftime > (Option::get(self::$moduleID,"keep_temp_files_days")*86400)){
						unlink($dir.'/'.$file); 
					}
				}
			}
			closedir($d);
		}
	}


	public static function getMessFromAllLangFiles($file, $key) {
		$arLanguages = Array();
		$rsLanguage = \CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch())
			$arLanguages[] = $arLanguage["LID"];
		$arMess = [];

		$filepath = rtrim(preg_replace("'[\\\\/]+'", "/", $file), "/ ");
		$module_path = "/modules/";
		if(strpos($filepath, $module_path) !== false)
		{
			$pos = strlen($filepath) - strpos(strrev($filepath), strrev($module_path));
			$rel_path = substr($filepath, $pos);
			$p = strpos($rel_path, "/");
			if(!$p)
				return false;

			$module_name = substr($rel_path, 0, $p);
			$rel_path = substr($rel_path, $p+1);
			$BX_DOC_ROOT = rtrim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]), "/ ");
			$module_path = $BX_DOC_ROOT.getLocalPath($module_path.$module_name);
		}
		else
		{
			return false;
		}

		foreach($arLanguages as $lang) {

			unset($MESS);
			$fname = $module_path."/lang/".$lang."/".$rel_path;
			$fname = \Bitrix\Main\Localization\Translation::convertLangPath($fname, $lang);
			if (file_exists($fname)) {
				include($fname);
				$arMess[$lang] = $MESS[$key];
			}

		}
		return $arMess;
	}



	/* Agent */


	public static function executeTasksAgent() {
		$can_execute_tasks = Option::get(self::$moduleID,"can_execute_tasks","Y");
		$task_time = time() - Option::get(self::$moduleID,"last_task_time",0);
		if ($task_time > 600 && $can_execute_tasks == "N") {
			Option::set(self::$moduleID,"can_execute_tasks","Y");
			$can_execute_tasks = "Y";
		}
		if ( $can_execute_tasks == "Y") {
			if (Option::get(self::$moduleID,"allow_multichain_tasks","N") == "N") {
				Option::set(self::$moduleID,"can_execute_tasks","N");
			} 
			Task::executeNextTask();
		} 
		if (Option::get(self::$moduleID,"products_check_disable","N") != "Y") {
			$pass = (time() - Option::get(self::$moduleID,"products_check_last_time",0))/3600;
			if ($pass > Option::get(self::$moduleID,"products_check_period",0)) { 
				exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen") ."/bitrix/services/iplogic/mkpapi/products.php");
			}
		}
		if (Option::get(self::$moduleID,"keep_log_days",0) > 0) {
			$pass = (time() - Option::get(self::$moduleID,"log_clear_last_time",0))/86400;
			if ($pass > Option::get(self::$moduleID,"keep_log_days")) {
				$time = time() - (Option::get(self::$moduleID,"keep_log_days")*86400);
				ApiLog::clearOld($time);
				Option::set(self::$moduleID,"log_clear_last_time",time());
			}
		}
		if (Option::get(self::$moduleID,"keep_temp_files_days",0) > 0) {
			$pass = (time() - Option::get(self::$moduleID,"temp_files_clear_last_time",0))/86400;
			if ($pass > Option::get(self::$moduleID,"keep_temp_files_days")) {
				self::clearOldFiles();
				Option::set(self::$moduleID,"temp_files_clear_last_time",time());
			}
		}
		return "\Iplogic\Beru\Control::executeTasksAgent();";
	}


	/* Event handlers */


	public static function iblockAfterUpdateHandler($arFields) {
		self::setUpdateTask($arFields["ID"]);
	}


	public static function productUpdateHandler($ID, $arFields) {
		self::setUpdateTask($ID);
	}


	public static function priceUpdateHandler($obEvent) {
		$arEventParam = $obEvent->getParameters();
		$ID = $arEventParam["fields"]["PRODUCT_ID"];
		self::setUpdateTask($ID);
	}


	public static function storeUpdateHandler($ID, $arFields) {
		self::setUpdateTask($ID);
	}


	public static function productDeleteHandler($ID) {
		$rsProducts = Product::getByProductId($ID);
		while ($arProduct = $rsProducts->Fetch()) {
			if ($arProduct["API"] == "Y") {
				$arFields = [];
				$arFields["DETAILS"] = null;
				$arFields["FEED"] = "N";
				$arFields["PRODUCT_ID"] = 0;
				Product::update($arProduct["ID"],$arFields);
			}
			else {
				Product::delete($arProduct["ID"]);
			}
		}
	}


	protected static function setUpdateTask($ID) {
		$rsProducts = Product::getByProductId($ID);
		while ($arProduct = $rsProducts->Fetch()) {
			$rsTask = Task::getList(["filter"=>["TYPE"=>"PU","STATE"=>"WT","ENTITY_ID"=>$arProduct["ID"]]]);
			if (!$rsTask->Fetch()) {
				$arFields = [
					"PROFILE_ID" 		=> $arProduct["PROFILE_ID"],
					"UNIX_TIMESTAMP" 	=> time(),
					"TYPE" 				=> "PU",
					"STATE" 			=> "WT",
					"ENTITY_ID" 		=> $arProduct["ID"],
					"TRYING" 			=> 0
				];
				Task::add($arFields);
			}
		}
	}

}