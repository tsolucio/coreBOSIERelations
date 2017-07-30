<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('modules/Users/LoginHistory.php');
require_once('modules/Users/Users.php');
require_once('include/logging.php');
require_once('include/utils/utils.php');
require_once("include/events/VTWSEntityType.inc");

global $app_strings, $mod_strings, $current_language, $current_user, $adb, $theme, $currentModule;

$log = LoggerManager::getLogger('IERelations');

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty = new vtigerCRM_Smarty();

$smarty->assign('MOD', $mod_strings);
$smarty->assign('APP', $app_strings);
$smarty->assign('THEME', $theme);
$smarty->assign('IMAGE_PATH',$image_path);
$smarty->assign('MODULELIST', getPicklistValuesSpecialUitypes(1613,'',''));
$smarty->assign('SHOWIMPORTUPLOAD','yes');
if (isset($_REQUEST['_op']) and $_REQUEST['_op']=='uploadimportfile') {
	$smarty->assign('SHOWIMPORTUPLOAD','no');
	if (file_exists('cache/cbierelsxmlimport.xml')) unlink('cache/cbierelsxmlimport.xml');
	$upload_status = @move_uploaded_file($_FILES['xmlupload']['tmp_name'], 'cache/cbierelsxmlimport.xml');
	$iexml = new SimpleXMLElement(file_get_contents('cache/cbierelsxmlimport.xml'));
	$mainmodule = (string)$iexml->origin;
	$et = new VTWSEntityType($mainmodule,$current_user);
	$flabels = $et->getFieldLabels();
	$mainmod[$mainmodule] = array(
		'i18n' => getTranslatedString($mainmodule,$mainmodule),
		'fields' => $flabels
	);
	$smarty->assign('MAINMODTOSELECT',$mainmod);
	$relmods = array();
	foreach ($iexml->relatedmodules->module as $relmod) {
		$et = new VTWSEntityType((string)$relmod,$current_user);
		$flabels = $et->getFieldLabels();
		$relmods[(string)$relmod] = array(
			'i18n' => getTranslatedString((string)$relmod,(string)$relmod),
			'fields' => $flabels
		);
	}
	$smarty->assign('RELMODSTOSELECT',$relmods);
}
$smarty->display('modules/coreBOSIERelations/index.tpl');
?>
