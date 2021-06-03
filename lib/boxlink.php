<?php

namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Application,
	\Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");

/**
 * Class BoxLinkTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> ORDER_ID int mandatory
 * <li> BOX_ID int mandatory
 * <li> SKU_ID string(150) mandatory
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class BoxLinkTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_box_link';
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
				'title' => Loc::getMessage('BOX_LINK_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_LINK_ENTITY_PROFILE_ID_FIELD'),
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_LINK_ENTITY_ORDER_ID_FIELD'),
			),
			'BOX_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOX_LINK_ENTITY_BOX_ID_FIELD'),
			),
			'SKU_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateSkuId'),
				'title' => Loc::getMessage('BOX_LINK_ENTITY_SKU_ID_FIELD'),
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
}