<?
/* //////////////////////////////////////////////////////////////////////////////
*
*	Clssses for bitrix administrative section 
*	iPloGic solutions
*
*	V 3.1.0   18.03.20
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


class AdminList extends Page {

	public 
		$saved = "",
		$POST_RIGHT,
		$arSort = [],
		$arSelect = [],
		$arFilter = [],
		$gaDelete = "Y",
		$gaActivate = "Y",
		$gaDeactivate = "Y",
		$gaEdit = "Y",
		$gaCopy = "N",
		$footerTitle = "Y",
		$footerCounter = "Y",
		$defaultBy = 'SORT',
		$defaultOrder = 'ASC',
		$arItemContextMenu = [],
		$arGroupActions = [],
		$filterFormAction = false;

	protected
		$obFilter = false,
		$obList,
		$sTableID,
		$uniq,
		$arHeader = [];


	public function initList($sTableID) {
		global $_GET;
		$this->sTableID = $sTableID;

		/* SELECT */
		foreach ( $this->arOpts as $arProps ) {
			if ($arProps["PROPERTY"] == "Y"){
				$this->arSelect[] = "PROPERTY_".strtoupper($arProps["NAME"]);
			}
			else {
				$this->arSelect[] = strtoupper($arProps["NAME"]);
			}
			$header["content"] = $arProps["CAPTION"];
			$arHeaders[] = $header;
			if ( $arProps["UNIQ"] == "Y" ) {
				$this->uniq = "f_".strtoupper($arProps["NAME"]);
			}
		}

		/* SORT */
		$by = $GLOBALS["by"];
		$order = $GLOBALS["order"];
		if ($by == "")
			$GLOBALS["by"] = $by = $this->defaultBy;
		if ($order == "")
			$GLOBALS["order"] = $order = $this->defaultOrder;
		$by = strtoupper($by);
		$order = strtoupper($order);
		$oSort = new \CAdminSorting($sTableID, $by, $order);
		$this->arSort = [$by=>$order];

		/* MAIN OBJECT */
		$this->obList = new \CAdminList($sTableID, $oSort);

		/* FILTER */
		$FilterArr = [];
		$arPopup = [];
		foreach ( $this->arOpts as $arProps ) {
			if(is_array($arProps["FILTER"])) {
				$arPopup[] = $arProps["CAPTION"];
				if ( $arProps["FILTER"]["VIEW"] == "date-from-to" || $arProps["FILTER"]["VIEW"] == "num-from-to" ) {
					$FilterArr[] = "find_".$arProps["NAME"]."_from";
					$FilterArr[] = "find_".$arProps["NAME"]."_to";
				}
				else {
					$FilterArr[] = "find_".$arProps["NAME"];
				}
			}
		}
		if (count($FilterArr)) {
			$this->obList->InitFilter($FilterArr);
			if ($this->CheckFilter($FilterArr) && $_GET["del_filter"]!="Y") {
				foreach ( $this->arOpts as $arProps ) {
					if ( $arProps["FILTER"]["VIEW"] == "date-from-to" || $arProps["FILTER"]["VIEW"] == "num-from-to" ) {
						if ($GLOBALS["find_".$arProps["NAME"]."_from"]!=""){
							$this->arFilter[">=".strtoupper($arProps["NAME"])] = $GLOBALS["find_".$arProps["NAME"]."_from"];
						}
						if ($GLOBALS["find_".$arProps["NAME"]."_to"]!=""){
							$this->arFilter["<=".strtoupper($arProps["NAME"])] = $GLOBALS["find_".$arProps["NAME"]."_to"];
						}
					}
					else {
						if ($GLOBALS["find_".$arProps["NAME"]]!=""){
							$this->arFilter[$arProps["FILTER"]["COMPARE"].strtoupper($arProps["NAME"])] = $GLOBALS["find_".$arProps["NAME"]];
						}
					}
				}
				if(method_exists($this, "filterMod")) {
					$this->filterMod();
				}
			}
			$this->obFilter = new \CAdminFilter(
				$sTableID."_filter",
				$arPopup
			);
		}

		/* HEADER LINE */
		$arHeaders = [];
		foreach ( $this->arOpts as $arProps ) {
			if ($arProps["VIEW"] == "hidden")
				continue;
			$header = [];
			if (is_array($arProps["HEADER_KEY"]))
				$header = $arProps["HEADER_KEY"];
			if ($arProps["PROPERTY"] == "Y"){
				$header["id"] = "PROPERTY_".strtoupper($arProps["NAME"])."_VALUE";
				$header["sort"] = "PROPERTY_".strtoupper($arProps["NAME"]);
			}
			else {
				$header["id"] = strtoupper($arProps["NAME"]);
				$header["sort"] = strtoupper($arProps["NAME"]);
			}
			$header["content"] = $arProps["CAPTION"];
			if(!isset($header["default"]))
				$header["default"] = true;
			$arHeaders[] = $header;
		}
		$this->obList->AddHeaders($arHeaders);
	}


	public function prepareData($rsData) {
		$rsData = new \CAdminResult($rsData, $this->sTableID);
		$rsData->NavStart();
		$this->obList->NavText($rsData->GetNavPrint($this->Mess["TITLE"]));

		while($arRes = $rsData->NavNext(true, "f_")):
			$row =& $this->obList->AddRow($GLOBALS[$this->uniq], $arRes);
			foreach ( $this->arOpts as $arProps ) { 
				if ($arProps["VIEW"] == "hidden")
					continue;
				$fieldname = "f_".strtoupper($arProps["NAME"]);
				if ( isset($arProps["REPLACE"]) ) {
					$fieldname_r = $fieldname."_real";
					$GLOBALS[$fieldname_r] = $GLOBALS[$fieldname];
					$GLOBALS[$fieldname] = $arProps["REPLACE"][$GLOBALS[$fieldname]];
				}
				if ( isset($arProps["VIEW"]) ) {
					foreach($arProps["VIEW"] as $method => $options) {
						if (isset($options["PARAM"])) {
							if ( !is_array($options["PARAM"]) ) {
								$param = $options["PARAM"];
								foreach ( $this->arOpts as $arVars ) {
									$varname = "f_".strtoupper($arVars["NAME"]);
									$varname_r = $varname."_real";
									$param = str_replace("##".$arVars["NAME"]."##", $GLOBALS[$varname], $param);
									$param = str_replace("##".$arVars["NAME"]."_real##", $GLOBALS[$varname_r], $param);
								}
								if ($options["TYPE"] == "HREF") {
									$param = "<a href=\"".$param."\">".$GLOBALS[$fieldname]."</a>";
								}
								$options = $param;
							}
							else {
								$options = $options["PARAM"];
							}
							$row->$method(strtoupper($arProps["NAME"]),$options);
						}
						else {
							$row->$method(strtoupper($arProps["NAME"]));
						}
					}
				}
			}

			/* CONTEXT MENU */
			if(count($this->arItemContextMenu)){
				$arItemContext = $this->arItemContextMenu;
				foreach($arItemContext as $key => $item) {
					$arItemContext[$key]["ACTION"]["ID"] = $GLOBALS[$this->uniq];
				}
				$arItemContext = $this->contextMenuActions($arItemContext);

				if(is_set($arItemContext[count($arItemContext)-1], "SEPARATOR"))
					unset($arItemContext[count($arItemContext)-1]);
				$row->AddActions($arItemContext);
			}

		endwhile;

		/* CONTEXT MENU */
		$this->obList->AddAdminContextMenu($this->getContextMenu());

		$arFooter = [];
		if($this->footerTitle == "Y") $arFooter[] = ["title"=>$this->Mess["SELECTED"], "value"=>$rsData->SelectedRowsCount()];
		if($this->footerCounter == "Y") $arFooter[] = ["counter"=>true, "title"=>$this->Mess["CHECKED"], "value"=>"0"];
		if(count($arFooter)) {
			$this->obList->AddFooter($arFooter);
		}

		if($this->gaCopy == "Y") $this->arGroupActions["copy"] = $this->Mess["COPY"];
		if($this->gaDelete == "Y") $this->arGroupActions["delete"] = $this->Mess["DELETE"]; 
		if($this->gaActivate == "Y") $this->arGroupActions["activate"] = $this->Mess["ACTIVATE"];
		if($this->gaDeactivate == "Y") $this->arGroupActions["deactivate"] = $this->Mess["DEACTIVATE"]; 
		if(count($this->arGroupActions)) {
			$this->obList->AddGroupActionTable($this->arGroupActions);
		}

		$this->obList->CheckListMode();
	}


	public function EditAction() {
		global $FIELDS;
		if($this->obList->EditAction() && $this->POST_RIGHT=="W")
		{
			foreach($FIELDS as $ID=>$arFields) {
				if(!$this->obList->IsUpdated($ID))
					continue;

				$ID = IntVal($ID);
				if($arData = $this->GetByID($ID)) {
					foreach($arFields as $key=>$value)
						$arData[$key]=$value;
					if(!$this->Update($ID, $arData)) {
						foreach ( $this->errors as $error )
							$this->obList->AddGroupError($error, $ID);
						$this->errors = [];
					}
				}
				else {
					$this->obList->AddGroupError($this->obList->Mess["SAVE_ERROR_NO_ITEM"], $ID);
				}
			}
		}
	}


	public function isEditAction() {
		if($this->obList->EditAction() && $this->POST_RIGHT=="W")
			return true;
		return false;
	}


	public function renderList() {
		$this->renderFilter();
		$this->obList->DisplayList();
	}


	public function addError($err, $id=false) {
		$this->obList->AddGroupError($err, $id);
	}


	protected function renderFilter() {
		global $APPLICATION, $request;
		if(!$this->obFilter) return "";
		if (!$this->filterFormAction)
			$this->filterFormAction = $APPLICATION->GetCurPage();
		echo '<form name="find_form" method="get" action="'.$this->filterFormAction.'">';
		$this->obFilter->Begin();
		foreach ( $this->arOpts as $arProps ) {
			if (isset($arProps["FILTER"])) {
				$varname = "find_".$arProps["NAME"];
				echo "<tr><td>".$arProps["CAPTION"].":</td><td>";
				switch ($arProps["FILTER"]["VIEW"]) {
					case 'select':
						echo SelectBoxFromArray($varname, $arProps["FILTER"]["VALUES"], $GLOBALS[$varname], $arProps["FILTER"]["DEFAULT"], "");
						break;
					case 'date-from-to':
						$varname_from = $varname."_from";
						$varname_to = $varname."_to";
						echo CalendarPeriod(
							$varname . '_from',
							$GLOBALS[$varname_from] ? $GLOBALS[$varname_from] : null,
							$varname . '_to',
							$GLOBALS[$varname_to] ? $GLOBALS[$varname_to] : null,
							'Y'
						);
						break;
					case 'num-from-to':
						$varname_from = $varname."_from";
						$varname_to = $varname."_to";
						echo '<div class="adm-filter-alignment">
							<div class="adm-filter-box-sizing">
								<div class="adm-input-wrap" style="display: inline-block;">
									<input type="text" class="adm-input" id="'.$varname_from.'" name="'.$varname_from.'" size="15" value="'.($GLOBALS[$varname_from] ? $GLOBALS[$varname_from] : '').'">
								</div>
								<span class="adm-calendar-separate" style="display: inline-block"></span>
								<div class="adm-input-wrap" style="display: inline-block;">
									<input type="text" class="adm-input" id="'.$varname_to.'" name="'.$varname_to.'" size="15" value="'.($GLOBALS[$varname_to] ? $GLOBALS[$varname_to] : '').'">
								</div>
							</div>
						</div>';
						break;
					default:
						echo '<input type="text" name="find_'.$arProps["NAME"].'" size="47" value="'.htmlspecialchars($GLOBALS[$varname]).'">';
						break;
				}
				echo "</td></tr>";
			}
		}
		$this->obFilter->Buttons(array("table_id"=>$this->sTableID,"url"=>$this->filterFormAction,"form"=>"find_form"));
		$this->obFilter->End();
		echo '</form>';
	}


	protected function CheckFilter($FilterArr) {
		return count($this->obList->arFilterErrors) == 0;
	}


	protected function getUnixDate($str,$end=false) {
		$a = explode(" ", $str);
		if (strlen($a[0])){
			$arDate = explode(".",$a[0]);
			if (count($arDate)==3){
				$stDate = $arDate[2]."-".$arDate[1]."-".$arDate[0];
				$stTime = $a[0];
				if (!preg_match ( '/\d{2}:\d{2}:\d{2}/', $stTime)) {
					$stTime = "00:00:00";
					if ($end) {
						$stTime = "23:59:59";
					}
				}
				return strtotime($stDate." ".$stTime);
			}
		}
		return false;
	}

}