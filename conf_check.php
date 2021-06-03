<?
use \Bitrix\Main\Loader,
	\Bitrix\Main\Config\Option;

function hasSsl( $domain ) {
	$res = false;
	$stream = @stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
	$socket = @stream_socket_client( 'ssl://' . $domain . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $stream );
	if ( $socket ) {
		$cont = stream_context_get_params( $socket );
		$cert_ressource = $cont['options']['ssl']['peer_certificate'];
		$cert = openssl_x509_parse( $cert_ressource );
		$namepart = explode( '=', $cert['name'] );
		if ( count( $namepart ) == 2 ) {
			$cert_domain = trim( $namepart[1], '*. ' );
			$check_domain = substr( $domain, -strlen( $cert_domain ) );
			$res = ($cert_domain == $check_domain);
		}
	}
	return $res;
}

function getModuleVersion($mod) {
	if($info = CModule::CreateModuleObject($mod)){
		return $info->MODULE_VERSION;
	}
}



$arConfigCheck = [];

// SSL
$domen = Option::get("iplogic.beru","domen");
$arConfigCheck["SSL"] = hasSsl($domen);

// write permissions
$arConfigCheck["PERM"]["modules"] = is_writable($_SERVER['DOCUMENT_ROOT']."/bitrix/modules");
$arConfigCheck["PERM"]["admin"] = is_writable($_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
$arConfigCheck["PERM"]["css"] = is_writable($_SERVER['DOCUMENT_ROOT']."/bitrix/css");
$arConfigCheck["PERM"]["images"] = is_writable($_SERVER['DOCUMENT_ROOT']."/bitrix/images");
$arConfigCheck["PERM"]["services"] = is_writable($_SERVER['DOCUMENT_ROOT']."/bitrix/services");
$arConfigCheck["PERM"]["urlrewrite"] = is_writable($_SERVER['DOCUMENT_ROOT']."/urlrewrite.php");

// PHP

$arConfigCheck["PHP"]["VERSION"] = phpversion();
$arConfigCheck["PHP"]["VALID"] = CheckVersion(phpversion(), "7.0.0");


if (!function_exists('exec')) {
	$arConfigCheck["PHP"]["exec"] = false;
}
else {
	if(exec('echo EXEC')!="EXEC") {
		$arConfigCheck["PHP"]["exec"] = false;
	}
	else {
		$arConfigCheck["PHP"]["exec"] = true;
	}
}

$arConfigCheck["PHP"]["zip"] = false;
if(extension_loaded('zip')) {
	$arConfigCheck["PHP"]["zip"] = true;
}

$arConfigCheck["PHP"]["gd"] = false;
if(extension_loaded('gd')) {
	$arConfigCheck["PHP"]["gd"] = true;
}

// Bitrix
$arConfigCheck["MOD"]["iblock"] = Loader::includeModule("iblock");
$arConfigCheck["MOD"]["catalog"] = Loader::includeModule("catalog");
$arConfigCheck["MOD"]["sale"] = Loader::includeModule("sale");

$arConfigCheck["MOD_VER"]["main"] = SM_VERSION;
$arConfigCheck["MOD_VER"]["iblock"] = getModuleVersion("iblock");
$arConfigCheck["MOD_VER"]["catalog"] = getModuleVersion("catalog");
$arConfigCheck["MOD_VER"]["sale"] = getModuleVersion("sale");

// cron
$arConfigCheck["CRON"] = true;


$arConfigCheck["RESULT"] = true;
if (
	!$arConfigCheck["SSL"] ||
	!$arConfigCheck["PERM"]["modules"] ||
	!$arConfigCheck["PERM"]["admin"] ||
	!$arConfigCheck["PERM"]["css"] ||
	!$arConfigCheck["PERM"]["images"] ||
	!$arConfigCheck["PERM"]["services"] ||
	!$arConfigCheck["PERM"]["urlrewrite"] ||
	!$arConfigCheck["PHP"]["VALID"] ||
	!$arConfigCheck["PHP"]["exec"] ||
	!$arConfigCheck["PHP"]["zip"] ||
	!$arConfigCheck["PHP"]["gd"] ||
	!$arConfigCheck["MOD"]["iblock"] ||
	!$arConfigCheck["MOD"]["catalog"] ||
	!$arConfigCheck["MOD"]["sale"] /*||
	!$arConfigCheck["CRON"]*/
)
	$arConfigCheck["RESULT"] = false;