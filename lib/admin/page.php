<?
/* //////////////////////////////////////////////////////////////////////////////
*
*	Clssses for bitrix administrative section 
*	iPloGic solutions
*
*	V 3.2.0   13.02.20
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


class Page {

	public 
		$module_id = "",
		$arOpts, 
		$Mess,
		$errors = [],
		$saved = "",
		$POST_RIGHT,
		$catalogIBlockID,
		$arElementFields,
		$arProductFields,
		$arContextMenu = false;

	protected
		$arUserGroups,
		$arProductProps,
		$arOfferProps,
		$arCommonProps,
		$arSites,
		$arIBlocksTypes,
		$arIBlocks,
		$arStores,
		$arPrices,
		$arDelivery,
		$arPayments,
		$arPersonTypes,
		$arOrderStatuses;


	function __construct($module_id) {
		global $APPLICATION;
		$this->module_id = $module_id;
		$this->POST_RIGHT = $APPLICATION->GetGroupRight($this->module_id);
	}


	public static function getLID() {
		global $request;
		if ( $request->get($option) != "" )
			return $request->get($option);
		else
			return "s1";
	}


	protected function validateOptions() {}


	public function getStandartArray($id) {
		switch ($id) {
			case "user_groups":
				$arOptions = $this->getUserGroups();
				break;
			case "product_props":
				$arOptions = $this->getProductProps();
				break;
			case "offer_props":
				$arOptions = $this->getOfferProps();
				break;
			case "common_props":
				$arOptions = $this->getCommonProps();
				break;
			case "sites":
				$arOptions = $this->getSites();
				break;
			case "iblock_types":
				$arOptions = $this->getIBlocksTypes();
				break;
			case "iblocks":
				$arOptions = $this->getIBlocks();
				break;
			case "stores":
				$arOptions = $this->getStores();
				break;
			case "element_fields":
				$arOptions = $this->getElementFields();
				break;
			case "product_fields":
				$arOptions = $this->getProductFields();
				break;
			case "prices":
				$arOptions = $this->getPrices();
				break;
			case "delivery":
				$arOptions = $this->getDelivery();
				break;
			case "payments":
				$arOptions = $this->getPayments();
				break;
			case "person_types":
				$arOptions = $this->getPersonTypes();
				break;
			case "order_statuses":
				$arOptions = $this->getOrderStatuses();
				break;
		}
		return $arOptions;
	}


	protected function getProductFields() {
		return $this->arProductFields;
	}


	protected function getElementFields() {
		return $this->arElementFields;
	}


	protected function getOrderStatuses(){
		if (is_array($this->arOrderStatuses) && count($this->arOrderStatuses)) {
			return $this->arOrderStatuses;
		}
		$result = \CSaleStatus::GetList();
		while ($arResult = $result->fetch()) {
			$this->arOrderStatuses[$arResult['ID']] = $arResult['NAME']." [".$arResult['ID']."]";
		}
		return $this->arOrderStatuses;
	}


	protected function getDelivery(){
		if (is_array($this->arDelivery) && count($this->arDelivery)) {
			return $this->arDelivery;
		}
		$result = \CSaleDelivery::GetList([], ["ACTIVE"=>"Y"]);
		while ($arResult = $result->fetch()) {
			$this->arDelivery[$arResult['ID']] = $arResult['NAME']." [".$arResult['ID']."]";
		}
		return $this->arDelivery;
	}


	protected function getPayments(){
		if (is_array($this->arPayments) && count($this->arPayments)) {
			return $this->arPayments;
		}
		$result = \CSalePaySystem::GetList([], ["ACTIVE"=>"Y"]);
		while ($arResult = $result->fetch()) {
			$this->arPayments[$arResult['ID']] = $arResult['NAME']." [".$arResult['ID']."]";
		}
		return $this->arPayments;
	}


	protected function getPersonTypes(){
		if (is_array($this->arPersonTypes) && count($this->arPersonTypes)) {
			return $this->arPersonTypes;
		}
		$result = \CSalePersonType::GetList([], ["ACTIVE"=>"Y"]);
		while ($arResult = $result->fetch()) {
			$this->arPersonTypes[$arResult['ID']] = $arResult['NAME']." [".$arResult['ID']."]";
		}
		return $this->arPersonTypes;
	}


	protected function getUserGroups() {
		if (is_array($this->arUserGroups) && count($this->arUserGroups)) {
			return $this->arUserGroups;
		}
		$result = \Bitrix\Main\GroupTable::getList(array(
			'select'  => ['NAME','ID'],
		));
		while ($arGroup = $result->fetch()) {
			$this->arUserGroups[$arGroup['ID']] = $arGroup['NAME'];
		}
		return $this->arUserGroups;
	}


	protected function getStores() {
		if (!is_array($this->arStores)) {
			$res = \CCatalogStore::GetList(['ID' => 'ASC'],["ACTIVE"=>"Y"]);
			$arStores = [];
			while ($res_arr = $res->Fetch()) {
				$arStores[$res_arr["ID"]] = $res_arr["TITLE"];
			}
			$this->arStores = $arStores;
		}
		return $this->arStores;
	}


	protected function getPrices() {
		if (!is_array($this->arPrices)) {
			$dbPriceType = \CCatalogGroup::GetList();
			while ($arPriceType = $dbPriceType->Fetch()) {
				$arPrices[$arPriceType["ID"]] = $arPriceType["NAME"];
			}
			$this->arPrices = $arPrices;
		}
		return $this->arPrices;
	}


	protected function getProductProps() {
		if (!is_array($this->arProductProps)) {
			$res = \CIBlock::GetProperties($this->catalogIBlockID);
			$arProps = [];
			while ($res_arr = $res->Fetch()) {
				$arProps[$res_arr["CODE"]] = $res_arr["NAME"];
			}
			$this->arProductProps = $arProps;
		}
		return $this->arProductProps;
	}


	protected function getOfferProps() {
		if (!is_array($this->arOfferProps)) {
			$arProdIBlock = \CCatalogSKU::GetInfoByProductIBlock($this->catalogIBlockID);
			if (is_array($arProdIBlock)) {
				$offerIBlockID = $arProdIBlock["IBLOCK_ID"];
				$res = \CIBlock::GetProperties($offerIBlockID);
				$arProps = [];
				while ($res_arr = $res->Fetch()) {
					$arProps[$res_arr["CODE"]] = $res_arr["NAME"];
				}
				$this->arOfferProps = $arProps;
			}
			else {
				$this->arOfferProps = [];
			}
		}
		return $this->arOfferProps;
	}


	protected function getCommonProps() {
		$res = [];
		$pr = $this->getProductProps();
		$of = $this->getOfferProps();
		foreach($of as $code => $name){
			if(array_key_exists($code, $pr)) {
				$res[$code] = $name;
			}
		}
		return $res;
	}


	protected function getSites() {
		if (!is_array($this->arSites)) {
			$res = \CSite::GetList($by="sort", $order="asc");
			$arSites = [];
			while ($res_arr = $res->Fetch()) {
				$arSites[$res_arr["ID"]] = $res_arr["NAME"];
			}
			$this->arSites = $arSites;
		}
		return $this->arSites;
	}


	protected function getIBlocksTypes() {
		if (!is_array($this->arIBlocksTypes)) {
			$arIBlocksTypes=["" => $this->Mess["ALL"]];
			$iblockTypes = \Bitrix\Iblock\TypeTable::getList(array('select' => array('*', 'LANG_MESSAGE')))->FetchAll();
			foreach($iblockTypes as $iblockType) {
				$arIBlocksTypes[$iblockType["ID"]] = "[".$iblockType["ID"]."] ".$iblockType["IBLOCK_TYPE_LANG_MESSAGE_NAME"];
			}
			$this->arIBlocksTypes = $arIBlocksTypes;
		}
		return $this->arIBlocksTypes;
	}


	protected function getIBlocks() {
		if (!is_array($this->arIBlocks)) {
			$arIBlocks=["0" => $this->Mess["NOT_CHOSEN"]];
			$arIBlocksJS=["0" => ["lid"=>"","type"=>""]];
			$res = \CIBlock::GetList(["SORT"=>"ASC"],['ACTIVE'=>'Y'], true);
			while($ar_res = $res->Fetch()) {
				$arIBlocks[$ar_res["ID"]] = "[".$ar_res["ID"]."] ".$ar_res["NAME"];
				$arIBlocksJS[$ar_res["ID"]] = ["lid"=>$ar_res["LID"],"type"=>$ar_res["IBLOCK_TYPE_ID"]];
			}
			$this->arIBlocks = $arIBlocks;
			$this->arIBlocksJS = $arIBlocksJS;
		}
		return $this->arIBlocks;
	}


	protected function contextMenuActions($arContext) {
		foreach($arContext as $key => $item) {
			if(isset($item["ACTION"])) {
				switch ($item["ACTION"]["TYPE"]) {
					case 'REDIRECT':
						$arContext[$key]["ACTION"] = str_replace("##ID##", $item["ACTION"]["ID"], $this->obList->ActionRedirect($item["ACTION"]["HREF"]));
						break;
					case 'DELETE':
						$arContext[$key]["ACTION"] = "if(confirm('".$this->Mess["DELETE_CONF"]."')) ".$this->obList->ActionDoGroup($item["ACTION"]["ID"], "delete", $item["ACTION"]["PARAMS"]);
						break;
				}
			}
		}
		return $arContext;
	}


	protected function getContextMenu() {
		if(count($this->arContextMenu)){ 
			foreach($this->arContextMenu as $key => $item) {
				if (isset($item["MENU"])){
					$this->arContextMenu[$key]["MENU"] = $this->contextMenuActions($item["MENU"]);
				}
			}
			if(is_set($this->arContextMenu[count($this->arContextMenu)-1], "SEPARATOR"))
				unset($this->arContextMenu[count($this->arContextMenu)-1]);
		}
		return $this->arContextMenu;
	}


	public static function hexToRgb($color) {
		if ($color[0] == '#') {
			$color = substr($color, 1);
		}
		if (strlen($color) == 6) {
			list($red, $green, $blue) = array(
				$color[0] . $color[1],
				$color[2] . $color[3],
				$color[4] . $color[5]
			);
		} elseif (strlen($cvet) == 3) {
			list($red, $green, $blue) = array(
				$color[0]. $color[0],
				$color[1]. $color[1],
				$color[2]. $color[2]
			);
		}else{
			return false; 
		}
		$red = hexdec($red); 
		$green = hexdec($green);
		$blue = hexdec($blue);
		return array(
			'red' => $red, 
			'green' => $green, 
			'blue' => $blue
		);
	}


	public static function colorFormat($s) {
		if (substr($s,0,1) != "#") {
			return "#".$s;
		}
		return $s;
	}

}