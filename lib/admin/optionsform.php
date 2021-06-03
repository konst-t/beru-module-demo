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

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


class OptionsForm extends Form {


	public function processingOptions($options = []) {
		global $request;
		if( $request->isPost() && ($request->get("save")!="" || $request->get("apply")!="") && $this->POST_RIGHT=="W" && check_bitrix_sessid() ) {
			$this->getRequestData();
			$this->validateOptions();
			if ( !count($this->errors) ) {
				$this->saveOptions($options);
			}
		}
		else {
			$this->getOptions();
		}
	}


	protected function saveOptions($options=[]) {
			foreach ( $this->arOpts as $option => $arProps ) {
				if ( !$this->isServiceOption($option) ) {
					if ( is_array($arProps["VALUE"]) )
						$arProps["VALUE"] = serialize($arProps["VALUE"]);
					Option::set($this->module_id, $this->namePrefix.$option, $arProps["VALUE"], self::getLID());
				}
			}
			if ( isset($options["CACHE"]) && is_array($options["CACHE"]) ) {
				$this->createOptionsCache($options["CACHE"]);
			}
			$this->saved = "ok";
	}


	protected function createOptionsCache($options) {
		$arOptions = [];
		foreach ( $this->arOpts as $option => $arProps ) {
			if ( !$this->isServiceOption($option) ) { 
				$arOptions[$option] = Option::get($this->module_id, $this->namePrefix.$option, "", self::getLID());
			}
		}
		file_put_contents(Application::getDocumentRoot()."/".Option::get("main", "upload_dir", "upload")."/iplogic/cache/".$options["FILENAME"], serialize($arOptions));
	}


	protected function getOptions() {
		foreach ( $this->arOpts as $option => $arProps ) {
			if ( !$this->isServiceOption($option) ) { 
				$this->arOpts[$option]["VALUE"] = Option::get($this->module_id, $this->namePrefix.$option, $arProps["DEFAULT"], self::getLID());
				if ($arProps["MULTIPLE"] == "Y")
					$this->arOpts[$option]["VALUE"] = unserialize($this->arOpts[$option]["VALUE"]);
			} 
		} 
	}


}