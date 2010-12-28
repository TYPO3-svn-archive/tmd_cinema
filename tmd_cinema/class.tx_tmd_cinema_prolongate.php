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
	 * Implement special copy behavior for the tx_tmdcinema_program
	 */
class tx_tmd_cinema_prolongate {
		var $extKey = 'tmd_cinema';

		function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$reference) {

			if($table != "tx_tmdcinema_program") return;

/*
				// if no title is given, then do so.
				// use the Movie's Title 	
			if(empty($incomingFieldArray['temp_title'])) {
				
				if(empty($incomingFieldArray['movie'])) {
					$incomingFieldArray['temp_title'] = 'No Title';
				} else {
					$lPart = strrchr($incomingFieldArray['movie'], '_');
					list(,$id) = explode("_", $lPart); 
					
					$rec = t3lib_BEfunc::getRecord('tx_tmdmovie_movie', $id, 'title', $where='');
					$incomingFieldArray['temp_title'] = $rec['title'];
				}
				
			}

				// Movie finally is selected but still no title given 
			if($incomingFieldArray['temp_title'] == 'No Title' && !empty($incomingFieldArray['movie'])) {
				$lPart = strrchr($incomingFieldArray['movie'], '_');
				list(,$id) = explode("_", $lPart); 
				
				$rec = t3lib_BEfunc::getRecord('tx_tmdmovie_movie', $id, 'title', $where='');
				$incomingFieldArray['temp_title'] = $rec['title'];
			}
			

*/			
			

			
			
			
				// prologate the program
				// only if it's a copy.
			if(isset($incomingFieldArray['t3_origuid'])) {
				$incomingFieldArray['week']++;

					# Urzeit garantiert auf 0:00:00 setzen!!
				#list($day, $month, $year) = explode(",", strftime("%e,%m,%Y", $incomingFieldArray['date']));
#				$incomingFieldArray['date'] = mktime(0,0,0, $month, $day, $year)  + 7*24*60*60;
				$now = $incomingFieldArray['date'];
				$incomingFieldArray['date'] = mktime(0, 0, 0, date("m", $now), date("d", $now)+7, date("Y", $now));
			}

			
		}
	}
		
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/class.tx_tmd_cinema_prolongate.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/class.tx_tmd_cinema_prolongate.php']);
}
?>