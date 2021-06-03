<?
/* //////////////////////////////////////////////////////////////////////////////
*
*	Clssses for bitrix administrative section 
*	iPloGic solutions
*
*	V 3.1.2   11.12.19
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


class Form extends Detail {

	public
		$captionWidth = 50;


	public function buildContent() {
		$inputWidth = 100 - $this->captionWidth;
		foreach ( $this->arTabs as $num => $a ) {
			$this->tabControl->BeginNextTab();
			foreach ( $this->arOpts as $option => $arProps ) {
				if ( $arProps["TAB"] == $a["DIV"] ) {
					$name = "str_".$option;
					$class="";
					if ($arProps["REQURIED"]=="Y")
						$caption = "<td width=\"".$this->captionWidth."%\"><b>".$arProps["NAME"]."</b></td>";
					else
						$caption = "<td width=\"".$this->captionWidth."%\">".$arProps["NAME"]."</td>";
					if ( isset($arProps["CLASS"]) && $arProps["CLASS"] != "" ) { 
						$class = ' class="'.$arProps["CLASS"].'"';
					}

					if ( $arProps["TYPE"] == "heading" ) { ?>
						<tr class="heading"><td colspan="2"<?=$class?>><?=$arProps["TEXT"]?></td></tr>
					<? }

					if ( $arProps["TYPE"] == "info" ) { ?>
						<tr>
							<td colspan="2" align="center">
								<div class="adm-info-message-wrap" align="center">
									<div class="adm-info-message"><?=$arProps["TEXT"]?></div>
								</div>
							</td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "textarea" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><textarea name="<?=$option?>" cols="32" rows="3" wrap="VIRTUAL"><?=$arProps["VALUE"]?></textarea></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "bigtextarea" ) { ?>
						<tr<?=$class?>>
							<td colspan="2"><textarea name="<?=$option?>" style="width:100%;height:500px;" wrap="VIRTUAL"><?=$arProps["VALUE"]?></textarea></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "text" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><input type="text" name="<?=$option?>" value='<?=$arProps["VALUE"]?>' size="30" maxlength="225"></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "color" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><input type="text" name="<?=$option?>" value='<?=$arProps["VALUE"]?>' size="30" maxlength="225" class="jscolor"></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "checkbox" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><input type="checkbox" name="<?=$option?>" value="Y"<?if($arProps["VALUE"] == "Y") echo " checked"?>></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "radio" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%">
								<? foreach($arProps["VALUES"] as $val) { ?>
									<div style="margin-bottom:10px;">
										<input type="radio" name="<?=$option?>" value="<?=$val["VALUE"]?>"<?if($arProps["VALUE"] == $val["VALUE"]) echo " checked"?>> <?=$val["LABEL"]?>
									</div>
								<? } ?>
							</td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "select" ) { 
						if (!is_array($arProps["OPTIONS"])) {
							$arProps["OPTIONS"] = $this->getStandartArray($arProps["OPTIONS"]);
						}
						?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%">
								<select name="<?=$option?><?=($arProps["MULTIPLE"]=="Y" ? "[]\" multiple" : "\"")?><?=($arProps["SIZE"]>0 ? " size=\"".$arProps["SIZE"]."\"" : "")?>>
									<? foreach($arProps["OPTIONS"] as $val => $text) {
										$selected = false;
										if ( $arProps["MULTIPLE"] == "Y" ) {
											if (in_array($val, $arProps["VALUE"])) $selected = true;
										}
										else {
											if ($arProps["VALUE"] == $val) $selected = true;
										}
										?>
										<option value="<?=$val?>"<? echo ($selected ? ' selected' : ''); ?>><?=$text?></option>
									<? } ?>
								</select>
							</td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "file" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><? echo self::__ShowFilePropertyField($option, $arProps); ?></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "action" ) { ?>
						<tr<?=$class?>>
							<td width="100%" colspan="2"><? echo $this->__ShowAction($option, $arProps); ?></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "hidden" ) { ?>
						<input type="hidden" name="<?=$option?>" value="<?=$arProps["VALUE"]?>">
					<? }

					if ( $arProps["TYPE"] == "html" ) { 
						foreach ( $this->arOpts as $vname => $arVar ) {
							$arProps["HTML"] = str_replace("##".$vname."##", $arVar["VALUE"], $arProps["HTML"]);
						}
						?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><?=$arProps["HTML"]?></td>
						</tr>
					<? }

					if ( $arProps["TYPE"] == "prop_choose" ) { ?>
						<tr<?=$class?>>
							<?=$caption?>
							<td width="<?=$inputWidth?>%"><? echo $this->__ShowPropChoose($option, $arProps); ?></td>
						</tr>
					<? }

				}
			}
		}

	}


	protected function addButtons() {
		$this->tabControl->Buttons(["disabled"=>($this->POST_RIGHT<"W"), "back_url"=>"?lang=".LANG]);
	}


	public function getIBlockChooseScript($fieldIblockID, $fieldType, $fieldSite = false) {
		echo "<script>
			$(document).ready(function(){
				opts = ".\CUtil::PhpToJsObject($this->arIBlocksJS).";
				function setIBlocks(first) {
					var site = $(\"select[name='".$fieldSite."']\").val(),
						type = $(\"select[name='".$fieldType."']\").val();
					$(\"select[name='".$fieldIblockID."'] > option\").show();
					if (first!=true) { $(\"select[name='".$fieldIblockID."']\").val(\"\"); }
					$(\"select[name='".$fieldIblockID."'] > option\").each(function(){
						var id = $(this).val();
						if (site != undefined) {
							if (opts[id].lid != site) {
								$(this).hide();
							}
						}
						if (type != undefined && type != 0) {
							if (opts[id].type != type) {
								$(this).hide();
							}
						}
					});
				}
				$(\"select[name='".$fieldType."']\").on(\"change\", function(){setIBlocks();});
				$(\"select[name='".$fieldSite."']\").on(\"change\", function(){setIBlocks();});
				setIBlocks(true);
			});
		</script>";
	}


	public function getPropChooseScript() {
		echo "<script>
			$(document).ready(function(){
				var choose_prop_type_first = true;
				$(\"select[data-select-type='choose-prop-type'\").on(\"change\", function(){
					var prop_select = $(this).siblings(\"select\"),
						prop_text = $(this).siblings(\"input[type='text']\"),
						type = $(this).val(),
						first = true,
						show = false;
					if(type == \"empty\") {
						prop_select.hide();
						choose_prop_type_first = false;
						return false;
					}
					if(type == \"permanent_text\") {
						prop_select.hide();
						prop_text.show();
						choose_prop_type_first = false;
						return false;
					}
					else {
						prop_select.show();
						prop_text.hide();
					}
					$(\" > option\", prop_select).each(function( index ){
						$(this).hide();
						if ($(this).attr(\"data-type\") == type) {
							$(this).show();
							show = true;
							if(first && !choose_prop_type_first) { 
								prop_select.prop('selectedIndex', index);
								first = false;
							}
						}
					});
					if(!show) {
						prop_select.hide();
					}
					else {
						prop_select.show();
					}
					choose_prop_type_first = false;
				});
				$(\"select[data-select-type='choose-prop-type'\").each(function(){
					choose_prop_type_first = true;
					$(this).change();
				});

			});
		</script>";
	}


	protected function __ShowPropChoose($fname, $arProps){
		$type_options = "";
		$prop_options = "";
		foreach($arProps["TYPES"] as $val => $name){
			$tselected = false;
			if ($this->arOpts[$fname."_TYPE"]["VALUE"] == $val) $tselected = true;
			$type_options .= "<option value=\"".$val."\"".($tselected ? ' selected' : '').">".$name."</option>";
			$standartArray = $this->getStandartArray($val);
			if (is_array($standartArray)) {
				foreach($standartArray as $key => $capt)  {
					$selected = false;
					if ($arProps["VALUE"] == $key && $tselected) $selected = true;
					$prop_options .= "<option value=\"".$key."\"".($selected ? ' selected' : '')." data-type=\"".$val."\">".$capt."</option>";
				}
			}
		}
		$result .= "<select name=\"".$fname."_TYPE\" data-select-type=\"choose-prop-type\">".$type_options."</select>&nbsp;&nbsp;";
		$result .= "<select name=\"".$fname."\">".$prop_options."</select>";
		return $result;
	}


	protected static function __ShowFilePropertyField($name, $arProps){
		$arOption = [
			"name" => $name,
			"description" => true,
			"upload" => true,
			"allowUpload" => $arProps["FILE_TYPE"],
			"medialib" => true,
			"fileDialog" => true,
			"cloud" => true,
			"delete" => true,
		];
		if($arProps['MULTIPLE'] == 'N'){
			$arOption["maxCount"] = 1;
		}
		else {
			$arOption["name"] = $name."[n#IND#]";
			$arOption["id"] = $name."[n#IND#]_".mt_rand(1, 1000000);
		}
		echo \Bitrix\Main\UI\FileInput::createInstance($arOption)->show($arProps["VALUE"]);
	}


	protected function __ShowAction($id, $arProps) {
		echo "<div id=\"".$id."_wrapper\"></div><br><button id=\"".$id."\">".$arProps["BUTTON"]."</button>
			<script>
				$(function(){
					var timerId;
					function refresh() {
						$.post('/bitrix/modules/".$this->module_id."/admin/services/process.ajax.php',{url:'".$arProps["PERCENTS"]."'},function(data_){ 
							var data=$.parseJSON(data_);
							$('#".$id."_wrapper').html(data.HTML);
							if (+data.PERCENT==100) {
								clearInterval(timerId);
								$('#".$id."').prop('disabled', false);
							}
						});
					}
					$('#".$id."').on('click',function () {
						$(this).prop('disabled', true);
						timerId=setInterval(refresh, 1000);
						return false;
					});
				});
				$(function(){
					$('#".$id."').on('click',function () {
						$.ajax('".$arProps["SCRIPT"]."');
						return false;
					});
				});
			</script>
		";
	}


	public function getRequestData() {
		global $request;
		foreach ( $this->arOpts as $option => $arProps ) {

			if ( 
				!$this->isServiceOption($option) 
				&& $arProps["TYPE"] != "file"
				&& $arProps["TYPE"] != "checkbox"
			) {
				$this->arOpts[$option]["VALUE"] = $request->get($option);
			}

			if ( $arProps["TYPE"] == "checkbox" ) {
				$this->arOpts[$option]["VALUE"] =($request->get($option) == "Y" ? "Y" : "N");
			}

			if ( $arProps["TYPE"] == "file" ) {
				if ($arProps["MULTIPLE"] != "Y") {
					$this->arOpts[$option]["VALUE"] = $request->get($option);
					$delMark = $option."_del";
					if (is_array($this->arOpts[$option]["VALUE"])) {
						$arFile = [
							"name" => $this->arOpts[$option]["VALUE"]["name"],
							"size" => $this->arOpts[$option]["VALUE"]["size"],
							"tmp_name" => Application::getDocumentRoot()."/".Option::get("main", "upload_dir", "upload")."/tmp".$this->arOpts[$option]["VALUE"]["tmp_name"],
							"type" => $this->arOpts[$option]["VALUE"]["type"],
							"MODULE_ID" => $this->module_id,
						];
						$ID = \CFile::SaveFile($arFile, "iplogic/img");
						if( $ID > 0 ) {
							$this->arOpts[$option]["VALUE"] = $ID;
						}
						else {
							$this->errors[] = $this->Mess["FILE_SAVE_ERROR"];
							$this->arOpts[$option]["VALUE"] = "";
						}
					}
					elseif ( (int)$this->arOpts[$option]["VALUE"] < 1 || $request->get($delMark) == "Y" ) {
						if ($request->get($delMark) == "Y") { 
							\CFile::Delete($this->arOpts[$option]["VALUE"]);
						}
						$this->arOpts[$option]["VALUE"] = "";
					}
				}
				else {
					$temp_val = $request->get($option);
					if (isset($temp_val) && count($temp_val)) { 
						$this->arOpts[$option]["VALUE"] = $request->get($option);
						$IDS = [];
						$i = 0;
						$arDel = $request->get($option."_del");
						foreach ($temp_val as $key => $img) {
							if ($arDel[$key]!="Y"){
								if (is_array($img)) {
									$img["tmp_name"] = Application::getDocumentRoot()."/".Option::get("main", "upload_dir", "upload")."/tmp".$img["tmp_name"];
									$ID = \CFile::SaveFile($img, "iplogic/img"); 
									if( $ID > 0 ) {
										$IDS[$option."[n".$i."]"] = $ID;
									}
									else {
										$this->errors[] = $this->Mess["FILE_SAVE_ERROR"];
									}
								}
								else {
									$IDS[$option."[n".$i."]"] = $img;
								}
								$i++;
							}
							else {
								\CFile::Delete($img);
							}
						}
						$this->arOpts[$option]["VALUE"] = $IDS; 
					}
					else {
						$this->arOpts[$option]["VALUE"] = unserialize(Option::get($this->module_id, $this->namePrefix.$option, $arProps["DEFAULT"], self::getLID()));
					}
				}
			}

		}
	}


	public function extractQueryValues() {
		$arFields = [];
		foreach ( $this->arOpts as $option => $arProps ) {
			if ( !$this->isServiceOption($option) ) { 
				$arFields[$option] = $arProps["VALUE"];
			} 
			elseif ($arProps["TYPE"] == "group") {
				foreach($arProps["ITEMS"] as $item) {
					$arFields[$option][$item] = $this->arOpts[$item]["VALUE"];
					unset($arFields[$item]);
				}
			}
		}
		return $arFields;
	}


	protected function isServiceOption($opt) {
		$arSer = ["heading","info", "html", "group"];
		if ( !in_array($this->arOpts[$opt]["TYPE"], $arSer) ) { 
			return false;
		}
		return true;
	}


	protected function validateOptions() {}




}