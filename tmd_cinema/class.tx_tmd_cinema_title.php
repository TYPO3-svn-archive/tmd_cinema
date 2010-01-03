<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Christian Tauscher <christian.tauscher@media-distlillery.de>
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
class tx_tmd_cinema_title {
	
	function getRecordTitle($params, $pObj) { 
		t3lib_div::devlog('msg', 'tmd_cinema', 0, $params);
		
			// get the ID
		$movie = t3lib_BEfunc::getRecord('tx_tmdcinema_program',$params['row']['uid'],'movie,week');
		$week = $movie['week'];
		 
			// get the Name
		$movie = t3lib_BEfunc::getRecord('tx_tmdmovie_movie',$movie['movie'],'title,short');
		
		if(empty($movie['short']))
			$params['title']  = trim($movie['title']).' ('.$week.')';
		else
			$params['title']  = trim($movie['short']).' ('.$week.')';

#	t3lib_div::devlog('msg', 'tmd_cinema', 0, $params); 
    }
    
    
}

?>