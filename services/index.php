<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main\Loader,
	\Bitrix\Main\Web\Json,
	\Bitrix\Main\Localization\Loc,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\ApiLogTable as ApiLog,
	\Iplogic\Beru\ErrorTable as Error;

Loc::loadMessages(__FILE__);

Loader::includeModule("iplogic.beru");
Loader::includeModule("catalog");
Loader::includeModule("sale");

$mod = new Control();
$action = $mod->initMethodFromUrl();

$req_headers = "";
foreach($mod->getallheaders() as $key => $val) {
	$req_headers .= $key.": ".$val."<br>";
} 

$arFields = [
	"PROFILE_ID" 		=> $mod->arProfile["ID"],
	"TYPE" 				=> "IC",
	"URL" 				=> $APPLICATION->GetCurPage(false),
	"REQUEST_TYPE" 		=> $_SERVER ['REQUEST_METHOD'],
	"REQUEST" 			=> Control::fixUnicode(file_get_contents( 'php://input' )),
	"REQUEST_H" 		=> $req_headers,
	"STATE" 			=> "EX",
];
$EID = ApiLog::add($arFields)->getId();

header('Content-Type: application/json');

if ($action) {
	include(__DIR__."/".$action);
	$data = mkaAction();
}
if (count($mod->error)) {
	$data = $mod->getErrorArray();
	header( 'HTTP/1.1 '.$data["error"]["code"].' '.$data["errors"][0]["code"] );
	$arLogFields = [
		"STATE" 			=> "RJ",
		"RESPOND" 			=> Json::encode($data),
		"STATUS" 			=> $data["error"]["code"],
		"ERROR" 			=> $data["errors"][0]["code"].": ".$data["errors"][0]["message"],
	];
	//$request = Control::toHtml(print_r(Control::fixUnicodeRecursive(Json::decode(file_get_contents( 'php://input' ))),true));
	$request = Control::fixUnicode(file_get_contents( 'php://input' ));
	$details = "HEADERS:<br><br>".$req_headers."<br><br>REQUEST:<br><br>".$request;
	$arErFields = [
		"PROFILE_ID" 		=> $mod->arProfile["ID"],
		"ERROR" 			=> $data["error"]["code"].": ".$data["errors"][0]["code"]." - ".$data["errors"][0]["message"],
		"DETAILS" 			=> $details,
	];
	Error::add($arErFields);
}
else {
	$arLogFields = [
		"STATE" 			=> "OK",
		"RESPOND" 			=> Json::encode($data),
		"STATUS" 			=> 200,
		"ERROR" 			=> null,
	];
}
$arLogFields["close"] = true;
ApiLog::update($EID, $arLogFields);

echo Control::prepareText(Json::encode($data) ,true ,true);