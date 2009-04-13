<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'][] = 'EXT:listmoduleplus/class.tx_listmoduleplus_hooks.php:&tx_listmoduleplus_hooks';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:listmoduleplus/class.tx_listmoduleplus_t3libtcemain.php:&tx_listmoduleplus_t3libtcemain';


?>
