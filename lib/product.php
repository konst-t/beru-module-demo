<?php

namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Application,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Web\Json,
	\Bitrix\Main\Config\Option,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\YMAPI,
	\Iplogic\Beru\TaskTable,
	\Iplogic\Beru\ProfileTable;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");

/**
 * Class ProductTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> PRODUCT_ID int optional
 * <li> SKU_ID string(150) optional
 * <li> MARKET_SKU int optional
 * <li> NAME string optional
 * <li> VENDOR string(255) optional
 * <li> AVAILABILITY bool optional default 'N'
 * <li> STATE string(12) optional
 * <li> REJECT_REASON string(255) optional
 * <li> REJECT_NOTES string optional
 * <li> DETAILS string optional
 * <li> PRICE string(12) optional
 * <li> HIDDEN bool optional default 'N'
 * <li> API bool optional default 'N'
 * <li> FEED bool optional default 'N'
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class ProductTable extends Main\Entity\DataManager
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_product';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('PRODUCT_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('PRODUCT_ENTITY_PROFILE_ID_FIELD'),
			),
			'PRODUCT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PRODUCT_ENTITY_PRODUCT_ID_FIELD'),
			),
			'SKU_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSkuId'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_SKU_ID_FIELD'),
			),
			'MARKET_SKU' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PRODUCT_ENTITY_MARKET_SKU_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('PRODUCT_ENTITY_NAME_FIELD'),
			),
			'VENDOR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVendor'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_VENDOR_FIELD'),
			),
			'AVAILABILITY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_AVAILABILITY_FIELD'),
			),
			'STATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateState'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_STATE_FIELD'),
			),
			'REJECT_REASON' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateRejectReason'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_REJECT_REASON_FIELD'),
			),
			'REJECT_NOTES' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('PRODUCT_ENTITY_REJECT_NOTES_FIELD'),
			),
			'DETAILS' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('PRODUCT_ENTITY_DETAILS_FIELD'),
			),
			'PRICE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validatePrice'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_PRICE_FIELD'),
			),
			'HIDDEN' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_HIDDEN_FIELD'),
			),
			'API' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_API_FIELD'),
			),
			'FEED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PRODUCT_ENTITY_FEED_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for SKU_ID field.
	 *
	 * @return array
	 */
	public static function validateSkuId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 150),
		);
	}
	/**
	 * Returns validators for VENDOR field.
	 *
	 * @return array
	 */
	public static function validateVendor()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for STATE field.
	 *
	 * @return array
	 */
	public static function validateState()
	{
		return array(
			new Main\Entity\Validator\Length(null, 12),
		);
	}
	/**
	 * Returns validators for REJECT_REASON field.
	 *
	 * @return array
	 */
	public static function validateRejectReason()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for PRICE field.
	 *
	 * @return array
	 */
	public static function validatePrice()
	{
		return array(
			new Main\Entity\Validator\Length(null, 12),
		);
	}


	public static function getById($ID) 
	{
		$result = parent::getById($ID);
		return $result->Fetch();
	}


	public static function getBySkuId($ID, $PROFILE_ID) 
	{
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT * FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('SKU_ID')."='".$ID."' AND ".$helper->quote('PROFILE_ID')."=".$PROFILE_ID;  //echo $strSql;
		$result = $conn->query($strSql);
		unset($helper, $conn);
		return $result->Fetch();
	}


	public static function getByProductId($ID) 
	{
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT * FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('PRODUCT_ID')."='".$ID."'";  //echo $strSql;
		$result = $conn->query($strSql);
		unset($helper, $conn);
		return $result;
	}


	public static function checkMarketProducts($profile_id = false, $page_token = false) 
	{ 
		$rsProfiles = ProfileTable::getList(["order"=>["ID"=>"ASC"], "filter"=>["ACTIVE"=>"Y"]]);
		if (!$profile_id) {
			$arProfile = $rsProfiles->Fetch();
			if (!$arProfile) return;
			Option::set(self::$moduleID,"products_check_last_time",time());
		}
		elseif ($profile_id && $page_token != "") {
			while ($ar_Profile = $rsProfiles->Fetch()) {
				if ($ar_Profile["ID"] == $profile_id) {
					$arProfile = $ar_Profile;
					break;
				}
			}
		}
		else {
			while ($ar_Profile = $rsProfiles->Fetch()) {
				if ($ar_Profile["ID"] == $profile_id) {
					$arProfile = $rsProfiles->Fetch();
					break;
				}
			}
			if (!$arProfile) {
				Option::set(self::$moduleID,"products_check_last_time",time());
			}
		}
		if (!$arProfile) {
			return;
		}

		if (   $arProfile["USE_API"] == "Y"
			&& $arProfile["CLIENT_ID"] != ""
			&& $arProfile["COMPAIN_ID"] != ""
			&& $arProfile["SEND_TOKEN"] != ""
		) {
			$con = new Control();
			$con->arProfile = ProfileTable::getById($arProfile["ID"]);

			$arHidden = [];
			$api = new YMAPI($arProfile["ID"]);
			$result = $api->getHidden();
			foreach($result["body"]["result"]["hiddenOffers"] as $offer) {
				$arHidden[] = $offer["marketSku"];
			}

			$arParams = ["limit"=>Option::get(self::$moduleID,"products_add_num",50)];
			for ($i=0; $i < 5; $i++) {
				if ($page_token) {
					$arParams["page_token"] = $page_token;
				}
				$api = new YMAPI($arProfile["ID"]);
				$result = $api->getOffersMapping($arParams); 
				if ($result["status"] != 200)
					return;
				if (!count($result["body"]["result"]["offerMappingEntries"]))
					return;
				foreach($result["body"]["result"]["offerMappingEntries"] as $offer) {
					$id = $con->getProductId($offer["offer"]["shopSku"]);
					$market_sku = null;
					if (isset($offer["mapping"])) {
						$market_sku = $offer["mapping"]["marketSku"];
					}
					elseif (isset($offer["awaitingModerationMapping"])) {
						$market_sku = $offer["awaitingModerationMapping"]["marketSku"];
					}
					elseif (isset($offer["rejectedMapping"])) {
						$market_sku = $offer["rejectedMapping"]["marketSku"];
					}
					$hidden = "N";
					if ($market_sku && in_array($market_sku, $arHidden)) {
						$hidden = "Y";
					}
					$arReason = [];
					$arNote = [];
					foreach($offer["offer"]["processingState"]["notes"] as $rn) {
						$arReason[] = $rn["type"];
						$n = Json::decode($rn["payload"]);
						$arNote[] = $n["itemsAsString"];
					}
					if (count($arReason))
						$stReason = implode(", ", $arReason);
					else
						$stReason = null;
					if (count($arNote))
						$stNote = implode(". ", $arNote);
					else
						$stNote = null;
					$arFields = [
						"PROFILE_ID" => $arProfile["ID"],
						"PRODUCT_ID" => $id,
						"SKU_ID" => $offer["offer"]["shopSku"],
						"MARKET_SKU" => $market_sku,
						"NAME" => $offer["offer"]["name"],
						"VENDOR" => $offer["offer"]["vendor"],
						"AVAILABILITY" => ($offer["offer"]["availability"]=="ACTIVE" ? "Y" : "N"),
						"STATE" => $offer["offer"]["processingState"]["status"],
						"REJECT_REASON" => $stReason,
						"REJECT_NOTES" => $stNote,
						"API" => "Y",
						"HIDDEN" => $hidden,
					]; 
					$res = self::getList(["filter"=>["PROFILE_ID"=>$arProfile["ID"], "SKU_ID"=>$offer["offer"]["shopSku"]]]);
					if ($pr = $res->Fetch())
						self::update($pr["ID"],$arFields);
					else
						self::add($arFields);
				}
				if ($result["body"]["result"]["paging"]["nextPageToken"] != "") {
					$page_token = $result["body"]["result"]["paging"]["nextPageToken"];
				}
				else {
					$page_token = "";
				}
			}
		}

		exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen") ."/bitrix/services/iplogic/mkpapi/products.php?param=".$arProfile["ID"]."__".$page_token);
		die();

	}


	public static function updateCache($ID) {
		if ($product = self::getById($ID)) { 
			$con = new Control($product["PROFILE_ID"]);
			$set = $con->getSKU($product["SKU_ID"], [], true); 
			if ($product["PRICE"]!=$set["PRICE"] && $set["PRICE"]>0 && $product["API"] == "Y") {
				TaskTable::addPriceUpdateTask($ID,$product["PROFILE_ID"]);
			}
			if ($product["FEED"] == "Y") {
				TaskTable::scheduleFeedGeneration($product["PROFILE_ID"]);
			}
			$eventManager = Main\EventManager::getInstance();
			$eventsList = $eventManager->findEventHandlers('iplogic.beru', 'OnIplogicBeruBeforeProductCacheSave');
			foreach ($eventsList as $arEvent) { 
				if (ExecuteModuleEventEx($arEvent, [$product["PRODUCT_ID"], &$set])===false) 
					return false;
			}
			$cache = serialize($set);
			$arFields = ["DETAILS"=>$cache];
			return self::update($ID,$arFields);
		}
		return false;
	}

}