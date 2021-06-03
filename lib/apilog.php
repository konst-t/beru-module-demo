<?
namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");


/**
 * Class ApiLogTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> UNIX_TIMESTAMP int mandatory
 * <li> HUMAN_TIME string(19) mandatory
 * <li> TYPE string(2) mandatory
 * <li> STATE string(2) mandatory
 * <li> URL string(255) mandatory
 * <li> REQUEST_TYPE string(6) mandatory
 * <li> REQUEST string mandatory
 * <li> REQUEST_H string optional
 * <li> RESPOND string optional
 * <li> RESPOND_H string optional
 * <li> STATUS int optional
 * <li> ERROR string(255) optional
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class ApiLogTable extends Main\Entity\DataManager
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_api_log';
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
				'title' => Loc::getMessage('API_LOG_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('API_LOG_ENTITY_PROFILE_ID_FIELD'),
			),
			'UNIX_TIMESTAMP' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('API_LOG_ENTITY_UNIX_TIMESTAMP_FIELD'),
			),
			'HUMAN_TIME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateHumanTime'),
				'title' => Loc::getMessage('API_LOG_ENTITY_HUMAN_TIME_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('API_LOG_ENTITY_TYPE_FIELD'),
			),
			'STATE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateState'),
				'title' => Loc::getMessage('API_LOG_ENTITY_STATE_FIELD'),
			),
			'URL' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateUrl'),
				'title' => Loc::getMessage('API_LOG_ENTITY_URL_FIELD'),
			),
			'REQUEST_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateRequestType'),
				'title' => Loc::getMessage('API_LOG_ENTITY_REQUEST_TYPE_FIELD'),
			),
			'REQUEST' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('API_LOG_ENTITY_REQUEST_FIELD'),
			),
			'REQUEST_H' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('API_LOG_ENTITY_REQUEST_H_FIELD'),
			),
			'RESPOND' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('API_LOG_ENTITY_RESPOND_FIELD'),
			),
			'RESPOND_H' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('API_LOG_ENTITY_RESPOND_H_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('API_LOG_ENTITY_STATUS_FIELD'),
			),
			'ERROR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateError'),
				'title' => Loc::getMessage('API_LOG_ENTITY_ERROR_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for HUMAN_TIME field.
	 *
	 * @return array
	 */
	public static function validateHumanTime()
	{
		return array(
			new Main\Entity\Validator\Length(null, 19),
		);
	}
	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
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
			new Main\Entity\Validator\Length(null, 2),
		);
	}
	/**
	 * Returns validators for URL field.
	 *
	 * @return array
	 */
	public static function validateUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for REQUEST_TYPE field.
	 *
	 * @return array
	 */
	public static function validateRequestType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 6),
		);
	}
	/**
	 * Returns validators for ERROR field.
	 *
	 * @return array
	 */
	public static function validateError()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}


	public static function add(array $arFields) {
		$arFields["UNIX_TIMESTAMP"] = time();
		$arFields["HUMAN_TIME"] = date('d.m.Y H:i:s');
		$arFields["STATE"] = "EX";
		return parent::add($arFields);
	}


	public static function update($ID, array $arFields) {
		if ($arFields["close"]) {
			if ( Option::get(self::$moduleID, 'use_log', 'Y') == "N" ) {
				return self::delete($ID);
			}
			if ( Option::get(self::$moduleID, 'dont_log_ok', 'N') == "Y" && $arFields["STATE"] == "OK" ) {
				return self::delete($ID);
			}
		} 
		return parent::update($ID,$arFields);
	}


	public static function getById($ID) 
	{
		$result = parent::getById($ID);
		return $result->Fetch();
	}


	public static function clear() {
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "TRUNCATE TABLE ".$helper->quote(self::getTableName());
		$rsData = $conn->query($strSql);
		unset($helper, $conn);
		return true;
	}

	public static function clearOld($time) {
		if($time<1) return false;
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "DELETE FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('UNIX_TIMESTAMP')."<".$time;
		$rsData = $conn->query($strSql);
		unset($helper, $conn);
		return true;
	}

}


