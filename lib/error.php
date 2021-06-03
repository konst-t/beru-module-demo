<?
namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Application,
	\Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");


/**
 * Class ErrorTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> UNIX_TIMESTAMP int mandatory
 * <li> HUMAN_TIME string(19) mandatory
 * <li> ERROR string(255) optional
 * <li> DETAILS string mandatory
 * <li> STATE string(2) mandatory
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class ErrorTable extends Main\Entity\DataManager
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_error';
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
				'title' => Loc::getMessage('ERROR_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ERROR_ENTITY_PROFILE_ID_FIELD'),
			),
			'UNIX_TIMESTAMP' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ERROR_ENTITY_UNIX_TIMESTAMP_FIELD'),
			),
			'HUMAN_TIME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateHumanTime'),
				'title' => Loc::getMessage('ERROR_ENTITY_HUMAN_TIME_FIELD'),
			),
			'ERROR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateError'),
				'title' => Loc::getMessage('ERROR_ENTITY_ERROR_FIELD'),
			),
			'DETAILS' => array(
				'data_type' => 'text',
				'required' => true,
				'title' => Loc::getMessage('ERROR_ENTITY_DETAILS_FIELD'),
			),
			'STATE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateState'),
				'title' => Loc::getMessage('ERROR_ENTITY_STATE_FIELD'),
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


	public static function add(array $arFields) {
		$arFields["UNIX_TIMESTAMP"] = time();
		$arFields["HUMAN_TIME"] = date('d.m.Y H:i:s');
		$arFields["STATE"] = "NW";
		return parent::add($arFields);
	}


	public static function getById($ID) 
	{
		$result = parent::getById($ID);
		return $result->Fetch();
	}


	public static function newCount() {
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT COUNT(*) AS count FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('STATE')."='NW'"; 
		$result = $conn->query($strSql);
		unset($helper, $conn);
		$ar_res = $result->Fetch();
		return $ar_res["count"];
	}


	public static function clearRead() {
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "DELETE FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('STATE')."='RD'"; 
		$result = $conn->query($strSql);
		unset($helper, $conn);
		return true;
	}


	public static function allRead() {
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "UPDATE ".$helper->quote(self::getTableName())." SET ".$helper->quote('STATE')."='RD' WHERE ".$helper->quote('STATE')."='NW'"; 
		$result = $conn->query($strSql);
		unset($helper, $conn);
		return true;
	}


}


