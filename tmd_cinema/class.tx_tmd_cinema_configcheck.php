<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2009 Christian Tauscher (cms@media-distillery.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class 'tx_tmd_cinema_configcheck' for the 'tmd_cinema' extension.
 *
 * This class checks this extension's configuration for basic sanity.
 *
 * @package TYPO3
 * @subpackage tx_tmd_cinema
 *
 * @author Christian Tauscher (cms@media-distillery.de)
 */

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oeli b_configcheck.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oeli b_db.php');

class tx_tmd_cinema_configcheck extends tx_oelib_configcheckxx {

	protected function check_tx_tmd_cinema_pi1() {
		die("hier");
	}





}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/class.tx_tmd_cinema_configcheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/class.tx_tmd_cinema_configcheck.php']);
}
?>