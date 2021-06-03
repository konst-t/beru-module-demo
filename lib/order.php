<?

namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Application,
	\Bitrix\Main\Localization\Loc,
	\Iplogic\Beru\YMAPI,
	\Iplogic\Beru\BoxTable,
	\Iplogic\Beru\BoxLinkTable,
	\Iplogic\Beru\ProfileTable;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");


/**
 * Class OrderTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> EXT_ID int mandatory
 * <li> ORDER_ID int mandatory
 * <li> STATE string(255) mandatory
 * <li> STATE_CODE string(255) optional
 * <li> UNIX_TIMESTAMP int mandatory
 * <li> HUMAN_TIME string(19) mandatory
 * <li> FAKE bool optional default 'N'
 * <li> SHIPMENT_ID int optional
 * <li> SHIPMENT_DATE string(10) optional
 * <li> SHIPMENT_TIMESTAMP int optional
 * <li> DELIVERY_NAME string(255) optional
 * <li> DELIVERY_ID string(255) optional
 * <li> BOXES_SENT bool optional default 'N'
 * <li> READY_TIME int optional
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class OrderTable extends Main\Entity\DataManager
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_order';
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
				'title' => Loc::getMessage('ORDER_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ORDER_ENTITY_PROFILE_ID_FIELD'),
			),
			'EXT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ORDER_ENTITY_EXT_ID_FIELD'),
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ORDER_ENTITY_ORDER_ID_FIELD'),
			),
			'STATE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateState'),
				'title' => Loc::getMessage('ORDER_ENTITY_STATE_FIELD'),
			),
			'STATE_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateStateCode'),
				'title' => Loc::getMessage('ORDER_ENTITY_STATE_CODE_FIELD'),
			),
			'UNIX_TIMESTAMP' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ORDER_ENTITY_UNIX_TIMESTAMP_FIELD'),
			),
			'HUMAN_TIME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateHumanTime'),
				'title' => Loc::getMessage('ORDER_ENTITY_HUMAN_TIME_FIELD'),
			),
			'FAKE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('ORDER_ENTITY_FAKE_FIELD'),
			),
			'SHIPMENT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('ORDER_ENTITY_SHIPMENT_ID_FIELD'),
			),
			'SHIPMENT_DATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateShipmentDate'),
				'title' => Loc::getMessage('ORDER_ENTITY_SHIPMENT_DATE_FIELD'),
			),
			'SHIPMENT_TIMESTAMP' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('ORDER_ENTITY_SHIPMENT_TIMESTAMP_FIELD'),
			),
			'DELIVERY_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDeliveryName'),
				'title' => Loc::getMessage('ORDER_ENTITY_DELIVERY_NAME_FIELD'),
			),
			'DELIVERY_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDeliveryId'),
				'title' => Loc::getMessage('ORDER_ENTITY_DELIVERY_ID_FIELD'),
			),
			'BOXES_SENT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('ORDER_ENTITY_BOXES_SENT_FIELD'),
			),
			'READY_TIME' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('ORDER_ENTITY_READY_TIME_FIELD'),
			),
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
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for STATE_CODE field.
	 *
	 * @return array
	 */
	public static function validateStateCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
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
	 * Returns validators for SHIPMENT_DATE field.
	 *
	 * @return array
	 */
	public static function validateShipmentDate()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	/**
	 * Returns validators for DELIVERY_NAME field.
	 *
	 * @return array
	 */
	public static function validateDeliveryName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for DELIVERY_ID field.
	 *
	 * @return array
	 */
	public static function validateDeliveryId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}


	public static function add(array $arFields) {
		$arFields["UNIX_TIMESTAMP"] = time();
		$arFields["HUMAN_TIME"] = date('d.m.Y H:i:s');
		$arFields["STATE"] = "S_NEW";
		return parent::add($arFields);
	}


	public static function getById($ID) 
	{
		$result = parent::getById($ID);
		return $result->Fetch();
	}


	public static function check($id) {
		$id = (int)$id;
		if ($id <= 0)
			return;
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT * FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('EXT_ID')."=".$id;
		$rsData = $conn->query($strSql);
		unset($helper, $conn);
		$arFields = $rsData->Fetch();
		return $arFields["ORDER_ID"];
	}


	public static function sendOrderBoxes($id) {
		$arOrder = self::getById($id);
		if(!$arOrder) {
			return ["status"=>0, "error"=>"Can't find order"];
		}
		$arProfile = ProfileTable::getById($arOrder["PROFILE_ID"]);
		if(!$arProfile) {
			return ["status"=>0, "error"=>"Can't find profile"];
		}
		$api = new YMAPI($arOrder["PROFILE_ID"]);
		$rsBoxes = BoxTable::getList(["filter"=>["ORDER_ID"=>$id],"order"=>["NUM"=>"ASC"]]);
		while ($arBox = $rsBoxes->Fetch()) {
			$arBoxes[] = $arBox;
		}
		$rsLinks = BoxLinkTable::getList(["filter"=>["ORDER_ID"=>$id]]);
		$boxes = [];

		for ($i=0; $i<5; $i++) {
			$res = $api->getOrders(["orderId"=>$arOrder["EXT_ID"]]); 
			if ($res["status"]==200)
				break;
			sleep(1);
		}
		if ($res["status"]!=200)
			return ["status"=>$res["status"], "error"=>$res["body"]["errors"][0]["message"]];
		else
			$arMrktOrder = $res["body"];
		$arMktProductsT = $arMrktOrder["order"]["items"]; 
		$arMktProducts = [];
		foreach($arMktProductsT as $prod) {
			$arMktProducts[$prod["offerId"]] = $prod["id"];
		}
		while ($arLink = $rsLinks->Fetch()) {
			$arLinks[] = $arLink;
		}
		foreach($arBoxes as $arBox){
			$box = [
				"fulfilmentId" 	=> $arOrder["EXT_ID"]."-".$arBox["NUM"],
				"weight" 		=> (int)$arBox["WEIGHT"],
				"width" 		=> (int)$arBox["WIDTH"],
				"height" 		=> (int)$arBox["HEIGHT"],
				"depth" 		=> (int)$arBox["DEPTH"],
			];
			$titems = [];
			foreach($arLinks as $arLink) {
				if ( $arLink["BOX_ID"] == $arBox["ID"] ) {
					if (isset($titems[$arLink["SKU_ID"]])) {
						$titems[$arLink["SKU_ID"]]["count"]++;
					}
					else {
						$titems[$arLink["SKU_ID"]] = ["id"=>$arMktProducts[$arLink["SKU_ID"]], "count"=>1];
					}
				}
			}
			if (!count($titems))
				continue;
			foreach($titems as $item) {
				$box["items"][] = [
					"id" 	=> (int)$item["id"],
					"count" => (int)$item["count"]
				];
			}
			$boxes[] = $box;
		}

		$res = $api->putBoxes($boxes,$arOrder["EXT_ID"],$arOrder["SHIPMENT_ID"]);
		if ($res["status"]!=200)
			return ["status"=>$res["status"], "error"=>$res["body"]["errors"][0]["message"]];
		else
			return ["status"=>200];
	}


}


