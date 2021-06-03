<?
/* //////////////////////////////////////////////////////////////////////////////
*
*   Clssses for bitrix administrative section 
*   iPloGic solutions
*
*   V 3.0.0   30.10.19
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


class Info extends Detail {


	protected function buildContent() {
		foreach ( $this->arTabs as $num => $a ) {
			$this->tabControl->BeginNextTab();
			foreach ( $this->arOpts as $arProps ) {
				if ( $arProps["TAB"] == $num ) {
					echo $arProps["INFO"];
				}
			}
		}

	}


}