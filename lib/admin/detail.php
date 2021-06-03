<?
/* //////////////////////////////////////////////////////////////////////////////
*
*	Clssses for bitrix administrative section 
*	iPloGic solutions
*
*	V 3.0.0   30.10.19
*
*////////////////////////////////////////////////////////////////////////////////

namespace Iplogic\Beru\Admin;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;


abstract class Detail extends Page {

	public 
		$arTabs;

	protected
		$tabControl;


	public function initDetailPage() {
		$this->tabControl = new \CAdminTabControl("tabControl", $this->arTabs);
	}


	public function ActiveTabParam() {
		return $this->tabControl->ActiveTabParam();
	}


	public function buildPage() {
		global $APPLICATION;
		if (count($this->arContextMenu)) {
			$context = new \CAdminContextMenu($this->getContextMenu());
			$context->Show();
		}
		?>
		<form method="POST" Action="<?echo $APPLICATION->GetCurPageParam("",["mess","tabControl_active_tab"])?>" ENCTYPE="multipart/form-data" name="post_form">
		<?echo bitrix_sessid_post();
		$this->tabControl->Begin();
		$this->buildContent();
		$this->addButtons();
		?>
		<input type="hidden" name="lang" value="<?=LANG?>">
		<?
		$this->tabControl->End();
		?>
		</form>
		<?
	}


	protected function addButtons() {
		return;
	}


	abstract protected function buildContent();

}