<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2009 Bernhard Kraft (kraftb@think-open.at)
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
 * Hook methods for the list module / template.php
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */



class tx_listaddons_template {
	protected $EXTKEY = 'listaddons';

	function addButton_filterReset($params, &$parentObject) {
		if ($parentObject->scriptID === 'typo3/db_list.php') {
			$this->pObj = &$GLOBALS['SOBE'];
			$params['markers']['BUTTONLIST_LEFT'] .= $this->getButton_filterReset();
		}
	}

	function getButton_filterReset() {
		global $LANG;
		$LLL = 'LLL:EXT:listaddons/locallang.xml:filters_reset';
		$label = $LANG->sL($LLL);
		$params = array(
			'id' => $this->pObj->id,
			'resetFilters' => 1,
			$this->EXTKEY => '',
			'table' => '',
			'pointer' => '',
		);
		if ($this->pObj->table) {
			$params['table'] = $this->pObj->table;
		}
		$link = t3lib_div::linkThisScript($params);
		return '<a href="'.$link.'"><img src="'.t3lib_extMgm::extRelPath($this->EXTKEY).'/res/icon_reset.png" alt="'.$label.'" title="'.$label.'" /></a>';
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/listaddons/class.tx_listaddons_template.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/listaddons/class.tx_listaddons_template.php']);
}

?>
