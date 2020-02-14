<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/*
 * This SSE is based on this article:
 * http://www.htmlgoodies.com/beyond/php/show-progress-report-for-long-running-php-scripts.html
 */
require_once 'modules/coreBOSIERelations/cbieopsutils.php';

global $current_user, $mod_strings;

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
set_time_limit(0);

function send_message($id, $message, $progress, $processed, $total) {
	$d = array('message' => $message , 'progress' => $progress, 'processed' => $processed, 'total' => $total);
	echo "id: $id" . PHP_EOL;
	echo 'data: ' . json_encode($d) . PHP_EOL;
	echo PHP_EOL;
	ob_flush();
	flush();
}

$params = json_decode(vtlib_purify($_REQUEST['params']), true);
$iexml = new SimpleXMLElement(file_get_contents('cache/cbierelsxmlimport.xml'));
$mainmodule = (string)$iexml->origin;
$minfo = __getrelmoduleinfo($mainmodule);
$mod = Vtiger_Module::getInstance($mainmodule);
$fld = Vtiger_Field::getInstance($params[$mainmodule], $mod);
$qg = new QueryGenerator($mainmodule, $current_user);
$qg->setFields(array('id',$minfo['autonumfield'],$params[$mainmodule]));
$mmsql = $qg->getQuery(). ' and ('.$fld->table.'.'.$fld->column.'=? or '.$fld->table.'.'.$fld->column.'=?)';
$mainmod = array(
	'i18n' => getTranslatedString($mainmodule, $mainmodule),
	'focus' => CRMEntity::getInstance($mainmodule),
	'modinfo' => $minfo,
	'query' => $mmsql,
);

$relmods = array();
foreach ($iexml->relatedmodules->module as $relmod) {
	$minfo = __getrelmoduleinfo((string)$relmod);
	$mod = Vtiger_Module::getInstance((string)$relmod);
	$fld = Vtiger_Field::getInstance($params[(string)$relmod], $mod);
	$qg = new QueryGenerator((string)$relmod, $current_user);
	$qg->setFields(array('id',$minfo['autonumfield'],$params[(string)$relmod]));
	$rmsql = $qg->getQuery(). ' and ('.$fld->table.'.'.$fld->column.'=? or '.$fld->table.'.'.$fld->column.'=?)';
	$relmods[(string)$relmod] = array(
		'i18n' => getTranslatedString((string)$relmod, (string)$relmod),
		'focus' => CRMEntity::getInstance((string)$relmod),
		'modinfo' => $minfo,
		'query' => $rmsql,
	);
}

$recordcount = $iexml->relations->record->count();
$recordprocessed = 0;
$id = 1;
foreach ($iexml->relations->record as $record) {
	$crmid = (string)$record->entityid->crmid;
	$anum = (string)$record->entityid->autonumid;
	$rs = $adb->pquery($mmsql, array($crmid,$anum));
	if ($rs && $adb->num_rows($rs)>0) {
		$localcrmid = $adb->query_result($rs, 0, 0);
		$msg = $mod_strings['FoundRecords'] . " <a href='index.php?module=$mainmodule&action=DetailView&record=$localcrmid'>$crmid/$anum</a><br>";
		foreach ($record->modules->module as $relmod) {
			$relcrmids = explode(',', (string)$relmod->relentityids);
			$relanids = explode(',', (string)$relmod->relentityans);
			$relwith = array();
			$found = 0;
			for ($index=0; $index<count($relcrmids); $index++) {
				$rsrel = $adb->pquery($relmods[(string)$relmod->modulename]['query'], array($relcrmids[$index], $relanids[$index]));
				if ($rsrel && $adb->num_rows($rsrel)>0) {
					$found++;
					$relwith[] = $adb->query_result($rsrel, 0, 0);
				}
			}
			if (count($relwith)>0) {
				relateEntities($mainmod['focus'], $mainmodule, $localcrmid, (string)$relmod->modulename, $relwith);
			}
			$msg.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings['Related with'] . ' '.$found.'/'.count($relcrmids).' '.$relmods[(string)$relmod->modulename]['i18n'].'<br>';
		}
	} else {
		$msg = sprintf($mod_strings['NotFoundRecords'], "$crmid/$anum", $params[$mainmodule]);
	}
	$recordprocessed++;
	$progress = $recordprocessed / $recordcount * 100;
	send_message($id++, $msg, $progress, $recordprocessed, $recordcount);
}

send_message('CLOSE', $mod_strings['Process complete'], 100, $recordcount, $recordcount);
?>
