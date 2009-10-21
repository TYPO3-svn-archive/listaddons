<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['enableFilters'] = intval($_EXTCONF['enableFilters']);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'][$_EXTKEY] = 'EXT:listaddons/class.tx_listaddons_hooks.php:&tx_listaddons_hooks';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$_EXTKEY] = 'EXT:listaddons/class.tx_listaddons_t3libtcemain.php:&tx_listaddons_t3libtcemain';

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['enableFilters']) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][$_EXTKEY] = 'EXT:listaddons/class.tx_listaddons_getTable.php:&tx_listaddons_getTable';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['docHeaderButtonsHook'][$_EXTKEY] = 'EXT:listaddons/class.tx_listaddons_template.php:tx_listaddons_template->addButton_filterReset';
}


?>
