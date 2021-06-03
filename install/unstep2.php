<?if(!check_bitrix_sessid()) return;?>
<? use \Bitrix\Main\Localization\Loc; ?>
<?
echo CAdminMessage::ShowNote(Loc::getMessage("IPL_MA_MODULE_UNINSTALLED"));
?>
<a href="/bitrix/admin/partner_modules.php"><button><?=Loc::getMessage("BACK")?></button></a>