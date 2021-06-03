<?
namespace Iplogic\Beru;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Web\HttpClient,
	\Bitrix\Main\Web\Json,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\ProfileTable as Profile,
	\Iplogic\Beru\TaskTable as Task,
	\Iplogic\Beru\ErrorTable as Error,
	\Iplogic\Beru\ApiLogTable as ApiLog;


class YMAPI {

	public static $moduleID = "iplogic.beru";
	private $arProfile,
			$url = "https://api.partner.market.yandex.ru/v2/",
			$cl,
			$headers;


	function __construct($profileID) 
	{
		$this->arProfile = Profile::getById($profileID);
		$this->cl = new HttpClient(['socketTimeout' => 10]);
		$this->headers = [
			"Content-Type" => "application/json; charset=UTF-8",
			"Authorization" => 'OAuth oauth_token="'.$this->arProfile["SEND_TOKEN"].'", oauth_client_id="'.$this->arProfile["CLIENT_ID"].'"'
		];
		foreach($this->headers as $key => $val) {
			$this->cl->setHeader($key, $val);
		}
		return;
	}


	/*
	GET /delivery/services
	*/
	public function getMPDeliveryList()
	{
		$path = "delivery/services.json";
		return $this->query("GET", $path);
	}


	/*
	PUT /campaigns/{campaignId}/orders/{orderId}/status
	*/
	public function setOrderStatus($order_id, $status, $substatus) 
	{
		$data = Json::encode([
			"order" => [
				"status" 	=> $status,
				"substatus" => $substatus
			]
		]);
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/orders/".$order_id."/status.json";
		return $this->query("PUT", $path, $data);
	}


	/*
	POST /campaigns/{campaignId}/orders/status-update
	*/
	public function setOrderStatuses($orders) 
	{
		$data = Json::encode([
			"orders" => $orders
		]);
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/orders/status-update.json";
		return $this->query("POST", $path, $data);
	}


	/*
	GET /campaigns/{campaignId}/offer-mapping-entries
	*/
	public function getOffersMapping($arParams = []) {
		$stParams = "";
		$arStParams = [];
		if(isset($arParams["limit"])) {
			$arStParams[] = "limit=".$arParams["limit"];
		}
		if(isset($arParams["page_token"])) {
			$arStParams[] = "page_token=".$arParams["page_token"];
		}
		if(isset($arParams["shop_sku"])) {
			if (is_array($sku)) {
				foreach ($arParams["shop_sku"] as $sku) {
					$arStParams[] = "shop_sku=".$sku;
				}
			}
			else
				$arStParams[] = "shop_sku=".$arParams["shop_sku"];
		}
		if (count($arStParams)) {
			$stParams = "?".implode("&", $arStParams);
		}
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/offer-mapping-entries.json".$stParams;
		return $this->query("GET", $path);
	}


	/*
	GET /campaigns/{campaignId}/orders/{orderId}
	GET /campaigns/{campaignId}/orders
	*/
	public function getOrders($params = []) {
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/orders";
		if (isset($params["orderId"])) {
			$path = $path."/".$params["orderId"].".json";
		}
		else {
			$par = ["fake","fromDate","page","pageSize","status","toDate"];
			$stParams = "";
			$arStParams = [];
			foreach($par as $param) {
				if (isset($params[$param])) {
					$stParams[] = $param."=".$params[$param];
				}
			}
			if (count($arStParams)) {
				$stParams = "?".implode("&", $arStParams);
			}
			$path = $path.".json".$stParams;
		}
		return $this->query("GET", $path);
	}


	/*
	PUT /campaigns/{campaignId}/orders/{orderId}/delivery/shipments/{shipmentId}/boxes
	*/
	public function putBoxes($boxes,$order,$shipment) {
		$data = Control::jsonEncode([
			"boxes" => $boxes
		]);
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/orders/".$order."/delivery/shipments/".$shipment."/boxes.json";
		return $this->query("PUT", $path, $data);
	}


	/*
	POST /campaigns/{campaignId}/offer-prices/updates
	*/
	public function setPrices($offers) {
		$data = Control::jsonEncode($offers);
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/offer-prices/updates.json";
		return $this->query("POST", $path, $data);
	}


	/*
	POST /campaigns/{campaignId}/hidden-offers
	*/
	public function setHidden($offers) {
		$data = Control::jsonEncode($offers);
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/hidden-offers.json";
		return $this->query("POST", $path, $data);
	}


	/*
	DELETE /campaigns/{campaignId}/hidden-offers
	*/
	public function setShown($offers) {
		$data = Control::jsonEncode($offers);
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/hidden-offers.json";
		return $this->query("DELETE", $path, $data);
	}


	/*
	GET /campaigns/{campaignId}/hidden-offers
	*/
	public function getHidden() {
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/hidden-offers.json";
		return $this->query("GET", $path);
	}


	/*
	GET /campaigns/{campaignId}/orders/{orderId}/delivery/shipments/{parcelId}/boxes/{boxId}/label
	*/
	public function getLabel($arParams, $file) {
		$path = "campaigns/".$this->arProfile["COMPAIN_ID"]."/hidden-offers.json";
		return $this->query("GET", $path);
	}


	public function query($type, $path, $data = null, $task = false) {
		$stop_repeating = null;
		if (!$task) { $path = $this->url.$path; }
		$arFields = [
			"PROFILE_ID" 		=> $this->arProfile["ID"],
			"TYPE" 				=> "OG",
			"STATE" 			=> "EX",
			"URL" 				=> $path,
			"REQUEST_TYPE" 		=> $type,
			"REQUEST_H" 		=> $this->getRequestHeaders(),
			"REQUEST" 			=> $data
		];
		$EID = ApiLog::add($arFields)->getId();
		$this->cl->query($type, $path, Control::prepareText($data ,true ,true)); 
		$state = "RJ";
		if ($this->cl->getStatus() == 200)
			$state = "OK";

		$error = null;
		if ($state == "RJ") {
			$res = Json::decode($this->cl->getResult());
			$error = $this->cl->getStatus().": ".$res["errors"][0]["code"]." - ".$res["errors"][0]["message"];
		}
		$arLogFields = [
			"STATE" 			=> $state,
			"RESPOND" 			=> Control::fixUnicode($this->cl->getResult()),
			"RESPOND_H" 		=> $this->getRespondHeaders(),
			"STATUS" 			=> $this->cl->getStatus(),
			"ERROR" 			=> $error,
		];
		if( $this->cl->getStatus() != 200 ) { 
			$response = Json::decode(Control::fixUnicode($this->cl->getResult()));
			if ( 
				$this->cl->getStatus() == 500 
			) {
				if (!$task) {
					$arFields = [
						"PROFILE_ID" 		=> $this->arProfile["ID"],
						"UNIX_TIMESTAMP" 	=> time() + Option::get(self::$moduleID,"task_trying_period",60),
						"TYPE" 				=> "RQ",
						"STATE" 			=> "WT",
						"ENTITY_ID" 		=> $EID,
						"TRYING" 			=> 0
					];
					Task::add($arFields);
					$arLogFields["STATE"] = "DF";
				}
				else {
					if ( ($task["TRYING"] + 1) >= Option::get(self::$moduleID,"task_trying_num",3) ) {
						$stop_repeating = true;
						$this->putError($path,$data);
					}
				}
			}
			else {
				$this->putError($path,$data);
			}
		}
		$arLogFields["close"] = true;
		ApiLog::update($EID, $arLogFields);
		return [
			"status" => $this->cl->getStatus(),
			"body" => Json::decode($this->cl->getResult()),
			"stop_repeating" => $stop_repeating
		];
	}


	private function putError($path, $data) {
		//$data = ($data ? Control::toHtml(print_r(Json::decode($data),true)) : "");
		$data = ($data ? $data : "");
		$details = "URL: ".$path."<br><br>TOKEN: ".$this->arProfile["SEND_TOKEN"]."<br><br>REQUEST<br><br>"
					.$data."<br><br>";
		$response = Json::decode($this->cl->getResult());
		$arFields = [
			"PROFILE_ID" 	=> $this->arProfile["ID"],
			"ERROR" 		=> $this->cl->getStatus().": ".$response["errors"][0]["code"]." - ".$response["errors"][0]["message"],
			"DETAILS" 		=> $details,
		];
		return Error::add($arFields);
	}


	private function getRequestHeaders() {
		$headers = "";
		foreach(headers_list() as $val) {
			if(stristr($val, 'Content-Type:') === FALSE) {
				$headers .= $val."<br>";
			}
		}
		foreach($this->headers as $key => $val) {
			$headers .= $key.": ".$val."<br>";
		}
		return $headers;
	}


	private function getRespondHeaders() {
		$headers = "";
		foreach($this->cl->getHeaders()->toArray() as $arHeader) {
			foreach($arHeader["values"] as $val) {
				$headers .= $arHeader["name"].": ".$val."<br>";
			}
		}
		return $headers;
	}


}