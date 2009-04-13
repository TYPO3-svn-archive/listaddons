<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Bernhard Kraft (kraftb@kraftb.at)
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
 * @author	Bernhard Kraft <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


require_once(PATH_typo3.'interfaces/interface.localrecordlist_actionsHook.php');

class tx_listmoduleplus_hooks implements localRecordList_actionsHook	{

	/**
	 * modifies clip-icons
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @param	array		the default clip-icons to get modified
	 * @param	object		Instance of calling object
	 * @return	array		the modified clip-icons
	 */
	public function makeClip($table, $row, $cells, &$parentObject)	{
		$this->pObj = &$parentObject;
		$ret = array();
		$paste = $this->pasteMultElements($table, $row);
		foreach ($cells as $key => $value)	{
			$ret[$key] = $value;
			if ($key=='pasteAfter')	{
				$ret['pasteAfterMult'] = $paste;
			}
		}
		if (!$ret['pasteAfterMult'])	{
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
	public function makeControl($table, $row, $cells, &$parentObject)	{
		$this->pObj = &$parentObject;
		$ret = array();
		foreach ($cells as $key => $value)	{
			$ret[$key] = $value;
			if ($key=='edit')	{
				$ret['editInPopup'] = $this->editInPopup($table, $row);
			}
		}
		return $ret;
	}


	private function editInPopup($table, $row)	{
		global $LANG, $BE_USER;
		$params = '&edit['.$table.']['.$row['uid'].']=edit';
		$link = $this->pObj->backPath.'alt_doc.php?returnUrl=close.html'.$params;
		$aOnClick = 'vHWin=window.open(\''.$link.'\',\''.md5(serialize($row)).'\',\''.($BE_USER->uc['edit_wideDocument']?'width=670,height=500':'width=600,height=400').',status=0,menubar=0,scrollbars=1,resizable=1\');vHWin.focus();return false;';
		return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'"><img'.t3lib_iconWorks::skinImg($this->pObj->backPath,'gfx/open_in_new_window.gif','width="19" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.openInNewWindow',1).'" alt="" /></a>';
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
	public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject)	{
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
	public function renderListHeaderActions($table, $currentIdList, $cells, &$parentObject)	{
		$this->pObj = &$parentObject;
		$paste = $this->pasteMultElements($table);
		$mark = $this->markAllTableElements($table);
		$ret = array();
		foreach ($cells as $key => $value)	{
			$ret[$key] = $value;
			if ($key=='markAll')	{
				$ret['markComplete'] = $mark;
			}
			if ($key=='pasteAfter')	{
				$ret['pasteAfterMult'] = $paste;
			}
		}
		if (!$ret['pasteAfterMult'])	{
			$ret = array_merge(array('pasteAfterMult' => $paste), $ret);
		}
		return $ret;
	}

	function markAllTableElements($table)	{
		global $LANG;
		if ($this->pObj->clipObj->current=='normal')	{
			return '';
		}
		$title = $LANG->sL('LLL:EXT:listmoduleplus/locallang.xml:title_select_all');
		$titleOff = $LANG->sL('LLL:EXT:listmoduleplus/locallang.xml:title_unselect_all');

		$limit = $this->pObj->iLimit;
		$this->pObj->iLimit = 0;
		$queryParts = $this->pObj->makeQueryArray($table, $this->pObj->id, '', 'uid');
		$this->pObj->iLimit = $limit;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
			$uidListArr[] = $row['uid'];
		}
		$uidList = implode(',', $uidListArr);
		$onClick = 'checkCompleteTable(\''.$table.'\', \''.$uidList.'\', 1); document.dblistForm.cmd.value=\'setCB\';document.dblistForm.cmd_table.value=\''.$table.'\';document.dblistForm.submit();return false;';
		$onOffClick = 'checkCompleteTable(\''.$table.'\', \''.$uidList.'\', 0); document.dblistForm.cmd.value=\'setCB\';document.dblistForm.cmd_table.value=\''.$table.'\';document.dblistForm.submit();return false;';
		if (!$this->pObj->checkCompleteTable)	{
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
		return $check.' <a href="#" onclick="'.$onClick.'"><img '.t3lib_iconWorks::skinImg($this->pObj->backPath,t3lib_extMgm::extRelPath('listmoduleplus').'res/clip_select_all.gif','width="12" height="12"').' title="'.$title.'" alt="" /></a> <a href="#" onclick="'.$onOffClick.'" ><img '.t3lib_iconWorks::skinImg($this->pObj->backPath,t3lib_extMgm::extRelPath('listmoduleplus').'res/clip_deselect_all.gif','width="12" height="12"').' title="'.$titleOff.'" alt="" /></a> ';
	}

	private function pasteMultElements($table, $row = false)	{
		global $LANG;
		if (!count($this->pObj->clipObj->elFromTable($table)))	{
			return;
		}
		$pmsg = $LANG->sL('LLL:EXT:listmoduleplus/locallang.xml:prompt_paste_numRecords');
		$title = $LANG->sL('LLL:EXT:listmoduleplus/locallang.xml:title_paste_numRecords');
		$id = $row['uid']?-$row['uid']:$this->pObj->id;
		$onClick = 'var num = 0;if (num = parseInt(prompt('.t3lib_div::quoteJSvalue($pmsg).'))) {if (!isNaN(num)) { this.href = this.href.replace(\''.rawurlencode('###NUM###').'\', num); return true; } else { return false; } } else {return false;}';
		return '<a href="'.htmlspecialchars($this->pObj->clipObj->pasteUrl($table, $id.'_###NUM###')).'" onclick="'.$onClick.'"><img'.t3lib_iconWorks::skinImg($this->pObj->backPath,t3lib_extMgm::extRelPath('listmoduleplus').'res/clip_pasteafter_mult.gif','width="16" height="16"').' title="'.$title.'" alt="" /></a>';
	}

	
}



?>
