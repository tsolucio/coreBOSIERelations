{*<!--
/*********************************************************************************
 * Copyright 2017 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO coreBOS Customizations.
 * Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
 * file except in compliance with the License. You can redistribute it and/or modify it
 * under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
 * granted by the License. coreBOS distributed by JPL TSolucio S.L. is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
 * applicable law or agreed to in writing, software distributed under the License is
 * distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing
 * permissions and limitations under the License. You may obtain a copy of the License
 * at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
 ********************************************************************************/
-->*}
<br>
{foreach item=arr key=relmod from=$RELMODULELIST name=relmods}
<div class="slds-form-element">
	<div class="slds-form-element__control">
		<span class="slds-checkbox">
			<input type="checkbox" name="{$relmod}" id="relmodcheckbox-{$smarty.foreach.relmods.iteration}" onclick="setExportButtonState()">
			<label class="slds-checkbox__label" for="relmodcheckbox-{$smarty.foreach.relmods.iteration}">
				<span class="slds-checkbox--faux"></span>
				<span class="slds-form-element__label">{$relmod|getTranslatedString:$relmod}</span>
			</label>
		</span>
	</div>
</div>
{foreachelse}
	{'LBL_NONE'|getTranslatedString}
{/foreach}
