<?
namespace Iplogic\Beru;

use \Bitrix\Main,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Application,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\YMAPI,
	\Iplogic\Beru\YML,
	\Iplogic\Beru\ProductTable,
	\Iplogic\Beru\ProfileTable,
	\Iplogic\Beru\ApiLogTable;

IncludeModuleLangFile(Application::getDocumentRoot().BX_ROOT."/modules/iplogic.beru/lib/lib.php");

/**
 * Class TaskTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROFILE_ID int mandatory
 * <li> UNIX_TIMESTAMP int mandatory
 * <li> TYPE string(20) optional
 * <li> STATE string(2) mandatory
 * <li> ENTITY_ID string(255) optional
 * <li> TRYING int optional
 * </ul>
 *
 * @package Iplogic\Beru
 **/

class TaskTable extends Main\Entity\DataManager
{

	public static $moduleID = "iplogic.beru";

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iplogicberu_task';
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
				'title' => Loc::getMessage('TASK_ENTITY_ID_FIELD'),
			),
			'PROFILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TASK_ENTITY_PROFILE_ID_FIELD'),
			),
			'UNIX_TIMESTAMP' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TASK_ENTITY_UNIX_TIMESTAMP_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('TASK_ENTITY_TYPE_FIELD'),
			),
			'STATE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateState'),
				'title' => Loc::getMessage('TASK_ENTITY_STATE_FIELD'),
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityId'),
				'title' => Loc::getMessage('TASK_ENTITY_ENTITY_ID_FIELD'),
			),
			'TRYING' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('TASK_ENTITY_TRYING_FIELD'),
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
			new Main\Entity\Validator\Length(null, 20),
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
	 * Returns validators for ENTITY_ID field.
	 *
	 * @return array
	 */
	public static function validateEntityId()
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


	public static function getNextTask() {
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT * FROM ".$helper->quote(self::getTableName())." WHERE ".
			$helper->quote('UNIX_TIMESTAMP')."<=".time()." AND ".
			$helper->quote('STATE')." = 'WT' AND ".
			$helper->quote('TYPE')." != 'HP' AND ".
			$helper->quote('TYPE')." != 'UP' AND ".
			$helper->quote('TYPE')." != 'PR' ORDER BY UNIX_TIMESTAMP ASC";
		$result = $conn->query($strSql);
		unset($helper, $conn);
		return $result->Fetch();
	}


	public static function executeNextTask() {
		Loader::includeModule("catalog");
		if($task = self::getNextTask()) {
			Option::set(self::$moduleID,"last_task_time",time());
			$arFields = ["STATE"=>"IW"];
			self::update($task["ID"],$arFields);
			if ($task["TYPE"]=="RQ") {
				self::repeatQuery($task);
			} 
			if ($task["TYPE"]=="PU") {
				self::updateProduct($task);
			}  
			if ($task["TYPE"]=="SP") {
				self::sendPrice($task);
			} 
			if ($task["TYPE"]=="FP") {
				self::refreshYmlProducts($task);
			}
			if ($task["TYPE"]=="FC") {
				self::generateYmlFile($task);
			}
			if ($task["TYPE"]=="HS") {
				self::sendHidden($task);
			}
			if ($task["TYPE"]=="US") {
				self::sendShown($task);
			}
			exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/task.php"); 
			//die();
		}
		else {
			if (Option::get(self::$moduleID,"can_execute_tasks","N") == "N"
				&& Option::get(self::$moduleID,"allow_multichain_tasks","N") == "N"
			) {
				Option::set(self::$moduleID,"can_execute_tasks","Y");
			}
		}
	}




	/* QUERY */

	protected static function repeatQuery($task) {
		$obApi = new YMAPI($task["PROFILE_ID"]);
		$arLog = ApiLogTable::getById($task["ENTITY_ID"]); 
		if ($arLog) {
			$res = $obApi->query($arLog["REQUEST_TYPE"], $arLog["URL"], $arLog["REQUEST"], $task);
			if ($res["status"] == 200) {
				self::delete($task["ID"]);
				return;
			}
			if ($res["stop_repeating"]) {
				self::delete($task["ID"]);
				$arFields = [
					"STATE" => "RJ"
				];
				ApiLogTable::update($task["ENTITY_ID"],$arFields);
			}
			else {
				$arFields = [
					"STATE" 			=> "WT",
					"TRYING" 			=> ($task["TRYING"] + 1),
					"UNIX_TIMESTAMP" 	=> time() + Option::get(self::$moduleID,"task_trying_period",60),
				];
				self::update($task["ID"],$arFields);
			}
		}
		else {
			self::delete($task["ID"]);
		}
	}




	/* PRODUCT */

	protected static function updateProduct($task) {
		ProductTable::updateCache($task["ENTITY_ID"]);
		self::delete($task["ID"]);
	}




	/* PRICE */

	public static function addPriceUpdateTask($ID, $PROFILE_ID) {
		$rsTask = self::getList(["filter"=>["TYPE"=>"PR","STATE"=>"WT","ENTITY_ID"=>$ID]]);
		if (!$rsTask->Fetch()) {
			$arFields = [
				"PROFILE_ID" 		=> $PROFILE_ID,
				"UNIX_TIMESTAMP" 	=> time(),
				"TYPE" 				=> "PR",
				"STATE" 			=> "WT",
				"ENTITY_ID" 		=> $ID,
				"TRYING" 			=> 0
			];
			self::add($arFields);
			self::scheduleSendPrice($PROFILE_ID);
		}
	}

	public static function scheduleSendPrice($PROFILE_ID) {
		self::scheduleTask($PROFILE_ID,"SP",60);
	}

	protected static function sendPrice($task) {
		$rsData = self::getList([
			"filter" => ["TYPE"=>"PR", "PROFILE_ID"=>$task["PROFILE_ID"]], 
			"order" => ["UNIX_TIMESTAMP" => "ASC"],
			'limit' => 50, 
			'offset' => 0
		]);
		$IDs = [];
		while ($arData = $rsData->Fetch()) {
			$IDs[$arData["ID"]] = $arData["ENTITY_ID"];
		}
		if (count($IDs)) {
			$rsData = ProductTable::getList([
				"filter" => ["ID"=>$IDs],
			]);
			$arProducts = [];
			while ($arData = $rsData->Fetch()) {
				$arProducts[$arData["ID"]] = $arData;
			}
		}
		if (count($arProducts)) {
			$arPrices = [];
			$arResult['offers'] = [];
			foreach($IDs as $key => $val) {
				if( !isset($arProducts[$val]) || !$arProducts[$val]["MARKET_SKU"] ) {
					self::delete($key);
				}
				else {
					$details = unserialize($arProducts[$val]["DETAILS"]);
					if($details["PRICE"]>0){
						$new_price = $details["PRICE"];
						$arPrices[$val] = $new_price;
						$arResult['offers'][] = [
							"marketSku" => $arProducts[$val]["MARKET_SKU"],
							"delete" 	=> false,
							"price" 	=> [
								"currencyId" 	=> "RUR",
								"value" 		=> (double)$new_price,
							]
						];
					}
					else {
						self::delete($key);
					}
				}
			}

			$api = new YMAPI($task["PROFILE_ID"]);
			$res = $api->setPrices($arResult);
			if ($res["status"] == 200) { 
				foreach($IDs as $key => $val) { 
					if(array_key_exists($val, $arPrices)) {
						ProductTable::update($val,["PRICE"=>$arPrices[$val]]);
						self::delete($key);
					}
				}
			}
			else {
				//AddMessage2Log('Price update error', 'iplogic.beru');
			}

		}
		self::scheduleSendPrice($task["PROFILE_ID"]);
	}




	/* FEED */


	public static function scheduleFeedProductsRefresh($PROFILE_ID) {
		self::scheduleTask($PROFILE_ID,"FP",300);
	}


	protected static function refreshYmlProducts($task) {
		$step_size = 100;
		$feedControl = new YML($task["PROFILE_ID"]);
		$arFeedProducts = $feedControl->getFeedProductsIDs();
		$rsProducts = ProductTable::getList(["filter"=>["PROFILE_ID"=>$task["PROFILE_ID"]], "select"=>["ID","PRODUCT_ID","SKU_ID","API","FEED"]]);
		$arExistingProducts = $rsProducts->fetchAll();

		$i = 0;
		foreach($arExistingProducts as $prod) {
			if(!in_array($prod["PRODUCT_ID"], $arFeedProducts) && $prod["API"]!="Y") {
				ProductTable::delete($prod["ID"]);
				TaskTable::scheduleFeedGeneration($task["PROFILE_ID"]);
				$i++;
				if($i >= $step_size) {
					exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/feed_products.php?param=".$task["ID"]);
					return self::scheduleFeedProductsRefresh($task["PROFILE_ID"]);
				}
			}
		}
		if ($i < $step_size && $i > 0) {
			exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/feed_products.php?param=".$task["ID"]);
			return self::scheduleFeedProductsRefresh($task["PROFILE_ID"]);
		}
		if($i == 0) {
			foreach($arExistingProducts as $prod) {
				if(!in_array($prod["PRODUCT_ID"], $arFeedProducts) && $prod["FEED"]=="Y") {
					ProductTable::update($prod["ID"],["FEED"=>"N"]);
					TaskTable::scheduleFeedGeneration($task["PROFILE_ID"]);
					$i++;
				}
				elseif(in_array($prod["PRODUCT_ID"], $arFeedProducts) && $prod["FEED"]!="Y") {
					ProductTable::update($prod["ID"],["FEED"=>"Y"]);
					TaskTable::scheduleFeedGeneration($task["PROFILE_ID"]);
					$i++;
				}
				if($i >= $step_size) {
					exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/feed_products.php?param=".$task["ID"]);
					return self::scheduleFeedProductsRefresh($task["PROFILE_ID"]);
				}
			}
		}
		if ($i < $step_size && $i > 0) {
			exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/feed_products.php?param=".$task["ID"]);
			return self::scheduleFeedProductsRefresh($task["PROFILE_ID"]);
		}
		if($i == 0) {
			$arExistingProductsIDs = [];
			foreach($arExistingProducts as $prod) {
				$arExistingProductsIDs[$prod["PRODUCT_ID"]] = $prod["SKU_ID"];
			}
			$control = new Control($task["PROFILE_ID"]);
			foreach($arFeedProducts as $id) {
				if (!array_key_exists($id, $arExistingProductsIDs)) { 
					$product = $control->getSKUByProductID($id);
					if ($product) {
						$fields = [
							"PROFILE_ID" => $task["PROFILE_ID"],
							"PRODUCT_ID" => $id,
							"SKU_ID"     => $product["SHOP_SKU_ID"],
							"VENDOR"     => $product["VENDOR"],
							"NAME"       => $product["NAME"],
							"API"        => "N",
							"FEED"       => "Y",
							"DETAILS"    => serialize($product),
							"STATE"      => "FEED_ONLY"
						]; 
						ProductTable::add($fields);
						TaskTable::scheduleFeedGeneration($task["PROFILE_ID"]);
						$i++;
						if($i >= ($step_size/2)) {
							exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/feed_products.php?param=".$task["ID"]);
							return self::scheduleFeedProductsRefresh($task["PROFILE_ID"]);
						}
					}

				}
			}
		}
		if ($i < ($step_size/2) && $i > 0) {
			exec("wget -b -q -O - https://".Option::get(self::$moduleID,"domen")."/bitrix/services/iplogic/mkpapi/feed_products.php?param=".$task["ID"]);
		}
		return self::scheduleFeedProductsRefresh($task["PROFILE_ID"]);
	}




	/* YML */


	public static function scheduleFeedGeneration($PROFILE_ID) {
		self::scheduleTask($PROFILE_ID,"FC",60);
	}


	public static function generateYmlFile($task) { 
		$yml = new YML($task["PROFILE_ID"],str_replace("/bitrix/modules/iplogic.beru/lib","",__DIR__));
		$yml->generateNew();
		self::delete($task["ID"]);
	}



	/* HIDE */


	public static function hideProductTask($ID, $PROFILE_ID) {
		$rsTask = self::getList(["filter"=>["TYPE"=>"HP","STATE"=>"WT","ENTITY_ID"=>$ID]]);
		if (!$rsTask->Fetch()) {
			$arFields = [
				"PROFILE_ID" 		=> $PROFILE_ID,
				"UNIX_TIMESTAMP" 	=> time(),
				"TYPE" 				=> "HP",
				"STATE" 			=> "WT",
				"ENTITY_ID" 		=> $ID,
				"TRYING" 			=> 0
			];
			self::add($arFields);
			self::scheduleSendHidden($PROFILE_ID);
		}
	}


	public static function scheduleSendHidden($PROFILE_ID) {
		self::scheduleTask($PROFILE_ID,"HS",60);
	}


	protected static function sendHidden($task) {
		$rsData = self::getList([
			"filter" => ["TYPE"=>"HP", "PROFILE_ID"=>$task["PROFILE_ID"]], 
			"order" => ["UNIX_TIMESTAMP" => "ASC"],
			'limit' => 500, 
			'offset' => 0
		]);
		$IDs = [];
		$arProducts = [];
		while ($arData = $rsData->Fetch()) { 
			$IDs[$arData["ID"]] = $arData["ENTITY_ID"];
		}
		if (count($IDs)) {
			$rsData = ProductTable::getList([
				"filter" => ["ID"=>$IDs],
			]);
			while ($arData = $rsData->Fetch()) { 
				$arProducts[$arData["ID"]] = $arData;
			}
		}
		if (count($arProducts)) { 
			$arResult['hiddenOffers'] = [];
			$unseted = [];
			foreach($IDs as $key => $val) {
				if( !isset($arProducts[$val]) || !$arProducts[$val]["MARKET_SKU"] ) { 
					$unseted[] = $val;
					self::delete($key);
				}
				else {
					$arResult['hiddenOffers'][] = [
						"marketSku" => (int)$arProducts[$val]["MARKET_SKU"],
						"comment" 	=> "",
						"ttlInHours" 	=> 720
					];
				}
			}
			$api = new YMAPI($task["PROFILE_ID"]);
			$res = $api->setHidden($arResult);
			if ($res["status"] == 200) { 
				foreach($IDs as $key => $val) { 
					if(!in_array($val, $unseted)) {
						ProductTable::update($val,["HIDDEN"=>"Y"]);
						self::delete($key);
					}
				}
			}
			else {
				//AddMessage2Log('Hidden update error', 'iplogic.beru');
			}
		}
		self::scheduleSendHidden($task["PROFILE_ID"]);
	}




	/* SHOW */


	public static function showProductTask($ID, $PROFILE_ID) {
		$rsTask = self::getList(["filter"=>["TYPE"=>"UP","STATE"=>"WT","ENTITY_ID"=>$ID]]);
		if (!$rsTask->Fetch()) {
			$arFields = [
				"PROFILE_ID" 		=> $PROFILE_ID,
				"UNIX_TIMESTAMP" 	=> time(),
				"TYPE" 				=> "UP",
				"STATE" 			=> "WT",
				"ENTITY_ID" 		=> $ID,
				"TRYING" 			=> 0
			];
			self::add($arFields);
			self::scheduleSendShown($PROFILE_ID);
		}
	}


	public static function scheduleSendShown($PROFILE_ID) {
		self::scheduleTask($PROFILE_ID,"US",60);
	}


	protected static function sendShown($task) {
		$rsData = self::getList([
			"filter" => ["TYPE"=>"UP", "PROFILE_ID"=>$task["PROFILE_ID"]], 
			"order" => ["UNIX_TIMESTAMP" => "ASC"],
			'limit' => 500, 
			'offset' => 0
		]);
		$IDs = [];
		while ($arData = $rsData->Fetch()) { 
			$IDs[$arData["ID"]] = $arData["ENTITY_ID"];
		}
		if (count($IDs)) {
			$rsData = ProductTable::getList([
				"filter" => ["ID"=>$IDs],
			]);
			$arProducts = [];
			while ($arData = $rsData->Fetch()) {  
				$arProducts[$arData["ID"]] = $arData;
			}
		}
		if (count($arProducts)) { 
			$arResult['hiddenOffers'] = [];
			$unseted = [];
			foreach($IDs as $key => $val) {
				if( !isset($arProducts[$val]) || !$arProducts[$val]["MARKET_SKU"] ) { 
					$unseted[] = $val;
					self::delete($key);
				}
				else {
					$arResult['hiddenOffers'][] = [
						"marketSku" => (int)$arProducts[$val]["MARKET_SKU"],
					];
				}
			}
			$api = new YMAPI($task["PROFILE_ID"]);
			$res = $api->setShown($arResult);
			if ($res["status"] == 200) { 
				foreach($IDs as $key => $val) { 
					if(!in_array($val, $unseted)) {
						ProductTable::update($val,["HIDDEN"=>"N"]);
						self::delete($key);
					}
				}
			}
			else {
				//AddMessage2Log('Hidden update error', 'iplogic.beru');
			}
		}
		self::scheduleSendShown($task["PROFILE_ID"]);
	}



	/* COMMON */


	public static function scheduleTask($PROFILE_ID,$CODE,$DELAY) {
		$conn = Application::getConnection(); 
		$helper = $conn->getSqlHelper();
		$strSql = "SELECT * FROM ".$helper->quote(self::getTableName())." WHERE ".$helper->quote('TYPE')." = '".$CODE."' AND ".$helper->quote('PROFILE_ID')." = ".$PROFILE_ID; 
		$result = $conn->query($strSql);
		unset($helper, $conn);
		$task = $result->Fetch(); 
		if (!$task) {
			$arFields = [
				"PROFILE_ID" 		=> $PROFILE_ID,
				"UNIX_TIMESTAMP" 	=> time() + $DELAY,
				"TYPE" 				=> $CODE,
				"STATE" 			=> "WT",
				"TRYING" 			=> 0
			];
			self::add($arFields);
		}
		else {
			$arFields = [
				"UNIX_TIMESTAMP" 	=> time() + $DELAY,
				"STATE" 			=> "WT",
			];
			self::update($task["ID"],$arFields);
		}
	}

}


