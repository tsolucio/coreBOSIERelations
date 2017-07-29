/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

function getRelatedModules(select) {
	document.getElementById('cbiespinner').classList.remove("hide");
	fetch('index.php?module=coreBOSIERelations&action=coreBOSIERelationsAjax&file=cbieops&_op=getRelatedModules&mod=' + select.value, {
		credentials: 'same-origin'
	}).then(function(response) {
		return response.text();
	}).then(function(data) {
		document.getElementById('relatedmodules').innerHTML = data;
		setExportButtonState();
		document.getElementById('cbiespinner').classList.add("hide");
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

