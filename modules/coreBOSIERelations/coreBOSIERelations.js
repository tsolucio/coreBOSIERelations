/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
jQuery.getScript('index.php?module=coreBOSIERelations&action=coreBOSIERelationsAjax&file=getjslanguage');
var cbierels_es;

function getRelatedModules(select) {
	document.getElementById('cbiespinner').classList.remove('hide');
	fetch('index.php?module=coreBOSIERelations&action=coreBOSIERelationsAjax&file=cbieops&_op=getRelatedModules&mod=' + select.value, {
		credentials: 'same-origin'
	}).then(function (response) {
		return response.text();
	}).then(function (data) {
		document.getElementById('relatedmodules').innerHTML = data;
		setExportButtonState();
		document.getElementById('cbiespinner').classList.add('hide');
	});
}

function setExportButtonState() {
	let chks = document.querySelectorAll('*[id^="relmodcheckbox"]');
	let i=0;
	let checked = false;
	let relmods = '';
	while (i<chks.length) {
		checked = checked || chks[i].checked;
		if (chks[i].checked) {
			relmods += chks[i].name + ',';
		}
		i++;
	}
	let module = document.getElementById('selectexportmodule').value;
	let btn = document.getElementById('cbieexportbutton');
	btn.href = 'index.php?module=coreBOSIERelations&action=coreBOSIERelationsAjax&file=cbieops&_op=getXMLExport&mod=' + module + '&relmods=' + relmods;
	btn.download = module + '_relationsexport.xml';
	btn.style.visibility = (checked ? 'visible' : 'hidden');
	return true;
}

function launchImportProcess() {
	let sels = document.querySelectorAll('*[id^="cbieimportmoduleselect"]');
	let i=0;
	let params = {};
	while (i<sels.length) {
		if (sels[i].value=='__none__') {
			alert(mod_alert_arr.ERR_SelectIDField);
			return false;
		}
		params[sels[i].dataset.module] = sels[i].value;
		i++;
	}
	params['ieformat'] = document.getElementById('ieformat').value;
	let rdo = document.getElementById('relresultssection');
	rdo.style.visibility = 'visible';
	cbierels_es = new EventSource('index.php?module=coreBOSIERelations&action=coreBOSIERelationsAjax&file=cbierelate&params='+encodeURIComponent(JSON.stringify(params)));

	//a message is received
	cbierels_es.addEventListener('message', function (e) {
		var result = JSON.parse(e.data);

		__addLog(result.message);

		var pBar = document.getElementById('progressor');
		if (e.lastEventId == 'CLOSE') {
			__addLog('<br><b>' + mod_alert_arr.ProcessFINISHED + '!</b>');
			cbierels_es.close();
			pBar.value = pBar.max; //max out the progress bar
		} else {
			pBar.value = result.progress;
			var perc = document.getElementById('percentage');
			perc.innerHTML   = result.progress  + '% &nbsp;&nbsp;' + result.processed + '/' + result.total;
			perc.style.width = (Math.floor(pBar.clientWidth * (result.progress/100)) + 15) + 'px';
		}
	});

	cbierels_es.addEventListener('error', function (e) {
		__addLog(mod_alert_arr.ERR_Importing);
		cbierels_es.close();
	});
}

function stopTask() {
	cbierels_es.close();
	__addLog(mod_alert_arr.Interrupted);
}

function __addLog(message) {
	var r = document.getElementById('relresults');
	r.innerHTML += message + '<br>';
	r.scrollTop = r.scrollHeight;
}
