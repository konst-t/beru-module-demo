<?
namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");


/**
 * Class ProfileTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 100
 * <li> SITE string(2) mandatory
 * <li> IBLOCK_TYPE string(50) mandatory
 * <li> IBLOCK_ID int mandatory
 * <li> USE_API bool optional default 'Y'
 * <li> USE_FEED bool optional default 'Y'
 * <li> COMPANY string(255) optional
 * <li> TAX_SYSTEM string(14) optional
 * <li> VAT string(6) optional
 * <li> BASE_URL string(100) optional
 * <li> CLIENT_ID string(255) optional
 * <li> COMPAIN_ID string(100) optional
 * <li> SEND_TOKEN string(255) optional
 * <li> GET_TOKEN string(255) optional
 * <li> STICKER_DELIVERY string(50) optional
 * <li> USER_ID int optional
 * <li> DELIVERY int optional
 * <li> PAYMENTS int optional
 * <li> PERSON_TYPE int optional
 * <li> STATUSES string optional
 * <li> STICKER_LOGO int optional
 * <li> YML_FROM_MARKET bool optional default 'N'
 * <li> YML_FILENAME string(255) optional
 * <li> YML_NAME string(255) optional
 * <li> YML_URL string(255) optional
 * <li> ENABLE_AUTO_DISCOUNTS bool optional default 'N'
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class ProfileTable extends Main\Entity\DataManager 
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_profile';
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
				'title' => Loc::getMessage('PROFILE_ENTITY_ID_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('PROFILE_ENTITY_NAME_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PROFILE_ENTITY_ACTIVE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PROFILE_ENTITY_SORT_FIELD'),
			),
			'SITE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateSite'),
				'title' => Loc::getMessage('PROFILE_ENTITY_SITE_FIELD'),
			),
			'IBLOCK_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateIblockType'),
				'title' => Loc::getMessage('PROFILE_ENTITY_IBLOCK_TYPE_FIELD'),
			),
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('PROFILE_ENTITY_IBLOCK_ID_FIELD'),
			),
			'USE_API' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PROFILE_ENTITY_USE_API_FIELD'),
			),
			'USE_FEED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PROFILE_ENTITY_USE_FEED_FIELD'),
			),
			'COMPANY' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCompany'),
				'title' => Loc::getMessage('PROFILE_ENTITY_COMPANY_FIELD'),
			),
			'TAX_SYSTEM' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTaxSystem'),
				'title' => Loc::getMessage('PROFILE_ENTITY_TAX_SYSTEM_FIELD'),
			),
			'VAT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVat'),
				'title' => Loc::getMessage('PROFILE_ENTITY_VAT_FIELD'),
			),
			'BASE_URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBaseUrl'),
				'title' => Loc::getMessage('PROFILE_ENTITY_BASE_URL_FIELD'),
			),
			'CLIENT_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateClientId'),
				'title' => Loc::getMessage('PROFILE_ENTITY_CLIENT_ID_FIELD'),
			),
			'COMPAIN_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCompainId'),
				'title' => Loc::getMessage('PROFILE_ENTITY_COMPAIN_ID_FIELD'),
			),
			'SEND_TOKEN' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSendToken'),
				'title' => Loc::getMessage('PROFILE_ENTITY_SEND_TOKEN_FIELD'),
			),
			'GET_TOKEN' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateGetToken'),
				'title' => Loc::getMessage('PROFILE_ENTITY_GET_TOKEN_FIELD'),
			),
			'STICKER_DELIVERY' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateStickerDelivery'),
				'title' => Loc::getMessage('PROFILE_ENTITY_STICKER_DELIVERY_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PROFILE_ENTITY_USER_ID_FIELD'),
			),
			'DELIVERY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PROFILE_ENTITY_DELIVERY_FIELD'),
			),
			'PAYMENTS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PROFILE_ENTITY_PAYMENTS_FIELD'),
			),
			'PERSON_TYPE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PROFILE_ENTITY_PERSON_TYPE_FIELD'),
			),
			'STATUSES' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('PROFILE_ENTITY_STATUSES_FIELD'),
			),
			'STICKER_LOGO' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('PROFILE_ENTITY_STICKER_LOGO_FIELD'),
			),
			'YML_FROM_MARKET' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PROFILE_ENTITY_YML_FROM_MARKET_FIELD'),
			),
			'YML_FILENAME' => array(
				'data_type' => 'text',
				'validation' => array(__CLASS__, 'validateYmlFilename'),
				'title' => Loc::getMessage('PROFILE_ENTITY_YML_FILENAME_FIELD'),
			),
			'YML_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateYmlName'),
				'title' => Loc::getMessage('PROFILE_ENTITY_YML_NAME_FIELD'),
			),
			'YML_URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateYmlUrl'),
				'title' => Loc::getMessage('PROFILE_ENTITY_YML_URL_FIELD'),
			),
			'ENABLE_AUTO_DISCOUNTS' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PROFILE_ENTITY_ENABLE_AUTO_DISCOUNTS_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for SITE field.
	 *
	 * @return array
	 */
	public static function validateSite()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
		);
	}
	/**
	 * Returns validators for IBLOCK_TYPE field.
	 *
	 * @return array
	 */
	public static function validateIblockType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for COMPANY field.
	 *
	 * @return array
	 */
	public static function validateCompany()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for TAX_SYSTEM field.
	 *
	 * @return array
	 */
	public static function validateTaxSystem()
	{
		return array(
			new Main\Entity\Validator\Length(null, 14),
		);
	}
	/**
	 * Returns validators for VAT field.
	 *
	 * @return array
	 */
	public static function validateVat()
	{
		return array(
			new Main\Entity\Validator\Length(null, 6),
		);
	}
	/**
	 * Returns validators for BASE_URL field.
	 *
	 * @return array
	 */
	public static function validateBaseUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for CLIENT_ID field.
	 *
	 * @return array
	 */
	public static function validateClientId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for COMPAIN_ID field.
	 *
	 * @return array
	 */
	public static function validateCompainId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}
	/**
	 * Returns validators for SEND_TOKEN field.
	 *
	 * @return array
	 */
	public static function validateSendToken()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for GET_TOKEN field.
	 *
	 * @return array
	 */
	public static function validateGetToken()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for STICKER_DELIVERY field.
	 *
	 * @return array
	 */
	public static function validateStickerDelivery()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for YML_FILENAME field.
	 *
	 * @return array
	 */
	public static function validateYmlFilename()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for YML_NAME field.
	 *
	 * @return array
	 */
	public static function validateYmlName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for YML_URL field.
	 *
	 * @return array
	 */
	public static function validateYmlUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}


	public static function getById($ID, $short = false) 
	{
		global $DB;
		$result = parent::getById($ID);
		if($arFields = $result->Fetch()){
			$arFields["STATUSES"] = unserialize($arFields["STATUSES"]);
			if (!$short) {
				$conn = Application::getConnection(); 
				$helper = $conn->getSqlHelper();
				$strSql = "SELECT * FROM b_iplogicberu_prop WHERE ".$helper->quote('PROFILE_ID')." = ".$ID."";
				$result = $conn->query($strSql);
				unset($helper, $conn);
				$arFields["PROP"] = [];
				while ($arPropFields = $result->Fetch()) {
					unset($arPropFields["PROFILE_ID"]);
					$arFields["PROP"][$arPropFields["NAME"]] = $arPropFields;
				}
			}
			return $arFields;
		}
		return false;
	}


	public static function delete($ID) 
	{
		$arBefore = self::getById($ID, true);
		$result = parent::delete($ID);
		if ($result->isSuccess()) {
			$conn = Application::getConnection(); 
			$helper = $conn->getSqlHelper();
			$conn->query("DELETE FROM b_iplogicberu_prop WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_attr WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_order WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_api_log WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_task WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_error WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_product WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_box WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_box_link WHERE PROFILE_ID=".$ID);
			$conn->query("DELETE FROM b_iplogicberu_condition WHERE PROFILE_ID=".$ID);
			unset($helper, $conn);
		}

		\CUrlRewriter::Delete([
			'CONDITION' => '#^'.$arBefore["BASE_URL"].'#',
		]);

		if ($result->isSuccess())
			return $result;
		else
			return ["error"=>$result->getErrorMessages()];
	}


	public static function update($ID, array $arFields) 
	{
		$ID = intval($ID);
		if($ID < 1)
			return false;
		$arBefore = self::getById($ID, true);
		$arFields["STATUSES"] = serialize($arFields["STATUSES"]);
		$arFields["YML_FILENAME"] = ($arFields["YML_FILENAME"][0]=="/" ? $arFields["YML_FILENAME"] : "/".$arFields["YML_FILENAME"]);
		$result = parent::update($ID, $arFields);
		if ($result->isSuccess()) {
			if ((!isset($arFields["BASE_URL"]) || $arFields["BASE_URL"]=="" || $arFields["USE_API"]!="Y") && $arBefore["BASE_URL"] != "") {
				\CUrlRewriter::Delete([
					'CONDITION' => '#^'.$arBefore["BASE_URL"].'#',
				]);
			}
			elseif (isset($arFields["BASE_URL"]) && $arBefore["BASE_URL"] != $arFields["BASE_URL"]){
				\CUrlRewriter::Delete([
					'CONDITION' => '#^'.$arBefore["BASE_URL"].'#',
				]);
				\Bitrix\Main\UrlRewriter::add($arFields["SITE"], [
					"CONDITION" => "#^".$arFields["BASE_URL"]."#",
					"RULE" => "",
					"ID" => "iplogic:beru",
					"PATH" => "/bitrix/services/iplogic/mkpapi/index.php",
					"SORT" => 100
				]);
			}
		}
		if ($result->isSuccess())
			return $result;
		else
			return ["error"=>$result->getErrorMessages()];
	}


	public static function add(array $arFields)
	{
		if (isset($arFields["ID"])) {
			if ( self::getById($arFields["ID"], true) ) {
				return self::update($arFields["ID"],$arFields);
			}
		}
		$arFields["STATUSES"] = serialize($arFields["STATUSES"]);
		$arFields["YML_FILENAME"] = ($arFields["YML_FILENAME"][0]=="/" ? $arFields["YML_FILENAME"] : "/".$arFields["YML_FILENAME"]);
		$result = parent::add($arFields);

		if ($result->isSuccess()) {
			if ($arFields["BASE_URL"]!="" && $arFields["USE_API"]=="Y") {
				\Bitrix\Main\UrlRewriter::add($arFields["SITE"], [
					"CONDITION" => "#^".$arFields["BASE_URL"]."#",
					"RULE" => "",
					"ID" => "iplogic:beru",
					"PATH" => "/bitrix/services/iplogic/mkpapi/index.php",
					"SORT" => 100
				]);
			}
			return $result->getId();
		}
		return ["error"=>$result->getErrorMessages()];
	}


	public static function setAccordance($PROFILE_ID, $arFields) {
		global $DB;
		if ($arFields["TYPE"] == "permanent_text") {
			$arFields["VALUE"] = $arFields["TEXT_VALUE"];
		}
		unset($arFields["TEXT_VALUE"]);
		$arFields["PROFILE_ID"] = $PROFILE_ID;
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		if (is_int($arFields["ID"]) && $arFields["ID"]>0) {
			$ID = intval($arFields["ID"]);
			if ($arFields["TYPE"] == "empty") {
				$conn->query("DELETE FROM b_iplogicberu_prop WHERE ID=".$ID);
			}
			else {
				$strUpdate = $DB->PrepareUpdate("b_iplogicberu_prop", $arFields);
				if($strUpdate!="")
				{
					$conn->query("UPDATE b_iplogicberu_prop SET ".$strUpdate." WHERE ID=".$ID);
				}
			}
		}
		else {
			if ($arFields["TYPE"] != "empty") {
				$DB->Add("b_iplogicberu_prop", $arFields);
			}
		}
		unset($helper, $conn);
		return true;
	}

}


