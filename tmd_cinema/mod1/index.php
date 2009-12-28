<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2009 Christian Tauscher <cms@media-distillery.de>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:tmd_cinema/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');


$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Cinema Program' for the 'tmd_cinema' extension.
 *
 * @author	Christian Tauscher <cms@media-distillery.de>
 * @package	TYPO3
 * @subpackage	tmd_cinema
 */
class  tmd_cinema_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $MCONF=array();
	var $MOD_MENU=array();
	var $MOD_SETTINGS=array();

	
	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();
	}

		
	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			$this->cinemaOrder = t3lib_BEfunc::getModTSconfig($this->id, 'mod.'.$GLOBALS['MCONF']['name'].'.cinemaOrder');
			$this->cinemaOrder = $this->cinemaOrder['value'];
#t3lib_div::devLog('Nachricht', 'tmd_cinema', 0, (array)$this->cinemaOrder);

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
					$this->doc->JScode = '
						<script language="javascript" type="text/javascript">
							script_ended = 0;
							function jumpToUrl(URL)	{
								document.location = URL;
							}
				
						</script>
					';
					
					
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}

	}

	

	
	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	


	
	
	
	
	
	
	
	
	
	
	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'), # PROGRAMM
				'2' => $LANG->getLL('function2'), # PROGRAMM
				'3' => $LANG->getLL('function3'), # PROGRAMM
				'4' => $LANG->getLL('function4'), # PROGRAMM
		
				'5' => $LANG->getLL('function5'), # Bundesstart
				'6' => $LANG->getLL('function6'), # Neueste Filme
				'7' => $LANG->getLL('function7'), # Filme dieser Site
				),
			'cinema' => Array (
				'0' => $LANG->getLL('all'), # PROGRAMM
				),
				
			);
			
		$cinemaOrder = t3lib_BEfunc::getModTSconfig($this->id, 'mod.'.$GLOBALS['MCONF']['name'].'.cinemaOrder');


		
		if($cinemaOrder['value'] != '') {
			$fields = '*';
			$table = 'tt_address';
			$where = 'uid IN('.$cinemaOrder['value'].')';
			$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$cinema[$row['uid']] = $row['name'];
			}
	
			$cineArr = explode(',', $cinemaOrder['value']);
			
			foreach($cineArr as $val) {
				$this->MOD_MENU['cinema'][$val] = $cinema[$val];
			}
		} else { # Fehler tsconfig
			$this->MOD_MENU['cinema']['error'] = $LANG->getLL('error_cinemaOrder');
		}		

		parent::menuConfig();
		}

		
		
	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		
		switch((string)$this->MOD_SETTINGS['function'])	{
			case '1': # -1 0 1
				$start = $this->weekStartDay(mktime())-7*24*60*60-1;
				$stop  = $this->weekStartDay(mktime())+8*24*60*60+100;
				$where = "date > ".$start." AND date <= ".$stop;
				$content= $this->listProgram($where);
				$this->content.=$this->doc->section('Programm -1 0 1:',$content,0,1);
			break;
			case '2': # Programm Zukunft
				$start = $this->weekStartDay(mktime())-0*24*60*60;
				$stop  = $this->weekStartDay(mktime())+7*24*60*60;
				$where = "date >= ".$start;
				$content= $this->listProgram($where);
				$this->content.=$this->doc->section('Programm Zukunft:',$content,0,1);
			break;
			case '3': # Program Ohne Termin
				$where = "date = 0";
				$content= $this->listProgram($where);
				$this->content.=$this->doc->section('Programm ohne Termin:',$content,0,1);
			break;
			case '4': # Programm Zukunft
				$stop  = $this->weekStartDay(mktime());
				$where = "date < ".$stop;
				$content= $this->listProgram($where);
				$this->content.=$this->doc->section('Programm Vergangen:',$content,0,1);
			break;
			
			
			
			case 5: # Start Zukunft
				$where = 'releasedate > '.mktime();
				$content= $this->listSelectedMovies('releasedate ASC', $where);
				$this->content.=$this->doc->section('kommende Bundesstart',$content,0,1);
			break;
			case 6: # neueste Filme
				#$where = 'releasedate > '.mktime();
				$content= $this->listSelectedMovies('crdate DESC');
				$this->content.=$this->doc->section('neu angelegte Filme',$content,0,1);
			break;
			case 7: # dieser Seite
				$where = 'pid = '.$this->id;
				$content= $this->listSelectedMovies('title ASC', $where);
				$this->content.=$this->doc->section('Filme dieser Seite:',$content,0,1);
			break;
			
			case 'error': # dieser Seite
				$content=  "";
				$this->content.=$this->doc->section('ERROR:',$content,0,1);
			break;
		}
		
	}

	
	

	
	
		/**
		 * listet das aktuelle Programm auf
		 */
	function listProgram($where=' 1=1 ') {
		global $BE_USER,$LANG,$BACK_PATH,$TCA,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB, $SOBE;

		if($this->pageinfo['module'] != 'cinema_prg') {
			return  $LANG->getLL('choosePRG');
		}

		$fields = '*';
		$table = 'tx_tmdcinema_program';
		if($this->MOD_SETTINGS['cinema'] != 0) {
			$where .= " AND tx_tmdcinema_program.cinema = ".$this->MOD_SETTINGS['cinema'];
		} else {
			$where .= " AND tx_tmdcinema_program.cinema IN (".$this->cinemaOrder.") ";
		}
		$where .= ' AND pid = '.$this->pageinfo['uid'].t3lib_BEfunc::deleteClause($table);
		$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', 'date DESC, sorting');

		$cinemaMenu = t3lib_BEfunc::getFuncMenu(
			$this->id, # diese Seite
			'SET[cinema]',
			$this->MOD_SETTINGS['cinema'],
			$this->MOD_MENU['cinema']);

		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if($count<=0) {
			return $cinemaMenu.'<br />'.$LANG->getLL('noData');
		}
			
		$c =0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				/* Sortierung nur innerhalb der gleichen Woche */
			$curTable[$c] = $row;
			
			$curTable[$c]['info']  = t3lib_div::fixed_lgd_cs(strip_tags($row['info'] ), 80); 
			$curTable[$c]['info2'] = t3lib_div::fixed_lgd_cs(strip_tags($row['info2']), 80);
			
			$date1 = $curTable[$c]['date'];
			
#				$curTable[$c]['info2'] = "debug: <br/>".$c;
			
			if($date1 == $date2) {
				if($c > 0) {
					$curTable[$c-1]['next'] = $curTable[$c]['uid'];
				}
				
				if($c < $count) {
					$prev = $curTable[$c-1]['uid'];
				}

				if($prev) {
					$curTable[$c]['prev'] = $prev;
				}
				
			}
			
			$date2 = $date1;
			
			$c++;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
#debug($curTable);


		foreach($curTable as $key => $this->row) {
				# get some missing info from the film
			$rec = t3lib_BEfunc::getRecord('tx_tmdmovie_movie',$this->row['movie'],$fields='uid,title,poster',$where=''); 
			$this->row['title'] = $rec['title'];
			$this->row['poster'] = $rec['poster'];
			$this->row['mov_uid'] = $rec['uid'];
			
			
				// Datumszeile wenn es sich geändert hat.
			$date1 = $this->getFieldContentPrg('date');
			if($date1 != $date2) {
				$out .= '<tr><td  bgcolor="#D9D5C9" colspan="5" style="text-align: right; font-size: 20px;"><b>'.strftime("%a, ", $date1).$date1.'</b></td></tr>';
			} 
			$date2 = $date1;
	
			$out .= '<tr>';
			$out .= '<td style="vertical-align: top;" colspan="3" bgcolor="';
			$out .= ($this->row['hidden'])?'red':'green';
			$out .= '"><b style="color: white">';

			// Edit Film
			$params = "&edit[tx_tmdmovie_movie][".$this->getFieldContentMovie('movie')."]=edit";
			$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="7" height="12"').' title="'.$LANG->getLL('editRecord').'" alt="" />'.
						'</a>';
			$out .= $this->getFieldContentMovie('title').'</b></td>';
	
			$out .= '<td style="text-align: right;">';
				
#$out .= $this->getFieldContentPrg('uid');

				// Copy/Edit
				$params = '&cmd[tx_tmdcinema_program]['.$this->row['uid'].'][copy]=-'.$this->row['uid'];
				$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/clip_copy.gif','width="16" height="16"').' title="'.$LANG->getLL('prolongate').'" alt="" style="margin-right: 25px;" />';

				
				
				
					
				/* functionen */
		
				/*
				 * Der vorgänger vom voränger? wie "top" ???
				 if($this->row['prev']) {
					$params='&cmd[tx_tmdcinema_program]['.$this->row['uid'].'][move]=-'.$curTable[$key+1]['prev'];# $this->row['prev'];
					$str = '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img src="pilup.gif" width="11" height="10" /></a><br />';
				#	debug($str);
					$out .= $this->row['prev'].$str;
				}
				*/
				//Move Down
				if($this->row['next']) {
					$params='&cmd[tx_tmdcinema_program]['.$this->row['uid'].'][move]=-'.$this->row['next'];
					$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$LANG->getLL('moveDown').'" alt="" />';
				}
				
				// New after
				$params='&edit[tx_tmdcinema_program]['.$this->pageinfo['uid'].']=new';
				$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_page.gif','width="11" height="12"').' title="'.$LANG->getLL('newRecord').'" alt="" />'.
						'</a>';
				
				// Edit
				$params = "&edit[tx_tmdcinema_program][".$this->row[uid]."]=edit";
				$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="7" height="12"').' title="'.$LANG->getLL('editRecord').'" alt="" />'.
						'</a>';
		
				// hide /unhide
				if ($this->row['hidden'])	{
					$params='&data[tx_tmdcinema_program]['.$this->row['uid'].'][hidden]=0';
					$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide').'" alt="" />'.
							'</a>';
				} else {
					$params='&data[tx_tmdcinema_program]['.$this->row['uid'].'][hidden]=1';
					$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide').'" alt="" />'.
							'</a>';
				}
				
				// "Delete" link:
				$params='&cmd[tx_tmdcinema_program]['.$this->row['uid'].'][delete]=1';
				$out .= '<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').t3lib_BEfunc::referenceCount('tx_tmdcinema_program',$this->row['uid'],' (There are %s reference(s) to this record!)')).')) {jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');} return false;').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete').'" alt="" />'.
						'</a>';
	
				
				#$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpSelf(\''.$this->clipObj->selUrlDB('tx_tmdcinema_program',$this->row['uid'],1,($isSel=='copy'),array('returnUrl'=>'')).'\');').'">'.
				#		'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/clip_copy'.($isSel=='copy'?'_h':'').'.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:cm.copy',1).'" alt="" />'.
				#		'</a>';
				
			$out .= '</td>';
	
			$out .= '</tr>';
			
			$out .= '<tr>';
			$out .= '<td style="vertical-align: top;">'.$this->getFieldContentMovie('posterOne').'</td>';
			$out .= '<td style="vertical-align: top;">';
			$out .= 	'<b>'.$this->getFieldContentPrg('cinema').'</b><br />';
			$out .= 	$this->getFieldContentPrg('showtype').'<br />';
			$out .= 	($this->getFieldContentPrg('3d')) ? '3D<br />' : '';;
			$out .= 	($this->getFieldContentPrg('nores')) ? 'NoRes<br />' : '';
			$out .= 	'Woche: '.$this->getFieldContentPrg('week');
	
			$out .= '</td>';
	
			$out .= '<td style="vertical-align: top;">';
	#		$out .= $this->getFieldContentPrg('date_raw').'<br />';
			$out .= $this->getFieldContentPrg('info');
			$out .= ($this->getFieldContentPrg('info2')) ? '<hr />'.$this->getFieldContentPrg('info2') : '';
			$out .= '</td>';
			
			$out .= '<td style="vertical-align: top;">'.$this->getFieldContentPrg('program').'</td>';
			
			$out .= '</tr>';
		}

		

		// Optional
		// Neuen Datensatz
		$params='&edit[tx_tmdcinema_program]['.$this->pageinfo['uid'].']=new';
		$new = '&nbsp;<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_page.gif','width="11" height="12"').' title="'.$LANG->getLL('newRecord').'" alt="" />'.
				'</a>';

		$out = '<table border=1 cellpadding=1 cellspacing=1 width="100%"
		 		style="empty-cells:show;">'.$out.'</table>';
		

		return $cinemaMenu.$new.$out;
	}


	
	

			
			
		/**
		 * Listet die neuesten Filme auf als übersicht
		 *
		 * @todo
		 */
	function listSelectedMovies($sorting='crdate DESC', $where = "1 = 1 ", $count=20) {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB, $SOBE;
		
		$fields = '*';
		$table = 'tx_tmdmovie_movie';

		#$where .= t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', $sorting, $count);

		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) 
			{
			while ($this->row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) 
				{
				$out .= '<tr><td style="background-color: #d9d5c9">';
;

				$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick("&edit[tx_tmdmovie_movie][".$this->row[uid]."]=edit",$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="7" height="12"').' title="'.$LANG->getLL('editRecord').'" alt="" />'.
						'</a>';
				// hide /unhide
				if ($this->row['hidden'])	{
					$params='&data[tx_tmdmovie_movie]['.$this->row['uid'].'][hidden]=0';
					$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide').'" alt="" />'.
							'</a>';
				} else {
					$params='&data[tx_tmdmovie_movie]['.$this->row['uid'].'][hidden]=1';
					$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide').'" alt="" />'.
								'</a>';
				}
				$out .= "</td>";
				
				$out .= '<td style="vertical-align: top;" colspan="3" bgcolor="';
				$out .= ($this->row['hidden'])?'red':'green';
				$out .= '">';
				
				$out .= '<b style="margin-left: 10px; color: white; font-size: 14px;">'.$this->getFieldContentMovie('title').'</b>';
				$out .= '</td></tr>';
				
				$out .= '<tr style="vertical-align: top;"> ';
				$out .= '<td>';
				$out .= 	($this->getFieldContentMovie('releasedate'))	? $this->getFieldContentMovie('releasedate').'<hr />' : '';
				$out .= 	($this->getFieldContentMovie('runningtime'))	? $this->getFieldContentMovie('runningtime').' '.$LANG->getLL('time').'<br />' : '';
				$out .= 	($this->getFieldContentMovie('rating')) 			? $this->getFieldContentMovie('rating').'<br />' : '';
				$out .= 	($this->getFieldContentMovie('distributor')) 	? $this->getFieldContentMovie('distributor').'<br />' : '';
				$out .= 	($this->getFieldContentMovie('genre'))			? '<hr />'.$this->getFieldContentMovie('genre') : '';
				$out .= '</td>';
				$out .= '<td width="320">'.t3lib_div::fixed_lgd_cs(strip_tags($this->getFieldContentMovie('summary')), 400).'</td>';
				$out .= '<td width="270">'.$this->getFieldContentMovie('poster').'<hr />'.$this->getFieldContentMovie('mediafile').'</td>';
				$out .= '</tr>';
				
				
				
				}
			}
		else 
			{
			$out = '<tr><td bgcolor="#D9D5C9" colspan="4">Keine Filmdaten auf dieser Seite</td></tr>';
			}
			
		$out = '<table border=1 cellpadding=1 cellspacing=1 width="100%">'.$out.'</table>';

		
		return $out;
		}



	
	
	
	

			
			
	
	
			/**
			 * 
			 *
			 */
	function getFieldContentPrg($fN) {
		switch($fN) {
			/* tx_cinema */
			case 'date':
				return t3lib_BEfunc::date($this->row[$fN]);
			break;
			case 'showtype':
				$id = $this->row[$fN];
				if(!$this->cache['showtype'][$id]) {
					$t = t3lib_BEfunc::getRecord('tx_tmdcinema_showtype', $id,'showtype');
					$this->cache['showtype'][$id] = $t['showtype'];
				} 
				return $this->cache['showtype'][$id];
			break;
			case 'cinema':
				$id = $this->row[$fN];
				if(!$this->cache['cinema'][$id]) {
					$t = t3lib_BEfunc::getRecord('tt_address', $id,'name');
					$this->cache['cinema'][$id] = $t['name'];
				} 
				return $this->cache['cinema'][$id];
			break;
			case 'program':
				$lines = explode(chr(10),$this->row['program']);
				$days = array('Do', 'Fr', 'Sa', 'So', 'Mo', 'Di', 'Mi');
				foreach ($days as $day)	{
					$timeRow .= '<td style="font-size: 10px; text-align: center; border: 1px solid;">'.$day.'</td>';
				}
				$timeRow = '<tr>'.$timeRow.'</tr>';
				
				foreach($lines as $line) {
					$time = explode('|', $line);
					$time = implode('</td><td style="text-align: center; border: 1px solid black;">',$time);
					$timeRow .= '<tr><td style="text-align: center; border: 1px solid black; ">'.$time.'</td></tr>'; 
				}
				return '<table style="border-collapse: collapse; width:100%;">'.$timeRow.'</table>';
			break;
			
			case 'date_raw': # for debugging only
				return $this->row['date'];
			break;	
			default:
				return $this->row[$fN];
		}
	}


	
		/**
		 * Get Fields for current movie
		 * 
		 * @param $fN
		 * @return unknown_type
		 */
	
	function getFieldContentMovie($fN) {
		global $GLOBALS;

		switch($fN) {
			/* tx_movie */
			case 'title':
					return $this->row[$fN];
			break;
			case 'rating':
				/* ShowType-Cache erstellen */
				if(!$this->fskCache)
					{
					$select = 'uid,rating';
					$local_table = 'tx_tmdmovie_rating';
#					$whereClause = "1=1 ".$GLOBALS['TYPO3_DB']->enableFields($local_table);
					$res = $GLOBALS[TYPO3_DB]->exec_SELECTquery($select,$local_table,$whereClause,$groupBy,$orderBy,$limit);
					while($erg = $GLOBALS[TYPO3_DB]->sql_fetch_assoc($res)) {
						$this->rating[$erg['uid']] = $erg['rating'];
						}
					}
				
				return $this->rating[$this->row[$fN]];
			break;
			case 'releasedate':
				return strftime('%d.%m.%y', $this->row[$fN]);
			break;
			case 'distributor':
				if(!$this->distributorCache[$this->row[$fN]]) {
					$rec = t3lib_BEfunc::getRecord('tt_address',$this->row[$fN],$fields='uid,name',$where='');
					$this->distributorCache[$rec['uid']] = $rec['name'];
				}
				
				return $this->distributorCache[$this->row[$fN]];	
			break;
			case 'sound':
				return 'Sound'.$this->row[$fN];
			break;
			case 'posterOne': // nur ein Bild
				if(!$this->row['poster']) return "Kein Bild verfügbar!";
				
				list($img) = explode(',', $this->row['poster']);

				$thumbScript = '../../../../t3lib/thumbs.php';
				$theFile =  PATH_site."uploads/tx_tmdmovie/".$img;
				$tparams='';
				$size='70x70';
			 
				if(file_exists($theFile)) {
					$out = t3lib_BEfunc::getThumbNail($thumbScript,$theFile,$tparams,$size);
				}								
				return $out;
			break;
			case 'poster':
				if(!$this->row[$fN]) return "Kein Bild verfügbar!";
				
				$img = explode(',', $this->row[$fN]);
				
				foreach($img as $key => $val) {
					$thumbScript = '../../../../t3lib/thumbs.php';
					$theFile =  PATH_site."uploads/tx_tmdmovie/".$val;
					$tparams='';
					$size='70x70';
				 
					if(file_exists($theFile)) {
						$out .= t3lib_BEfunc::getThumbNail($thumbScript,$theFile,$tparams,$size)."&nbsp;";
					}
				}
				
				return $out;
			break;
			case 'mediafile':
				if(!$this->row[$fN]) return "Keine Media-Bilder verfügbar!";
				$img = explode(',', $this->row[$fN]);
				
				foreach($img as $key => $val) {
					$thumbScript = '../../../../t3lib/thumbs.php';
					$theFile =  PATH_site."uploads/tx_tmdmovie/".$val;
					$tparams='';
					$size='50x50';
				 
					if(file_exists($theFile)) {
						$out .= ''.t3lib_BEfunc::getThumbNail($thumbScript,$theFile,$tparams,$size).'&nbsp;';
					}
				}
				return $out;				
			break;
			case 'summary':
				return $this->row['summary'];
			break;
			case 'genre':
				$list = explode(",", $this->row[$fN]);
	
				foreach($list as $genreID) {
					if(!$this->genreCache[$genreID]) {
						$rec = t3lib_BEfunc::getRecord('tx_tmdmovie_genre',$genreID,'uid,genre',$where='');
						$this->genreCache[$rec['uid']] = $rec['genre'];
					}
					$genre[] = $this->genreCache[$genreID];
				}

				
				return implode(", ", $genre);
			break;	
			
			default:
				return $this->row[$fN];
		}
	}

	

	
	/**
	 * Find closest Weekstart
	 * 
	 * @param 	timestamp	timestamp to seek closeststartday
	 * @return	timestamp	timestamp, Midnight of thge first day of the week
	 * 
	 */
	function weekStartDay($ts) {
		$oneDay = 24*60*60;
		
		# reset timestamp to midnight
		list($day, $month, $year) = explode("-", strftime("%d-%m-%Y", $ts));

		$time = mktime(0,0,0, $month, $day, $year); 
		
		switch(strftime("%u", $time)) # %u = Tag der Woche 1= Montag
			{
			case 1: $wStart = $time - $oneDay*4; break; # Mo
			case 2: $wStart = $time - $oneDay*5; break; # DI
			case 3: $wStart = $time - $oneDay*6; break; # MI
			case 4: $wStart = $time - $oneDay*0; break; # DO
			case 5: $wStart = $time - $oneDay*1; break; # FR
			case 6: $wStart = $time - $oneDay*2; break; # SA
			case 7: $wStart = $time - $oneDay*3; break; # SO
			}

		return $wStart;		
	}
	

	
			
			
			
			
			
} /* END of class */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tmd_cinema_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>