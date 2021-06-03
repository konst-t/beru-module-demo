<?
/* //////////////////////////////////////////////////////////////////////////////
*
*	Clssses for bitrix administrative section 
*	iPloGic solutions
*
*	V 3.0.0   29.10.19
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


class TableList extends AdminList {

	public 
		$sTableClass = "";


	protected function getList($param) {
		$class = $this->sTableClass;
		return $class::getList($param);
	}


	protected function getById($ID) {
		$class = $this->sTableClass;
		return $class::getById($ID);
	}


	protected function update($ID,$arFields = []) {
		$class = $this->sTableClass;
		$result = $class::update($ID,$arFields);
		if ($result->isSuccess())
			return true;
		else {
			$this->errors[] = $this->Mess["SAVE_ERROR_UPDATE"]." (".$result->getErrorMessages().")";
			return false;
		}
	}


	protected function delete($ID) {
		$class = $this->sTableClass;
		return $class::delete($ID);
	}


	public function GroupAction() { 
		if(($arID = $this->obList->GroupAction()) && $this->POST_RIGHT=="W") { 

			if($_REQUEST['action_target']=='selected') {
				$rsData = $this->getList(['filter' => $this->arFilter]);
				while($arRes = $rsData->Fetch())
					$arID[] = $arRes['ID'];
			}

			foreach($arID as $ID) {
				if(strlen($ID)<=0)
					continue;
				$ID = IntVal($ID);

				switch($_REQUEST['action']) {
					case "delete":
						if(!$this->Delete($ID)) {
							$this->obList->AddGroupError($this->Mess["SAVE_ERROR_DELETE"], $ID);
						}
						break;
					case "activate":
					case "deactivate":
						if($arFields = $this->GetByID($ID)) {
							$arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
							if(!$this->Update($ID, $arFields))
								foreach ( $this->errors as $error )
									$this->obList->AddGroupError($error, $ID);
								$this->errors = [];
						}
						else
							$this->obList->AddGroupError($this->Mess["SAVE_ERROR_NO_ITEM"], $ID);
					break;
				}
				if(method_exists($this, "GroupActionEx")) {
					$this->GroupActionEx($ID);
				}
			}
			return true;
		}
		return false;
	}

	public function isGroupAction() {
		if($this->obList->GroupAction() && $this->POST_RIGHT=="W")
			return true;
		return false;
	}


}