<?

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
	'iplogic.beru', 
	[
		"Iplogic\Beru\Admin\Page" 			=> "lib/admin/page.php",
		"Iplogic\Beru\Admin\AdminList" 		=> "lib/admin/list.php",
		"Iplogic\Beru\Admin\TableList" 		=> "lib/admin/tablelist.php",
		"Iplogic\Beru\Admin\Detail" 		=> "lib/admin/detail.php",
		"Iplogic\Beru\Admin\Info" 			=> "lib/admin/info.php",
		"Iplogic\Beru\Admin\Form" 			=> "lib/admin/form.php",
		"Iplogic\Beru\Admin\TableForm" 		=> "lib/admin/tableform.php",
		"Iplogic\Beru\Admin\OptionsForm" 	=> "lib/admin/optionsform.php",

		"Iplogic\Beru\Control" 		=> "lib/control.php",
		"Iplogic\Beru\YMAPI" 		=> "lib/ymapi.php",
		"Iplogic\Beru\YML" 			=> "lib/yml.php",
		"Iplogic\Beru\Profile" 		=> "lib/profile.php",
		"Iplogic\Beru\Product" 		=> "lib/product.php",
		"Iplogic\Beru\Order" 		=> "lib/order.php",
		"Iplogic\Beru\Error" 		=> "lib/error.php",
		"Iplogic\Beru\ApiLog" 		=> "lib/apilog.php",
		"Iplogic\Beru\Task" 		=> "lib/task.php",
		"Iplogic\Beru\Box" 			=> "lib/box.php",
		"Iplogic\Beru\BoxLink" 		=> "lib/boxlink.php",
	]
);
