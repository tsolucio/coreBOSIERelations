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

$smarty->display('modules/coreBOSIERelations/index.tpl');
?>
