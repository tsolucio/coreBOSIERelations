<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'Smarty_setup.php';
require_once 'data/Tracker.php';
require_once 'modules/Users/LoginHistory.php';
require_once 'modules/Users/Users.php';
require_once 'include/logging.php';
require_once 'include/utils/utils.php';
require_once "include/events/VTWSEntityType.inc";

global $app_strings, $mod_strings, $current_language, $current_user, $adb, $theme, $currentModule;

$log = LoggerManager::getLogger('IERelations');

$theme_path='themes/'.$theme.'/';
$image_path=$theme_path.'images/';

$smarty = new vtigerCRM_Smarty();

$smarty->assign('MOD', $mod_strings);
$smarty->assign('APP', $app_strings);
$smarty->assign('THEME', $theme);
$smarty->assign('IMAGE_PATH', $image_path);
$smarty->assign('MODULELIST', getPicklistValuesSpecialUitypes(1613, '', ''));
$smarty->assign('SHOWIMPORTUPLOAD', 'yes');
if (isset($_REQUEST['_op']) && $_REQUEST['_op']=='uploadimportfile') {
	$smarty->assign('SHOWIMPORTUPLOAD', 'no');
	$ieformat = vtlib_purify($_REQUEST['xmlcsv']);
	$smarty->assign('IEFORMAT', $ieformat);
	$filename = 'cache/cbierelsxmlimport.'.($ieformat=='csv' ? 'csv' : 'xml');
	if (file_exists($filename)) {
		unlink($filename);
	}
	$upload_status = @move_uploaded_file($_FILES['xmlupload']['tmp_name'], $filename);
	if ($ieformat=='csv') {
		if (($handle = fopen($filename, 'r')) !== false) {
			$header = fgetcsv($handle, 1000, ',');
			$mainmodule = $header[0];
			$relmodule = array();
			for ($ms=1; $ms<count($header); $ms++) {
				$relmodule[] = $header[$ms];
			}
			fclose($handle);
		} else {
			$smarty->assign('ERROR_MESSAGE_CLASS', 'cb-alert-warning');
			$smarty->assign('ERROR_MESSAGE', getTranslatedString('ERR_FileFormat', $currentModule));
			$smarty->display('applicationmessage.tpl');
			die();
		}
	} else {
		try {
			$iexml = @new SimpleXMLElement(file_get_contents($filename));
		} catch (\Throwable $th) {
			$smarty->assign('ERROR_MESSAGE_CLASS', 'cb-alert-warning');
			$smarty->assign('ERROR_MESSAGE', getTranslatedString('ERR_FileFormat', $currentModule));
			$smarty->display('applicationmessage.tpl');
			die();
		}
		$mainmodule = (string)$iexml->origin;
		$relmodule = $iexml->relatedmodules->module;
	}
	$et = new VTWSEntityType($mainmodule, $current_user);
	$flabels = $et->getFieldLabels();
	$mainmod[$mainmodule] = array(
		'i18n' => getTranslatedString($mainmodule, $mainmodule),
		'fields' => $flabels
	);
	$smarty->assign('MAINMODTOSELECT', $mainmod);
	$relmods = array();
	foreach ($relmodule as $relmod) {
		$et = new VTWSEntityType((string)$relmod, $current_user);
		$flabels = $et->getFieldLabels();
		$relmods[(string)$relmod] = array(
			'i18n' => getTranslatedString((string)$relmod, (string)$relmod),
			'fields' => $flabels
		);
	}
	$smarty->assign('RELMODSTOSELECT', $relmods);
}
$smarty->display('modules/coreBOSIERelations/index.tpl');
?>
