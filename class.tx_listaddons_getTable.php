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
 * Hook methods for the list module
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */



require_once(PATH_t3lib.'interfaces/interface.t3lib_localrecordlistgettablehook.php');

class tx_listaddons_getTable implements t3lib_localRecordListGetTableHook {
	protected $EXTKEY = 'listaddons';

	/**
	 * modifies the DB list query
	 *
	 * @param	string		the current database table
	 * @param	integer		the record's page ID
	 * @param	string		an additional WHERE clause
	 * @param	string		comma separated list of selected fields
	 * @param	localRecordList		parent localRecordList object
	 * @return	void
	 */
	public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject) {
		$filters = $parentObject->modTSconfig['properties']['listaddons.']['filters.'][$table.'.'];
		if (is_array($filters)) {
			$this->getFilterSettings($table, $filters);
			if (is_array($this->filterSettings) && is_array($this->filterSettings[$table])) {
				$where = $this->getFilterConditions($table, $this->filterSettings[$table]);
				if ($where) {
					$additionalWhereClause .= ' AND '.$where;
				}
			}
		}
	}



	/**
	 * returns a "where" string containing all required conditions to match current filter criteria
	 *
	 * @param	string		the current database table
	 * @param	array		an array containing the fields/values which to filter
	 * @return	string		a "where" string which can get appended to the SQL queries "where"
	 */
	protected function getFilterConditions($table, $currentFilters) {
		$result = '';
		if (is_array($currentFilters) && count($currentFilters)) {
			$conditions = array();
			foreach ($currentFilters as $field => $value) {
				$condition = $this->getSingleFilterCondition($table, $field, $value);
				if ($condition) {
					$conditions[] = $condition;
				}
			}
			$result = implode(' AND ', $conditions);
			if ($result) {
				$result = '('.$result.')';
			}
		}
		return $result;
	}

	/**
	 * returns a "where" string containing one conditions for the appropriate filter field/value
	 *
	 * @param	string		the current database table
	 * @param	string		the field for which to generate the "where"
	 * @param	string		the value to match as md5 hash
	 * @return	string		a "where" string for this field
	 */
	protected function getSingleFilterCondition($table, $field, $value) {
		if ($value==='__ALL__') return '';
		return 'md5('.$table.'.'.$field.')='.$GLOBALS['TYPO3_DB']->fullQuoteStr($value, $table);
	}

	/**
	 * This method retrieves current filter settings
	 *
	 * @param	string		the database table for which to retrieve the list filters
	 * @param	string		the field for which to retrieve list filters
	 * @return	string		the html code for the filter for a field
	 */
	protected function getFilterSettings($table, $filters) {
		$postVars = t3lib_div::_GP($this->EXTKEY);
		$reset = t3lib_div::_GP('resetFilters');
		$current = array();
		if (is_array($postVars) && is_array($postVars['filters'])) {
			$current = $postVars['filters'][$table];
		}
		$this->filterSettings = $GLOBALS['BE_USER']->getModuleData($this->EXTKEY, 'ses');
		if ($reset) {
			$this->filterSettings = array();
		}
		if (!is_array($this->filterSettings)) {
			$this->filterSettings = array();
		}
		if (!is_array($this->filterSettings[$table])) {
			$this->filterSettings[$table] = array();
		}
		if (is_array($current)) {
			foreach ($current as $key => $value) {
				if ($filters[$key]) {
					$this->filterSettings[$table][$key] = $value;
				}
			}
		}
		$GLOBALS['BE_USER']->pushModuleData($this->EXTKEY, $this->filterSettings);
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/listaddons/class.tx_listaddons_getTable.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/listaddons/class.tx_listaddons_getTable.php']);
}

?>
