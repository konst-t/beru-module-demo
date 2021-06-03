<?
use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\IO\Directory,
	\Bitrix\Main\Application,
	\Bitrix\Main\EventManager,
	\Bitrix\Main\ModuleManager,
	\Iplogic\Beru\ProfileTable;

Loc::loadMessages(__FILE__);
Class iplogic_beru extends CModule
{
	const MODULE_ID = 'iplogic.beru';
	var $MODULE_ID = 'iplogic.beru';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("IPL_MA_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("IPL_MA_MODULE_DESC");

		$this->PARTNER_NAME = Loc::getMessage("IPL_MA_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("IPL_MA_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{

		global $DB, $APPLICATION;

		$this->errors = false;
		$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/".strtolower($DB->type)."/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
		Loader::includeModule($this->MODULE_ID); 
		EventManager::getInstance()->registerEventHandler('iblock', 'OnAfterIBlockElementUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'iblockAfterUpdateHandler');
		EventManager::getInstance()->registerEventHandler('catalog', 'OnProductUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'productUpdateHandler');
		EventManager::getInstance()->registerEventHandler('catalog', '\Bitrix\Catalog\Price::OnAfterUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'priceUpdateHandler');
		EventManager::getInstance()->registerEventHandler('catalog', 'OnStoreProductUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'storeUpdateHandler');
		EventManager::getInstance()->registerEventHandler('iblock', 'OnBeforeIBlockElementDelete', self::MODULE_ID, 'Iplogic\Beru\Control', 'productDeleteHandler');
		$taid = CAgent::AddAgent( "\Iplogic\Beru\Control::executeTasksAgent();", self::MODULE_ID, "N", 60, "", "Y");

		Option::set(self::MODULE_ID,"GROUP_DEFAULT_RIGHT",'R');
		Option::set(self::MODULE_ID,"use_log",'Y');
		Option::set(self::MODULE_ID,"dont_log_ok",'N');
		Option::set(self::MODULE_ID,"log_in_menu",'Y');
		Option::set(self::MODULE_ID,"last_task_time",0);
		Option::set(self::MODULE_ID,"task_trying_num",3);
		Option::set(self::MODULE_ID,"task_trying_period",60);
		Option::set(self::MODULE_ID,"allow_multichain_tasks","N");
		Option::set(self::MODULE_ID,"can_execute_tasks","Y");
		Option::set(self::MODULE_ID,"products_check_period",1);
		Option::set(self::MODULE_ID,"products_add_num",50);
		Option::set(self::MODULE_ID,"products_check_disable","N");
		Option::set(self::MODULE_ID,"products_check_last_time",0);
		Option::set(self::MODULE_ID,"domen",$_SERVER['HTTP_HOST']);
		Option::set(self::MODULE_ID,"keep_temp_files_days",30);
		Option::set(self::MODULE_ID,"keep_log_days",0);
		Option::set(self::MODULE_ID,"menu_sort_index",10);

		$dbEvent = CEventType::GetList(Array("TYPE_ID" => "IPL_MA_STATUS_CHANGE"));
		if(!($dbEvent->Fetch()))
		{
			$res = \CSite::GetList($by="sort", $order="asc");
			$arSites = [];
			while ($res_arr = $res->Fetch()) {
				$arSites[] = $res_arr["ID"];
			}
			$et = new CEventType;
			$et->Add([
				"LID" => LANGUAGE_ID,
				"EVENT_NAME" => "IPL_MA_STATUS_CHANGE",
				"NAME" => Loc::getMessage("IPL_MA_STATUS_CHANGE_NAME"),
				"DESCRIPTION" => Loc::getMessage("IPL_MA_STATUS_CHANGE_DESC"),
			]);
			$emess = new CEventMessage;
			$emess->Add([
				"ACTIVE" => "Y",
				"EVENT_NAME" => "IPL_MA_STATUS_CHANGE",
				"LID" => $arSites,
				"EMAIL_FROM" => "#SALE_EMAIL#",
				"EMAIL_TO" => "#SALE_EMAIL#",
				"SUBJECT" => Loc::getMessage("IPL_MA_STATUS_CHANGE_SUBJECT"),
				"MESSAGE" => Loc::getMessage("IPL_MA_STATUS_CHANGE_MESSAGE"),
				"BODY_TYPE" => "text",
			]);
		}

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		if (!$arParams['savedata'])
		{
			Loader::includeModule($this->MODULE_ID);
			$rsData = ProfileTable::getList([],[],["ID","BASE_URL"]);
			while ( $arProfile = $rsData->Fetch() ) {
				\CUrlRewriter::Delete([
					'CONDITION' => '#^'.$arProfile["BASE_URL"].'#',
				]);
			}
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/".strtolower($DB->type)."/uninstall.sql");
		}

		if(!empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnAfterIBlockElementUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'iblockAfterUpdateHandler');
		EventManager::getInstance()->unRegisterEventHandler('catalog', 'OnProductUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'productUpdateHandler');
		EventManager::getInstance()->unRegisterEventHandler('catalog', '\Bitrix\Catalog\Price::OnAfterUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'priceUpdateHandler');
		EventManager::getInstance()->unRegisterEventHandler('catalog', 'OnStoreProductUpdate', self::MODULE_ID, 'Iplogic\Beru\Control', 'storeUpdateHandler');
		EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnBeforeIBlockElementDelete', self::MODULE_ID, 'Iplogic\Beru\Control', 'productDeleteHandler');

		CAgent::RemoveModuleAgents(self::MODULE_ID);
		Option::delete($this->MODULE_ID);
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles(__DIR__.'/admin/', Application::getDocumentRoot().'/bitrix/admin', true);
		CopyDirFiles(__DIR__.'/css/', Application::getDocumentRoot().'/bitrix/css/'.$this->MODULE_ID, true, true);
		CopyDirFiles(__DIR__.'/images/', Application::getDocumentRoot().'/bitrix/images/'.$this->MODULE_ID, true, true);
		CopyDirFiles(__DIR__."/services/", Application::getDocumentRoot()."/bitrix/services", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles(__DIR__.'/admin/', Application::getDocumentRoot().'/bitrix/admin');
		DeleteDirFilesEx('/bitrix/css/'.$this->MODULE_ID.'/');
		DeleteDirFilesEx('/bitrix/images/'.$this->MODULE_ID.'/');
		DeleteDirFilesEx('/bitrix/services/iplogic/mkpapi/');
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		ModuleManager::registerModule($this->MODULE_ID);
		$this->InstallDB();
		$APPLICATION->IncludeAdminFile(Loc::getMessage("IPL_MA_MODULE_INSTALLED_TITLE"), Application::getDocumentRoot()."/bitrix/modules/".$this->MODULE_ID."/install/step.php");
		return true;
	}

	function DoUninstall()
	{

		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IPL_MA_MODULE_UNINSTALLED_TITLE"), Application::getDocumentRoot()."/bitrix/modules/".$this->MODULE_ID."/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			ModuleManager::unRegisterModule(self::MODULE_ID);
			$APPLICATION->IncludeAdminFile(Loc::getMessage("IPL_MA_MODULE_UNINSTALLED_TITLE"), Application::getDocumentRoot()."/bitrix/modules/".$this->MODULE_ID."/install/unstep2.php");
		}
		return true;
	}
}
?>
