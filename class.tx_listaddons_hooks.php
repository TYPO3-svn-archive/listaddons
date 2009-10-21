<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007-2009 Bernhard Kraft (kraftb@think-open.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Hook methods for the list module
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


require_once(PATH_typo3.'interfaces/interface.localrecordlist_actionsHook.php');

class tx_listaddons_hooks implements localRecordList_actionsHook {
	protected $EXTKEY = 'listaddons';



	/**
	 * modifies clip-icons
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @param	array		the default clip-icons to get modified
	 * @param	object		Instance of calling object
	 * @return	array		the modified clip-icons
	 */
	public function makeClip($table, $row, $cells, &$parentObject) {
		$this->pObj = &$parentObject;
		$ret = array();
		$paste = $this->pasteMultElements($table, $row);
		if (is_array($this->pObj->modTSconfig) && is_array($this->pObj->modTSconfig['properties']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']['disable.'])) {
			$dis_pasteAfterMult = intval($this->pObj->modTSconfig['properties']['listaddons.']['disable.']['pasteAfterMult']);
		}
		foreach ($cells as $key => $value) {
			$ret[$key] = $value;
			if (($key=='pasteAfter') && !$dis_pasteAfterMult) {
				$ret['pasteAfterMult'] = $paste;
			}
		}
		if (!$ret['pasteAfterMult'] && !$dis_pasteAfterMult) {
			$ret = array_merge(array('pasteAfterMult' => $paste), $ret);
		}
		return $ret;
	}



	/**
	 * modifies control-icons
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @param	array		the default control-icons to get modified
	 * @param	object		Instance of calling object
	 * @return	array		the modified control-icons
	 */
	public function makeControl($table, $row, $cells, &$parentObject) {
		$this->pObj = &$parentObject;
		$ret = array();
		if (is_array($this->pObj->modTSconfig) && is_array($this->pObj->modTSconfig['properties']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']['disable.'])) {
			$dis_editInPopup= intval($this->pObj->modTSconfig['properties']['listaddons.']['disable.']['editInPopup']);
		}
		foreach ($cells as $key => $value) {
			$ret[$key] = $value;
			if (($key=='edit') && !$dis_editInPopup) {
				$ret['editInPopup'] = $this->editInPopup($table, $row);
			}
		}
		return $ret;
	}



	/**
	 * modifies Web>List header row columns/cells
	 *
	 * @param	string		the current database table
	 * @param	array		Array of the currently displayed uids of the table
	 * @param	array		An array of rendered cells/columns
	 * @param	object		Instance of calling (parent) object
	 * @return	array		Array of modified cells/columns
	 */
	public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject) {
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->EXTKEY]['enableFilters']) {
			$this->pObj = &$parentObject;
			$parentObject->HTMLcode .= $this->getFilters($table);
		}
		return $headerColumns;
	}



	/**
	 * modifies Web>List header row clipboard icons
	 *
	 * @param	string		the current database table
	 * @param	array		Array of the currently displayed uids of the table
	 * @param	array		An array of the current clipboard icons
	 * @param	object		Instance of calling (parent) object
	 * @return	array		Array of modified clipboard icons
	 */
	public function renderListHeaderActions($table, $currentIdList, $cells, &$parentObject) {
		$this->pObj = &$parentObject;
		if (is_array($this->pObj->modTSconfig) && is_array($this->pObj->modTSconfig['properties']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']['disable.'])) {
			$dis_markAll = intval($this->pObj->modTSconfig['properties']['listaddons.']['disable.']['markAll']);
			$dis_pasteAfterMult = intval($this->pObj->modTSconfig['properties']['listaddons.']['disable.']['pasteAfterMult']);
		}
		$paste = $this->pasteMultElements($table);
		$mark = $this->markAllTableElements($table);
		$ret = array();
		foreach ($cells as $key => $value) {
			$ret[$key] = $value;
			if (($key=='markAll') && !$dis_markAll) {
				$ret['markComplete'] = $mark;
			}
			if (($key=='pasteAfter') && !$dis_pasteAfterMult) {
				$ret['pasteAfterMult'] = $paste;
			}
		}
		if ((!$ret['pasteAfterMult']) && !$dis_pasteAfterMult) {
			$ret = array_merge(array('pasteAfterMult' => $paste), $ret);
		}
		return $ret;
	}



	/**
	 * Generates a link which allows to mark all elements in a table, not only the shown
	 *
	 * @param	string		the table for which to generate a mark-all link
	 * @return	string		Link to mark all elements of passed table
	 */
	protected function markAllTableElements($table) {
		global $LANG;
		if ($this->pObj->clipObj->current=='normal') {
			return '';
		}
		$title = $LANG->sL('LLL:EXT:listaddons/locallang.xml:title_select_all');
		$titleOff = $LANG->sL('LLL:EXT:listaddons/locallang.xml:title_unselect_all');

		$limit = $this->pObj->iLimit;
		$this->pObj->iLimit = 0;
		$queryParts = $this->pObj->makeQueryArray($table, $this->pObj->id, '', 'uid');
		$this->pObj->iLimit = $limit;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$uidListArr[] = $row['uid'];
		}
		$uidList = implode(',', $uidListArr);
		$onClick = 'checkCompleteTable(\''.$table.'\', \''.$uidList.'\', 1); document.dblistForm.cmd.value=\'setCB\';document.dblistForm.cmd_table.value=\''.$table.'\';document.dblistForm.submit();return false;';
		$onOffClick = 'checkCompleteTable(\''.$table.'\', \''.$uidList.'\', 0); document.dblistForm.cmd.value=\'setCB\';document.dblistForm.cmd_table.value=\''.$table.'\';document.dblistForm.submit();return false;';
		if (!$this->pObj->checkCompleteTable) {
			$this->pObj->checkCompleteTable = 1;
			$check = '
<script type="text/javascript">

function checkCompleteTable(table, uidList, flag)	{
	var uidArr = uidList.split(\',\');
	var frm = document.forms[\'dblistForm\'];
	var cbs = \'\';
	var cnt = 0;
	if (table && uidArr.length)	{
		for (var key in uidArr)
		cbs += \'<input type="hidden" name="CBC[\'+table+\'|\'+uidArr[key]+\']" value="\'+flag+\'" />\';
		cnt++;
		if (!(cnt%50))	{
			cnt = 0;
			frm.innerHTML += cbs;
			cbs = \'\';	
		}
	}
	frm.innerHTML += cbs;
}

</script>
';
		}
		return $check.' <a href="#" onclick="'.$onClick.'"><img '.t3lib_iconWorks::skinImg($this->pObj->backPath,t3lib_extMgm::extRelPath('listaddons').'res/clip_select_all.gif','width="12" height="12"').' title="'.$title.'" alt="" /></a> <a href="#" onclick="'.$onOffClick.'" ><img '.t3lib_iconWorks::skinImg($this->pObj->backPath,t3lib_extMgm::extRelPath('listaddons').'res/clip_deselect_all.gif','width="12" height="12"').' title="'.$titleOff.'" alt="" /></a> ';
	}



	/**
	 * Returns the icons for pasting multiple elements
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @return	string		the paste multiple link
	 */
	protected function pasteMultElements($table, $row = false) {
		global $LANG;
		if (!count($this->pObj->clipObj->elFromTable($table))) {
			return;
		}
		$pmsg = $LANG->sL('LLL:EXT:listaddons/locallang.xml:prompt_paste_numRecords');
		$title = $LANG->sL('LLL:EXT:listaddons/locallang.xml:title_paste_numRecords');
		$id = $row['uid']?-$row['uid']:$this->pObj->id;
		$onClick = 'var num = 0;if (num = parseInt(prompt('.t3lib_div::quoteJSvalue($pmsg).'))) {if (!isNaN(num)) { this.href = this.href.replace(\''.rawurlencode('###NUM###').'\', num); return true; } else { return false; } } else {return false;}';
		return '<a href="'.htmlspecialchars($this->pObj->clipObj->pasteUrl($table, $id.'_###NUM###')).'" onclick="'.$onClick.'"><img'.t3lib_iconWorks::skinImg($this->pObj->backPath,t3lib_extMgm::extRelPath('listaddons').'res/clip_pasteafter_mult.gif','width="16" height="16"').' title="'.$title.'" alt="" /></a>';
	}



	/**
	 * Returns a pop-up link for editing a single record
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @return	string		the pop-up edit link
	 */
	protected function editInPopup($table, $row) {
		global $LANG, $BE_USER;
		$params = '&edit['.$table.']['.$row['uid'].']=edit';
		$link = $this->pObj->backPath.'alt_doc.php?returnUrl=close.html'.$params;
		$aOnClick = 'vHWin=window.open(\''.$link.'\',\''.md5(serialize($row)).'\',\''.($BE_USER->uc['edit_wideDocument']?'width=670,height=500':'width=600,height=400').',status=0,menubar=0,scrollbars=1,resizable=1\');vHWin.focus();return false;';
		return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'"><img'.t3lib_iconWorks::skinImg($this->pObj->backPath,'gfx/open_in_new_window.gif','width="19" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.openInNewWindow',1).'" alt="" /></a>';
	}



	/**
	 * This method returns all filters defined for the current table
	 *
	 * @param	string		the database table for which to retrieve the list filters
	 * @return	string		the filters html code
	 */
	protected function getFilters($table) {
		$result = '';
		if (is_array($this->pObj->modTSconfig) && is_array($this->pObj->modTSconfig['properties']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']['filters.']) && is_array($this->pObj->modTSconfig['properties']['listaddons.']['filters.'][$table.'.'])) {
				// Define parameters for link generation
			$this->linkParams = array('id' => $this->pObj->id, $this->EXTKEY => '', 'resetFilters' => '', 'table' => '');
			if ($this->pObj->table) {
				$this->linkParams['table'] = $this->pOb->table;
			}
			$filters = $this->pObj->modTSconfig['properties']['listaddons.']['filters.'][$table.'.'];
			$this->getFilterSettings($table, $filters);
			$result .= '<table style="margin-top: 5px; border: 1px solid #333333; border-collapse: collapse;">';
			foreach ($filters as $field => $active) {
				if (!$active) {
					continue;
				}
				$result .= '<tr>';
				$filter = $this->getFilter($table, $field);
				$result .= '<td style="border: 1px solid #333333; text-align: right; padding: 3px 10px 3px 10px;">'.$filter[0].'</td>';
				$result .= '<td style="border: 1px solid #333333; padding: 3px 5px 3px 5px;">'.$filter[1].'</td>';
				$result .= '</tr>';
			}
			$params = $this->linkParams;
			$params['resetFilters'] = 1;
			$result .= '</table>';
		}
		return $result;
	}



	/**
	 * This method returns the filter for the specified field of the current table
	 *
	 * @param	string		the database table for which to retrieve the list filters
	 * @param	string		the field for which to retrieve list filters
	 * @return	string		the html code for the filter for a field
	 */
	protected function getFilter($table, $field) {
		t3lib_div::loadTCA($table);
			// get the label for the filter field
		$label = $GLOBALS['TCA'][$table]['columns'][$field]['label'];
		$labelStr = $GLOBALS['LANG']->sL($label);
			// get enable fields and select all distinct value rows
		$enableFields = t3lib_BEfunc::BEenableFields($table);
		$values = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT('.$field.') AS value', $table, 'pid='.intval($this->pObj->id).' '.$enableFields, '', 'value');
			// generate an array containing all key=>value pairs
		$labels = array('__ALL__' => $GLOBALS['LANG']->sL('LLL:EXT:listaddons/locallang.xml:filters_showAll'));
		foreach ($values as $value) {
			$proc = t3lib_BEfunc::getProcessedValueExtra($table, $field, $value['value']);
			$md5 = md5($value['value']);
			$labels[$md5] = $proc;
		}
			// Check if currently an item of the filter is choosen
		$current = '__ALL__';
		if (is_array($this->filterSettings) && is_array($this->filterSettings[$table])) {
			$current = $this->filterSettings[$table][$field];
		}
			// Get filter select box
		$filter = t3lib_BEfunc::getFuncMenu($this->linkParams, $this->EXTKEY.'[filters]['.$table.']['.$field.']', $current, $labels, '');

		return array($labelStr, $filter);
	}



	/**
	 * This method retrieves current filter settings
	 *
	 * @param	string		the database table for which to retrieve the list filters
	 * @param	string		the field for which to retrieve list filters
	 * @return	string		the html code for the filter for a field
	 */
	protected function getFilterSettings($table, $filters) {
		$this->filterSettings = $GLOBALS['BE_USER']->getModuleData($this->EXTKEY, 'ses');
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/listaddons/class.tx_listaddons_hooks.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/listaddons/class.tx_listaddons_hooks.php']);
}

?>
