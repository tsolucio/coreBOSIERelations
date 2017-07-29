<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

function __getcbIERelatedLists($module) {
	global $log, $adb, $current_user;
	$log->debug('Entering __getcbIERelatedLists(' . $module . ') method ...');

	$cur_tab_id = getTabid($module);

	$sql = 'select *
		from vtiger_relatedlists
		where tabid=? and related_tabid != 0 and name in (?,?)
			and related_tabid not in (SELECT tabid FROM vtiger_tab WHERE presence = 1 and isentitytype=0)
			order by sequence';
	$result = $adb->pquery($sql, array($cur_tab_id,'get_related_list','get_attachments'));
	$num_row = $adb->num_rows($result);
	$focus_list = array();
	for ($i = 0; $i < $num_row; $i++) {
		$rel_tab_id = $adb->query_result($result, $i, 'related_tabid');
		$function_name = $adb->query_result($result, $i, 'name');
		$label = $adb->query_result($result, $i, 'label');
		$actions = $adb->query_result($result, $i, 'actions');
		$relationId = $adb->query_result($result, $i, 'relation_id');
		$focus_list[$label] = array('related_tabid' => $rel_tab_id, 'relationId' => $relationId, 'actions' => $actions);
	}
	$log->debug("Exiting __getcbIERelatedLists method ...");
	return $focus_list;
}

function __createExportFile($module,$relations) {
	global $adb;
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(true);
	$xml->startDocument('1.0','UTF-8');
	$xml->startElement("ierelations");
	$xml->writeElement('origin', $module);
	$xml->startElement("relatedmodules");
	$relinfo = array();
	foreach ($relations as $relmod) {
		$xml->writeElement('module', $relmod);
		$relinfo[$relmod] = __getrelmoduleinfo($relmod);
	}
	$xml->endElement();
	$xml->startElement("relations");
	$modinfo = __getrelmoduleinfo($module);
	$q = __getMainModuleQuery($modinfo);
	$rsm = $adb->query($q);
	while ($record = $adb->fetch_array($rsm)) {
		$xml->startElement("record");
		$xml->startElement("entityid");
		$xml->writeElement('crmid', $record[$modinfo['idfield']]);
		$xml->writeElement('autonumid', $record[$modinfo['autonumfield']]);
		$xml->endElement();
		$xml->startElement("modules");
		foreach ($relations as $relmod) {
			$q = __getRelatedModuleQuery($relinfo[$relmod],$module);
			$xml->startElement("module");
			$xml->writeElement('modulename', $relmod);
			global $log;$log->fatal($q);$log->fatal(array($record,$modinfo));
			if ($relinfo[$relmod]['idtable'] == 'vtiger_notes') {
				$rsrel = $adb->pquery($q,array($record[$modinfo['idfield']]));
			} else {
				$rsrel = $adb->pquery($q,array($record[$modinfo['idfield']],$record[$modinfo['idfield']]));
			}
			$relids = $relan = array();
			while ($relrec = $adb->fetch_array($rsrel)) {
				$relids[] = $relrec[$relinfo[$relmod]['idfield']];
				$relan[] = $relrec[$relinfo[$relmod]['autonumfield']];
			}
			$xml->writeElement('relentityids', implode(',', $relids));
			$xml->writeElement('relentityans', implode(',', $relan));
			$xml->endElement();
		}
		$xml->endElement();
		$xml->endElement();
	}
	$xml->endElement();
	$xml->endElement();
	header("Pragma: public");
	header("Expires: 0"); // set expiration time
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header('Content-type: text/xml');
	header("Content-Type: application/force-download");
	header('Content-Disposition: attachment; filename="'.$module.'_relationsexport.xml"');
	echo $xml->outputMemory(true);
}

function __getrelmoduleinfo($relmod) {
	global $adb;
	$relinfo = array();
	$focus = CRMEntity::getInstance($relmod);
	$relinfo['idfield'] = $focus->table_index;
	$relinfo['idtable'] = $focus->table_name;
	$rsfld = $adb->pquery('select columnname,tablename from vtiger_field where tabid=? and uitype=?',array(getTabid($relmod),4));
	if ($rsfld and $adb->num_rows($rsfld)>0) {
		$relinfo['autonumfield'] = $adb->query_result($rsfld, 0, 'columnname');
		$relinfo['autonumtable'] = $adb->query_result($rsfld, 0, 'tablename');
	} else {
		$relinfo['autonumfield'] = '';
		$relinfo['autonumtable'] = '';
	}
	return $relinfo;
}

function __getMainModuleQuery($modinfo) {
	$q = 'select '.$modinfo['idtable'].'.'.$modinfo['idfield'].','.$modinfo['autonumtable'].'.'.$modinfo['autonumfield'];
	$q.= ' from '.$modinfo['idtable'];
	$q.= ' inner join vtiger_crmentity on vtiger_crmentity.crmid='.$modinfo['idtable'].'.'.$modinfo['idfield'];
	if ($modinfo['idtable'] != $modinfo['autonumtable']) {
		$q.= ' inner join '.$modinfo['autonumtable'].' on '.$modinfo['autonumtable'].'.'.$modinfo['idfield'].'='.$modinfo['idtable'].'.'.$modinfo['idfield'];
	}
	$q.=' where deleted = 0';
	return $q;
}

function __getRelatedModuleQuery($modinfo,$mainmodule) {
	$q = 'select '.$modinfo['idtable'].'.'.$modinfo['idfield'].','.$modinfo['autonumtable'].'.'.$modinfo['autonumfield'];
	$q.= ' from '.$modinfo['idtable'];
	$q.= ' inner join vtiger_crmentity on vtiger_crmentity.crmid='.$modinfo['idtable'].'.'.$modinfo['idfield'];
	if ($modinfo['idtable'] != $modinfo['autonumtable']) {
		$q.= ' inner join '.$modinfo['autonumtable'].' on '.$modinfo['autonumtable'].'.'.$modinfo['idfield'].'='.$modinfo['idtable'].'.'.$modinfo['idfield'];
	}
	if ($modinfo['idtable'] == 'vtiger_notes') {
		$q.= " inner join vtiger_senotesrel on (vtiger_crmentity.crmid=vtiger_senotesrel.notesid AND vtiger_senotesrel.crmid=?)";
	} else {
		$q.= " inner join vtiger_crmentityrel on (vtiger_crmentity.crmid=vtiger_crmentityrel.crmid AND vtiger_crmentityrel.relmodule='".$mainmodule."' AND vtiger_crmentityrel.relcrmid=?) OR
			(vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid AND vtiger_crmentityrel.module='".$mainmodule."' AND vtiger_crmentityrel.crmid=?)";
	}
	$q.=' where deleted = 0';
	return $q;
}

?>