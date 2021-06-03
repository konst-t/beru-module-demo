<?php
namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");

/**
 * Class ConditionTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SORT int optional default 100
 * <li> TYPE string(2) mandatory
 * <li> PROP_TYPE string(255) mandatory
 * <li> PROP string(255) optional
 * <li> COND string(15) mandatory
 * <li> VALUE string(255) optional
 * <li> ACTION string(15) mandatory
 * <li> SET_VALUE1 string(255) optional
 * <li> SET_VALUE2 string(255) optional
 * <li> SET_VALUE3 string(255) optional
 * <li> DESCRIPTION string optional
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class ConditionTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_condition';
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
				'title' => Loc::getMessage('CONDITION_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('CONDITION_ENTITY_PROFILE_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CONDITION_ENTITY_ACTIVE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CONDITION_ENTITY_SORT_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('CONDITION_ENTITY_TYPE_FIELD'),
			),
			'PROP_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validatePropType'),
				'title' => Loc::getMessage('CONDITION_ENTITY_PROP_TYPE_FIELD'),
			),
			'PROP' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateProp'),
				'title' => Loc::getMessage('CONDITION_ENTITY_PROP_FIELD'),
			),
			'COND' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCond'),
				'title' => Loc::getMessage('CONDITION_ENTITY_COND_FIELD'),
			),
			'VALUE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONDITION_ENTITY_VALUE_FIELD'),
			),
			'ACTION' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateAction'),
				'title' => Loc::getMessage('CONDITION_ENTITY_ACTION_FIELD'),
			),
			'SET_VALUE1' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSetValue1'),
				'title' => Loc::getMessage('CONDITION_ENTITY_SET_VALUE1_FIELD'),
			),
			'SET_VALUE2' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSetValue2'),
				'title' => Loc::getMessage('CONDITION_ENTITY_SET_VALUE2_FIELD'),
			),
			'SET_VALUE3' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSetValue3'),
				'title' => Loc::getMessage('CONDITION_ENTITY_SET_VALUE3_FIELD'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CONDITION_ENTITY_DESCRIPTION_FIELD'),
			),
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
	 * Returns validators for PROP_TYPE field.
	 *
	 * @return array
	 */
	public static function validatePropType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for PROP field.
	 *
	 * @return array
	 */
	public static function validateProp()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for COND field.
	 *
	 * @return array
	 */
	public static function validateCond()
	{
		return array(
			new Main\Entity\Validator\Length(null, 15),
		);
	}
	/**
	 * Returns validators for ACTION field.
	 *
	 * @return array
	 */
	public static function validateAction()
	{
		return array(
			new Main\Entity\Validator\Length(null, 15),
		);
	}
	/**
	 * Returns validators for SET_VALUE1 field.
	 *
	 * @return array
	 */
	public static function validateSetValue1()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for SET_VALUE2 field.
	 *
	 * @return array
	 */
	public static function validateSetValue2()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for SET_VALUE3 field.
	 *
	 * @return array
	 */
	public static function validateSetValue3()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	public static function getById($ID) 
	{
		$result = parent::getById($ID);
		return $result->Fetch();
	}
}