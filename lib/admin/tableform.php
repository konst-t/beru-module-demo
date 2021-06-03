<?
/* //////////////////////////////////////////////////////////////////////////////
*
*	Clssses for bitrix administrative section 
*	iPloGic solutions
*
*	V 3.0.0   25.11.19
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;


class TableForm extends Form {


	public function setFields($arFields) {
		foreach ( $this->arOpts as $option => $arProps ) {
			if ( !$this->isServiceOption($option) ) { 
				$this->arOpts[$option]["VALUE"] = $arFields[$option];
				if (!isset($arFields[$option]))
					$this->arOpts[$option]["VALUE"] = $arProps["DEFAULT"];
				if ($arProps["MULTIPLE"] == "Y")
					$this->arOpts[$option]["VALUE"] = unserialize($this->arOpts[$option]["VALUE"]);
			} 
			elseif ($arProps["TYPE"] == "group") {
				foreach($arProps["ITEMS"] as $item) {
					$arGroup = $arFields[$option];
					$this->arOpts[$item]["VALUE"] = $arGroup[$item];
				}
			}
			if ($arProps["TYPE"] == "prop_choose") {
				$this->arOpts[$option."_TYPE"]["VALUE"] = $arFields[$option."_TYPE"];
			}
		} 
	}


}