<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Christian Tauscher <christian.tauscher@media-distlillery.de>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */



	/**
	 * Class for generating infomai loof the current record.
	 *
	 */
class tx_tmd_cinema_copyedit {
		
		
		var $extKey = 'tmd_cinema';


		function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$reference) {
			if(isset($incomingFieldArray['t3_origuid']) && $table=="tx_tmdcinema_program") {
				$incomingFieldArray['week']++;
				$incomingFieldArray['date'] = $incomingFieldArray['date'] + 7*24*60*60;
			}
		}

		
		

	}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/class.tx_tmd_cinema_copyedit.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/class.tx_tmd_cinema_copyedit.php']);
}
?>