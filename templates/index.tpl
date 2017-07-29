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
<script type="text/javascript" src="modules/coreBOSIERelations/coreBOSIERelations.js"></script>
<br>
<div class="slds-page-header slds-is-relative" role="banner">
	<div class="slds-grid">
		<div class="slds-col slds-has-flexi-truncate">
			<div class="slds-media slds-no-space slds-grow">
				<div class="slds-media__figure">
					<svg aria-hidden="true" class="slds-icon slds-icon-standard-user">
						<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#share"></use>
					</svg>
				</div>
				<div class="slds-media__body">
					<h1 class="slds-page-header__title slds-m-right--small slds-align-middle slds-truncate"
						title="{$MOD.coreBOSIERelations}">{$MOD.coreBOSIERelations}</h1>
				</div>
			</div>
		</div>
	</div>
	<div role="status" class="slds-spinner_container slds-spinner slds-spinner_medium hide" id="cbiespinner">
		<span class="slds-assistive-text">{'LBL_LOADING'|getTranslatedString}</span>
		<div class="slds-spinner__dot-a"></div>
		<div class="slds-spinner__dot-b"></div>
	</div>
</div>
<div class="slds-grid">
<div class="slds-box slds-col">
	<div class="slds-page-header" role="banner">
		<div class="slds-col slds-has-flexi-truncate">
			<div class="slds-media slds-no-space slds-grow">
				<div class="slds-media__figure">
					<svg aria-hidden="true" class="slds-icon slds-icon-standard-user">
						<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#download"></use>
					</svg>
				</div>
				<div class="slds-media__body">
					<h1 class="slds-page-header__title slds-m-right--small slds-align-middle slds-truncate"
						title="{$MOD.Export}">{$MOD.Export}</h1>
				</div>
			</div>
		</div>
	</div>
<div class="slds-form-element">
	<label class="slds-form-element__label" for="select-01">{$MOD.ModuleToExport}</label>
	<div class="slds-form-element__control">
		<div class="slds-select_container">
			<select class="slds-select" id="selectexportmodule" onchange="getRelatedModules(this);">
				<option value="__none__">{$APP.LBL_NONE}</option>
				{foreach item=arr from=$MODULELIST}
					<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
				{foreachelse}
					<option value="" style='color: #777777' disabled>{$APP.LBL_NONE}</option>
				{/foreach}
			</select>
		</div>
	</div>
</div>
<div id="relatedmodules">
</div>
<div class="slds-align--absolute-center slds-p-top--large">
<a class="slds-button slds-button_icon slds-button--icon-more" title="{$MOD.Export}" id="cbieexportbutton" style="visibility:hidden;"
	href="index.php?module=coreBOSIERelations&action=coreBOSIERelationsAjax&file=cbieops&_op=getXMLExport&mod=&rels=" download="_relationsexport.xml">
	<svg class="slds-button__icon cbslds-button--icon-more" aria-hidden="true">
		<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#download"></use>
	</svg>
	<span class="slds-assistive-text">{$MOD.Export}</span></a>
</div>
</div>
<div class="slds-box slds-col">
	<div class="slds-page-header" role="banner">
		<div class="slds-col slds-has-flexi-truncate">
			<div class="slds-media slds-no-space slds-grow">
				<div class="slds-media__figure">
					<svg aria-hidden="true" class="slds-icon slds-icon-standard-user">
						<use xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#upload"></use>
					</svg>
				</div>
				<div class="slds-media__body">
					<h1 class="slds-page-header__title slds-m-right--small slds-align-middle slds-truncate"
						title="{$MOD.Import}">{$MOD.Import}</h1>
				</div>
			</div>
		</div>
	</div>
	<div>
	<div class="slds-form-element">
		<span class="slds-form-element__label"><br></span>
		<div class="slds-form-element__control">
			<div class="slds-file-selector slds-file-selector_files">
				<div class="slds-file-selector__dropzone">
				<input type="file" class="slds-file-selector__input slds-assistive-text" accept="text/xml" id="xmlupload" name="xmlupload" aria-describedby="file-selector-id">
				<label class="slds-file-selector__body" for="xmlupload">
					<span class="slds-file-selector__button slds-button slds-button_neutral">
						<svg class="slds-button__icon slds-button__icon_left" aria-hidden="true">
						<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="include/LD/assets/icons/utility-sprite/svg/symbols.svg#upload"></use>
						</svg>&nbsp;{$MOD.ImportFile}</span>
					<span class="slds-file-selector__text slds-medium-show"></span>
				</label>
			</div>
		</div>
		</div>
	</div>
	</div>
</div>
</div>
