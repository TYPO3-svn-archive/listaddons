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
 * Hook methods for tcemain
 *
 * @author	Bernhard Kraft <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

class tx_listmoduleplus_t3libtcemain	{


	function processCmdmap_preProcess(&$command, $table, $id, $value, &$pObj)	{
		if ($command === 'copy')	{
			$cb = t3lib_div::_GP('CB');
			if ($cb)	{
				if ($paste = $cb['paste'])	{
					$ref = explode('|', $paste, 2);
					if ((!$ref[0]) || ($ref[0]==$table))	{
						$parts = explode('_', $ref[1]);
						if (intval($parts[0])==$value)	{
							$this->pObj = &$pObj;
							$this->copyMultiple($table, $id, $value, intval($parts[1]));
							$command = '';
						}
					}
				}
			}
		}
	}

	function copyMultiple($table, $id, $target, $count)	{
		if ($count)	{
			$store_merged = $this->pObj->copyMappingArray_merged;
			$store_mapping = $this->pObj->copyMappingArray;
			if ($table === 'pages')	{
				for ($x = 0; $x < $count; $x++)	{
					$this->pObj->copyMappingArray_merged = Array();
					$this->pObj->copyMappingArray = Array();
					$this->pObj->copyPages($id, $target);
					$this->pObj->copyMappingArray_merged = $this->pObj->copyMappingArray;
					$this->pObj->remapListedDBRecords();
				}
			} else {
				for ($x = 0; $x < $count; $x++)	{
					$this->pObj->copyMappingArray_merged = Array();
					$this->pObj->copyMappingArray = Array();
					$this->pObj->copyRecord($table, $id, $target, 1);
					$this->pObj->copyMappingArray_merged = $this->pObj->copyMappingArray;
					$this->pObj->remapListedDBRecords();
				}
			}
			$this->pObj->copyMappingArray_merged = $store_merged;
			$this->pObj->copyMappingArray = $store_mapping;
		}
	}

}

?>
