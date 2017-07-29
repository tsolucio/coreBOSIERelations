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
require_once 'modules/coreBOSIERelations/cbieopsutils.php';
$ret = array();
switch ($_REQUEST['_op']) {
	case 'getRelatedModules':
		$mod = vtlib_purify($_REQUEST['mod']);
		$relatedmods = __getcbIERelatedLists($mod,null);
		$smarty = new vtigerCRM_Smarty();
		$smarty->assign('RELMODULELIST', $relatedmods);
		$smarty->display('modules/coreBOSIERelations/relatedmodules.tpl');
		die();
		break;
	case 'getXMLExport':
		$module = vtlib_purify($_REQUEST['mod']);
		$relations = explode(',', vtlib_purify(trim($_REQUEST['relmods'],',')));
		__createExportFile($module,$relations);
		die();
		break;
	default:
		break;
}
echo json_encode($ret);
?>
