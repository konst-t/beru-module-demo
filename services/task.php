<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('iplogic.beru');

\Iplogic\Beru\TaskTable::executeNextTask();

?>