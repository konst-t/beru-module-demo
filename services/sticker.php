<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iplogic.beru/services/sticker/";
include($path.'php-barcode.php');

use \Bitrix\Main\Loader,
	\Iplogic\Beru\ProfileTable,
	\Iplogic\Beru\BoxTable,
	\Iplogic\Beru\Control,
	\Iplogic\Beru\OrderTable;

Loader::includeModule("iplogic.beru");

class sticker
{
	private
		$bc_width = 760,
		$bc_height = 320,
		$logo_width = 700,
		$logo_height = 200,
		$font_size = 50,
		$max_line_length = 18,
		$line_height = 60,
		$path,
		$font = 'arial.ttf';


	function __construct() {
		global $path;
		$this->path = $path;
		$this->font = $path.$this->font;
	}


	function getBarcode($code) {
		$x        = $this->bc_width/2;
		$y        = $this->bc_height/2;
		$height   = 300;
		$width    = 6;
		$angle    = 0;
		$type     = 'code128';

		$im     = imagecreatetruecolor($this->bc_width, $this->bc_height);
		$black  = ImageColorAllocate($im,0x00,0x00,0x00);
		$white  = ImageColorAllocate($im,0xff,0xff,0xff);
		imagefilledrectangle($im, 0, 0, $this->bc_width, $this->bc_height, $white);

		$data = Barcode::gd($im, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);

		return $im;
	}


	function splitLong($str) {
		$arWords = explode(" ", $str);
		$i = 0;
		$lines = [0=>""];
		foreach($arWords as $word) {
			if ($lines[$i] == "") {
				$lines[$i] = $word;
			}
			else {
				$line = $lines[$i]." ".$word;
				if (strlen($line) > $this->max_line_length) {
					$i++;
					$lines[$i] = $word;
				}
				else {
					$lines[$i] = $line;
				}
			}
		}
		return $lines;
	}


	function logo($file) {
		$im     = imagecreatetruecolor($this->logo_width, $this->logo_height);
		$white  = ImageColorAllocate($im,0xff,0xff,0xff);
		imagefilledrectangle($im, 0, 0, $this->logo_width, $this->logo_height, $white);

		$idata = @getimagesize($file);
		$source_width = $idata[0];
		$source_height = $idata[1];
		$ext = $idata[2];
		if( $ext == '2' ) { $source = @imagecreatefromjpeg($file); }
		elseif( $ext == '1' ) { $source = @imagecreatefromgif($file); }
		elseif( $ext == '3' ) { $source = @imagecreatefrompng($file); }

		(double)$ratiow=(double)$source_width/ (double)$this->logo_width;
		(double)$ratioh=(double)$source_height/ (double)$this->logo_height;
		if( $ratiow < 1 && $ratioh < 1 ) {
			if( $ratiow > $ratioh ) { $ratio = $ratiow; }
			else { $ratio = $ratioh; }
		}
		elseif( $ratiow > 1 && $ratioh > 1 ) {
			if( $ratiow < $ratioh ) { $ratio = $ratioh; }
			else { $ratio = $ratiow; }
		}
		elseif( $ratiow > 1 && $ratioh == 1 ) {
			$ratio = $ratiow;
		}
		elseif( $ratiow < 1 && $ratioh ==1 ) {
			$ratio = $ratioh;
		}
		elseif( $ratiow >= 1 && $ratioh < 1 ) { 
			$ratio = $ratiow;
		}
		elseif( $ratiow <= 1 && $ratioh > 1 ) {
			$ratio = $ratioh;
		}
		elseif( $ratiow == 1 && $ratioh == 1 ) {
			$ratio = 1;
		}
		$result_width = $source_width/$ratio;
		$result_height = $source_height/$ratio;

		$pos_x = ($this->logo_width-$result_width)/2;
		$pos_y = ($this->logo_height-$result_height)/2;

		imagecopyresampled($im, $source, $pos_x, $pos_y, 0, 0, $result_width, $result_height, $source_width, $source_height);

		return $im;
	}


	function calcBCTextPosition($x,$y,$text) {
		$a = $x + ( $this->bc_width / 2 );
		$b = $y + $this->bc_height + $this->line_height;
		$box = imagettfbbox($this->font_size, 0, $this->font, $text);
		$position = $a-round(($box[2]-$box[0])/2);
		return [$position,$b];
	}


	function generate($arParams) {
		$im = imagecreatefrompng($this->path."blank.png");

		$black = ImageColorAllocate($im,0x00,0x00,0x00);

		$x = 850;
		$y = 520;
		imagecopy($im, $this->getBarcode($arParams["BOX"]), $x, $y, 0, 0, $this->bc_width, $this->bc_height);
		$tp = $this->calcBCTextPosition($x,$y,$arParams["BOX"]);
		imagettftext($im, $this->font_size, 0, $tp[0], $tp[1], $black, $this->font, $arParams["BOX"]);

		$x = 480;
		$y = 1000;
		imagecopy($im, $this->getBarcode($arParams["EXT_ID"]), $x, $y, 0, 0, $this->bc_width, $this->bc_height);
		$tp = $this->calcBCTextPosition($x,$y,$arParams["EXT_ID"]);
		imagettftext($im, $this->font_size, 0, $tp[0], $tp[1], $black, $this->font, $arParams["EXT_ID"]);

		$x = 850;
		$y = 1770;
		imagecopy($im, $this->getBarcode($arParams["ORDER_ID"]), $x, $y, 0, 0, $this->bc_width, $this->bc_height);
		$tp = $this->calcBCTextPosition($x,$y,$arParams["ORDER_ID"]);
		imagettftext($im, $this->font_size, 0, $tp[0], $tp[1], $black, $this->font, $arParams["ORDER_ID"]);

		$lines = $this->splitLong($arParams["DELEVERY_NAME"]);
		$x = 670;
		foreach ($lines as $line){
			imagettftext($im, $this->font_size, 0, 140, $x, $black, $this->font, Control::prepareRequestText($line));
			$x = $x + $this->line_height;
		}
		imagettftext($im, $this->font_size, 0, 140, 1600, $black, $this->font, $arParams["PLACE"]);
		$weight = ($arParams["WEIGTH"]/1000)." кг";
		imagettftext($im, $this->font_size, 0, 530, 1600, $black, $this->font, $weight);
		imagettftext($im, $this->font_size, 0, 930, 1600, $black, $this->font, $arParams["DELEVERY_ID"]);
		$lines = $this->splitLong($arParams["SHOP_NAME"]);
		$x = 1900;
		foreach ($lines as $line){
			imagettftext($im, $this->font_size, 0, 140, $x, $black, $this->font, Control::prepareRequestText($line));
			$x = $x + $this->line_height;
		}

		if ($arParams["LOGO"] != "") {
			imagecopy($im, $this->logo($arParams["LOGO"]), 500, 2150, 0, 0, $this->logo_width, $this->logo_height);
		}

		return $im;
	}


	function render($arParams) {
		$im = $this->generate($arParams);
		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
	}


	function save($arParams,$filename) {
		$im = $this->generate($arParams);
		header('Content-type: image/png');
		$res = imagepng($im,$filename);
		imagedestroy($im);
		return $res;
	}

}



$box = BoxTable::getById($_GET["box"]);
$order = OrderTable::getById($box["ORDER_ID"]);
$profile = ProfileTable::getById($box["PROFILE_ID"]);

$arParams = [
	"BOX"           => $order["EXT_ID"]."-".$box["NUM"],
	"EXT_ID"        => $order["EXT_ID"],
	"ORDER_ID"      => $order["ORDER_ID"],
	"DELEVERY_NAME" => ($profile["STICKER_DELIVERY"] ? $profile["STICKER_DELIVERY"] : "DPD"),
	"PLACE"         => $box["NUM"]."/".BoxTable::getCountInOrder($box["ORDER_ID"]),
	"WEIGTH"        => $box["WEIGHT"],
	"DELEVERY_ID"   => ($profile["STICKER_DELIVERY"] ? $profile["STICKER_DELIVERY"] : "DPD"),
	"SHOP_NAME"     => $profile["COMPANY"],
	"LOGO"          => ($profile["STICKER_LOGO"] > 0 ? $_SERVER["DOCUMENT_ROOT"].CFile::GetPath($profile["STICKER_LOGO"]) : ""),
];

$sticker = new sticker();
if (isset($_GET["filename"])) {
	$res = $sticker->save($arParams,$_GET["filename"]);
	if ($res){
		echo "OK";
	}
	else {
		echo "ERROR";
	}
} else {
	$sticker->render($arParams);
}
?>