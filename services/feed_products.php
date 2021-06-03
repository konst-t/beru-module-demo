<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('iplogic.beru');

$taskID = $_GET["param"];

$task = \Iplogic\Beru\TaskTable::getById($taskID);

\Iplogic\Beru\TaskTable::refreshYmlProducts($task);

?>