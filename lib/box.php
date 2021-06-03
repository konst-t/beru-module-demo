<?php

namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Application,
	\Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");

/**
 * Class BoxTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> ORDER_ID int mandatory
 * <li> NUM int mandatory
 * <li> WEIGHT int mandatory
 * <li> WIDTH int mandatory
 * <li> HEIGHT int mandatory
 * <li> DEPTH int mandatory
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class BoxTable extends Main\Entity\DataManager
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_box';
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
				'title' => Loc::getMessage('BOX_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_PROFILE_ID_FIELD'),
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_ORDER_ID_FIELD'),
			),
			'NUM' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_NUM_FIELD'),
			),
			'WEIGHT' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_WEIGHT_FIELD'),
			),
			'WIDTH' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_WIDTH_FIELD'),
			),
			'HEIGHT' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_HEIGHT_FIELD'),
			),
			'DEPTH' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_ENTITY_DEPTH_FIELD'),
			),
		);
	}

	public static function getById($ID) 
	{
		$result = parent::getById($ID);
		return $result->Fetch();
	}

	public static function getCountInOrder($ID) 
	{
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT COUNT(*) AS count FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('ORDER_ID')."=".$ID;
		$result = $conn->query($strSql);
		unset($helper, $conn);
		$ar_res = $result->Fetch();
		return $ar_res["count"];
	}
}