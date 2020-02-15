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
$ieformat = $params['ieformat'];
$filename = 'cache/cbierelsxmlimport.'.($ieformat=='csv' ? 'csv' : 'xml');
if ($ieformat=='csv') {
	if (($handle = fopen($filename, 'r')) !== false) {
		$header = fgetcsv($handle, 5000, ',');
		$mainmodule = $header[0];
		$relmodule = array();
		$relmodcachename = $relmodcacheid = array();
		for ($ms=1; $ms<count($header); $ms++) {
			$relmodule[] = $header[$ms];
			$relmodcachename[$header[$ms]] = $relmodcacheid[$header[$ms]] = '';
		}
	}
} else {
	$iexml = new SimpleXMLElement(file_get_contents($filename));
	$mainmodule = (string)$iexml->origin;
	$relmodule = $iexml->relatedmodules->module;
}

$minfo = __getrelmoduleinfo($mainmodule);
$mod = Vtiger_Module::getInstance($mainmodule);
$fld = Vtiger_Field::getInstance($params[$mainmodule], $mod);
$qg = new QueryGenerator($mainmodule, $current_user);
$qg->setFields(array('id',$minfo['autonumfield'],$params[$mainmodule]));
if ($ieformat=='csv') {
	$mmsql = $qg->getQuery(). ' and '.$fld->table.'.'.$fld->column.'=?';
} else {
	$mmsql = $qg->getQuery(). ' and ('.$fld->table.'.'.$fld->column.'=? or '.$fld->table.'.'.$fld->column.'=?)';
}
$mainmod = array(
	'i18n' => getTranslatedString($mainmodule, $mainmodule),
	'focus' => CRMEntity::getInstance($mainmodule),
	'modinfo' => $minfo,
	'query' => $mmsql,
);

$relmods = array();
foreach ($relmodule as $relmod) {
	$minfo = __getrelmoduleinfo((string)$relmod);
	$mod = Vtiger_Module::getInstance((string)$relmod);
	$fld = Vtiger_Field::getInstance($params[(string)$relmod], $mod);
	$qg = new QueryGenerator((string)$relmod, $current_user);
	$qg->setFields(array('id',$minfo['autonumfield'],$params[(string)$relmod]));
	if ($ieformat=='csv') {
		$rmsql = $qg->getQuery(). ' and '.$fld->table.'.'.$fld->column.'=?';
	} else {
		$rmsql = $qg->getQuery(). ' and ('.$fld->table.'.'.$fld->column.'=? or '.$fld->table.'.'.$fld->column.'=?)';
	}
	$relmods[(string)$relmod] = array(
		'i18n' => getTranslatedString((string)$relmod, (string)$relmod),
		'focus' => CRMEntity::getInstance((string)$relmod),
		'modinfo' => $minfo,
		'query' => $rmsql,
	);
}

$recordprocessed = 0;
$id = 1;
if ($ieformat=='csv') {
	$numrmods = count($relmodule);
	$file = new \SplFileObject($filename, 'r');
	$file->seek(PHP_INT_MAX);
	$recordcount = $file->key();
	$prerowmm = '';
	$prerowmmid = 0;
	while ($data = fgetcsv($handle, 5000, ',')) {
		// search for main module record
		if ($prerowmm==$data[0]) {
			$mmid = $prerowmmid;
		} else {
			$rs = $adb->pquery($mmsql, array($data[0]));
			if ($rs && $adb->num_rows($rs)>0) {
				$mmid = $adb->query_result($rs, 0, 0);
				$prerowmmid = $mmid;
				$prerow = $data[0];
			} else {
				$msg = '<span style="color:red">' . sprintf($mod_strings['NotFoundRecords'], $data[0], $params[$mainmodule]).'</span>';
				$mmid = 0;
			}
		}
		if ($mmid) {
			$msg = '';
			for ($c=1; $c <= $numrmods; $c++) {
				// search for relmodule
				if ($relmodcachename[$header[$c]]==$data[$c]) {
					$relid = $relmodcacheid[$header[$c]];
				} else {
					$rsrel = $adb->pquery($relmods[$header[$c]]['query'], array($data[$c]));
					if ($rsrel && $adb->num_rows($rsrel)>0) {
						$relid = $adb->query_result($rsrel, 0, 0);
						$relmodcacheid[$header[$c]] = $relid;
						$relmodcachename[$header[$c]] = $data[$c];
					} else {
						$relid = 0;
						$msg.= '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red">' . sprintf($mod_strings['NotFoundRecords'], $data[$c], $relmods[$header[$c]]['i18n']).'</span><br>';
					}
				}
				if ($relid) {
					$relwith = $adb->query_result($rsrel, 0, 0);
					relateEntities($mainmod['focus'], $mainmodule, $mmid, $header[$c], $relwith);
					$msg.= '&nbsp;&nbsp;&nbsp;&nbsp;' . $data[0] . ' ' . $mod_strings['Related with'] . ' '.$data[$c].'<br>';
				}
			}
		}
		$recordprocessed++;
		$progress = $recordprocessed / $recordcount * 100;
		send_message($id++, $msg, $progress, $recordprocessed, $recordcount);
	}
	fclose($handle);
} else {
	$recordcount = $iexml->relations->record->count();
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
}
send_message('CLOSE', $mod_strings['Process complete'], 100, $recordcount, $recordcount);
?>
