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
class  tx_tmdcinema_module1 extends t3lib_SCbase {
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

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],50);

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
				'1' => 'Programm Ansicht ausführlich',
				'2' => 'Programm Ansicht kurz',
				'3' => 'Programm ohne Termin',
				),
			'cinema' => Array (
				'0' => $LANG->getLL('all'), # PROGRAMM
				),
			'dateMenu1' => array(
				'-0' => 'diese Woche',
				'-1' => '-1 Woche',
				'-2' => '-2 Wochen',
				'-3' => '-3 Wochen',
				'-4' => '-4 Wochen',
				'-100' => 'Vergangenheit',
				),
			'dateMenu2' => array(
				'0' => 'diese Woche',
				'1' => '+1 Woche',
				'2' => '+2 Wochen',
				'3' => '+3 Wochen',
				'4' => '+4 Wochen',
				'100' => 'Zukunft',
				)

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
		if($this->MOD_SETTINGS['dateMenu1'] == '-100') $this->MOD_SETTINGS['dateMenu1'] = 0 ; # Vergangenheit
		if($this->MOD_SETTINGS['dateMenu2'] == '100') $this->MOD_SETTINGS['dateMenu2'] = 100; # Zukunft

		switch((string)$this->MOD_SETTINGS['function'])	{
			case '1': # -1 0 1

				$start = $this->weekFirstLastDay(mktime(), 0, $this->MOD_SETTINGS['dateMenu1']);
				$stop  = $this->weekFirstLastDay(mktime(), 1, $this->MOD_SETTINGS['dateMenu2']);

				$where = "date >= ".$start." AND date <= ".$stop;
				$content= $this->listProgram($where, 'full');
				$this->content.=$this->doc->section('Programm ausführlich:',$content,0,1);
			break;

			case '2': # Programm Kurze Übersicht
				$start = $this->weekFirstLastDay(mktime(), 0, $this->MOD_SETTINGS['dateMenu1']);
				$stop  = $this->weekFirstLastDay(mktime(), 1, $this->MOD_SETTINGS['dateMenu2']);

				$where = "date >= ".$start." AND date <= ".$stop;
				$content= $this->listProgram($where, 'short');
				$this->content.=$this->doc->section('Programm kurz:',$content,0,1);
			break;

			case '3': # Program Ohne Termin
				$where = "date = 0";
				$content= $this->listProgram($where);
				$this->content.=$this->doc->section('Programm ohne Termin:',$content,0,1);
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
	function listProgram($where=' 1=1 ', $view='full') {
		global $BE_USER,$LANG,$BACK_PATH,$TCA,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB, $SOBE;

		if($this->pageinfo['module'] != 'cinema_prg') {
			return  $LANG->getLL('choosePRG');
		}




		$cinemaMenu = t3lib_BEfunc::getFuncMenu(
			$this->id, # diese Seite
			'SET[cinema]',
			$this->MOD_SETTINGS['cinema'],
			$this->MOD_MENU['cinema']);

		$date1Menu = t3lib_BEfunc::getFuncMenu(
			$this->id, # diese Seite
			'SET[dateMenu1]',
			$this->MOD_SETTINGS['dateMenu1'],
			$this->MOD_MENU['dateMenu1']);

		$date2Menu = t3lib_BEfunc::getFuncMenu(
			$this->id, # diese Seite
			'SET[dateMenu2]',
			$this->MOD_SETTINGS['dateMenu2'],
			$this->MOD_MENU['dateMenu2']);

		$menuItems = $date1Menu.' - '.
					strftime("%a %d.%m.%y", $this->weekFirstLastDay(mktime(), 0, 0)).' - '.
					strftime("%a %d.%m.%y", $this->weekFirstLastDay(mktime(), 1, 0)).' - '.
					$date2Menu.'&nbsp;&nbsp;&nbsp;'.
					$cinemaMenu.
					$new.'<br />';




		$fields = '*';
		$table = 'tx_tmdcinema_program';
		if($this->MOD_SETTINGS['cinema'] != 0) {
			$where .= " AND tx_tmdcinema_program.cinema = ".$this->MOD_SETTINGS['cinema'];
		} else {
			$where .= " AND tx_tmdcinema_program.cinema IN (".$this->cinemaOrder.") ";
		}
		$where .= ' AND pid = '.$this->pageinfo['uid'].t3lib_BEfunc::deleteClause($table);
		$res  = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', 'date DESC, sorting');
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if($count<=0) {
			return $menuItems.$LANG->getLL('noData');
		}




		$c =0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				/* Sortierung nur innerhalb der gleichen Woche */
			$curTable[$c] = $row;

			list($day, $month, $year) = explode(",", strftime("%e,%m,%Y", $curTable[$c]['date']));
			$curTable[$c]['date'] = mktime(0,0,0, $month, $day, $year);


			$curTable[$c]['info']  = t3lib_div::fixed_lgd_cs(strip_tags($row['info'] ), 80);
			$curTable[$c]['info2'] = t3lib_div::fixed_lgd_cs(strip_tags($row['info2']), 80);

			$date1 = $curTable[$c]['date'];

#			$curTable[$c]['info2'] = "debug: <br/>".$curTable[$c]['date'].'<br />'.$curTable[$c]['sorting'];

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
			$rec = t3lib_BEfunc::getRecord('tx_tmdmovie_movie',$this->row['movie'],$fields='uid,artikel,title,short,poster,version3d,runningtime',$where='');
			$this->row['title'] = $rec['title'];
			$this->row['poster'] = $rec['poster'];
			$this->row['mov_uid'] = $rec['uid'];
			$this->row['version3d'] = $rec['version3d'];
			$this->row['length'] = $rec['runningtime'];
			$this->row['short'] = $rec['short'];
			$this->row['artikel'] = $rec['artikel'];

			if($view == 'full') {
				$out .= $this->view1();

			} else {
				$out .= $this->view2();
			}

		} # end foreach


		if($view == 'full') {
			$out = '<table border=1 cellpadding=1 cellspacing=1 width="100%"
		 		style="empty-cells:show;">'.$out.'</table>';
		} else {
			$out = '<div style="clear: bioth; width: 100%;">'.$out.'</div><div style="clear: both;"><!-- --></div>';
		}


		// Optional
		// Neuen Datensatz
		$params='&edit[tx_tmdcinema_program]['.$this->pageinfo['uid'].']=new';
		$new = '&nbsp;<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_page.gif','width="11" height="12"').' title="'.$LANG->getLL('newRecord').'" alt="" />'.
				'</a>';



		return $new.$menuItems.$out;
	}




		/**
		 * Volle Ansicht
		 *
		 * @return unknown_type
		 */
	function view1() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB, $SOBE;

			// Datumszeile wenn es sich geändert hat.
		$this->date1 = $this->getFieldContentPrg('date');
		if($this->date1 != $this->date2) {
			$out .= '<tr><td  bgcolor="#D9D5C9" colspan="5" style="text-align: right; font-size: 20px; line-height: 24px;"><b>'.strftime("%a, ", $this->date1).$this->date1.'</b></td></tr>';
		}
		$this->date2 = $this->date1;

		$out .= '<tr>';
			$out .= '<td style="vertical-align: top;" colspan="3" bgcolor="';
			$out .= ($this->row['hidden'])?'red':'green';
			$out .= '"><b style="color: white">';

			// Edit Film
			$params = "&edit[tx_tmdmovie_movie][".$this->getFieldContentMovie('movie')."]=edit";
			$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="7" height="12"').' title="'.$LANG->getLL('editMovie', 'Film bearbeiten').'" alt="" />'.
						'</a>';
			$out .= $this->getFieldContentMovie('title').'</b></td>';

			$out .= '<td style="vertical-align: top; text-align: right;" colspan="3" bgcolor="';
			$out .= ($this->row['hidden'])?'red':'green';
			$out .= '">';

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
			$out .= 	$this->getFieldContentPrg('features').'<br />';
			$out .= 	($this->getFieldContentPrg('nores')) ? 'NoRes<br />' : '';
			$out .= 	"ca. ".$this->getFieldContentMovie('length')." min.<br />";
			$out .= 	'Woche: '.$this->getFieldContentPrg('week');

			$out .= '</td>';

			$out .= '<td style="vertical-align: top;">';
	#		$out .= $this->getFieldContentPrg('date_raw').'<br />';
			$out .= $this->getFieldContentPrg('info');
			$out .= ($this->getFieldContentPrg('info2')) ? '<hr />'.$this->getFieldContentPrg('info2') : '';
			$out .= '</td>';

			$out .= '<td style="vertical-align: top;">'.$this->getFieldContentPrg('program').'</td>';
		$out .= '</tr>';

		return $out;
	}



		/**
		 * Kurze Ansicht
		 *
		 * @return unknown_type
		 */
	function view2() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB, $SOBE;

			// Datumszeile wenn es sich geändert hat.
		$this->date1 = $this->getFieldContentPrg('date');
		if($this->date1 != $this->date2) {
			$out .= '<div style="clear: both; background: #D9D5C9; text-align:right; font-size: 20px; line-height: 24px; padding-right: 10px;"><b>'.$this->date1.'</b></div>';
		}
		$this->date2 = $this->date1;



		$out .= '<div style="float: left; width: 100px; height: 245px; margin: 0 5px 5px 0; color: white !important; padding: 3px; overlow: hidden; background: ';
		$out .= ($this->row['hidden'])?'red;':'green;';
		$out .= '">';

			$out .= '<div style="color: white; font-weight:bold; width: 100%; height: 3em; overflow: hidden; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 3px;">'.$this->getFieldContentMovie('title')."</div>";

			$out .= '<div style="border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 3px;">';
				// Copy/Edit
				$params = '&cmd[tx_tmdcinema_program]['.$this->row['uid'].'][copy]=-'.$this->row['uid'];
				$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/clip_copy.gif','width="16" height="16"').' title="'.$LANG->getLL('prolongate').'" alt="" />';
				//Move Down
				if($this->row['next']) {
					$params='&cmd[tx_tmdcinema_program]['.$this->row['uid'].'][move]=-'.$this->row['next'];
					$out .= '<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$LANG->getLL('moveDown').'" alt="" />';
				}

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
			$out .= '</div>';

			$out .= $this->getFieldContentMovie('posterOneBig');

			$out .= '<div style="border-top: 1px solid #ccc; padding-top: 3px; margin-top: 3px; color: white; white-space: nowrap; ">';
				$out .= $this->getFieldContentPrg('cinema').'<br />';
				$out .= $this->getFieldContentPrg('showtype').'<br />';
				$out .= $this->getFieldContentPrg('features');
			$out .= '</div>';


		$out .= '</div>';

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
				$lines = explode(chr(10), trim($this->row['program']));
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
			case 'features':
				if( ($this->row['features']&1) || $this->getFieldContentMovie('version3d') ) 	$out[] = "3D";
				if(  $this->row['features']&2) 													$out[] = "K-Kino";

				return implode(", ", $out);
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
					if($this->row['short']) {
						return $this->row['short'];
					} else {
						switch($this->row['artikel']) {
							case 1: $art = 'Der '; break;
							case 2: $art = 'Die '; break;
							case 3: $art = 'Das '; break;
							case 4: $art = 'The '; break;
							case 5: $art = 'Lè ';  break;
						}

						return $art.$this->row[$fN];
					}

			break;
			case 'rating':
				/* ShowType-Cache erstellen */
				if(!$this->fskCache) {
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
			case 'posterOneBig': // nur ein Bild
				if(!$this->row['poster']) return "Kein Bild verfügbar!";

				list($img) = explode(',', $this->row['poster']);

				$thumbScript = '../../../../t3lib/thumbs.php';
				$theFile =  PATH_site."uploads/tx_tmdmovie/".$img;
				$tparams='';

				if(file_exists($theFile)) {
					if($fN == 'posterOneBig') {
						$out = t3lib_BEfunc::getThumbNail($thumbScript,$theFile,$tparams,'97x150');
					} else {
						$out = t3lib_BEfunc::getThumbNail($thumbScript,$theFile,$tparams,'70x70');
					}
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
			case 'version3d':
				return $this->row[$fN];
			break;
			case 'length':
				return $this->row['length'];
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
	function weekFirstLastDay($ts, $fl=0, $startWeek) {

		if(t3lib_BEfunc::getModTSconfig($this->id, 'mod.'.$GLOBALS['MCONF']['name'].'.DEBUG')) {
			$today = t3lib_BEfunc::getModTSconfig($this->id, 'mod.'.$GLOBALS['MCONF']['name'].'.DEBUG');
			list($d, $m, $y) = explode('-', $today['properties']['day']);
			$debugDay = mktime(0,0,0,$m,$d,$y);
#debug(strftime("%d.%m.%y", $debugDay));
		}

		if($debugDay > 1)  {
			$now = $debugDay;
		} else {
			$now = $ts;
		}

		switch(strftime("%u", $now)) { # %u = Tag der Woche 1= Montag
			case 1: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-4, date("Y", $now)); break; # Mo
			case 2: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-5, date("Y", $now)); break; # DI
			case 3: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-6, date("Y", $now)); break; # MI
			case 4: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-0, date("Y", $now)); break; # DO
			case 5: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-1, date("Y", $now)); break; # FR
			case 6: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-2, date("Y", $now)); break; # SA
			case 7: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-3, date("Y", $now)); break; # SO
		}


		if($fl == 0) {
			return mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7*$startWeek, date("Y", $wStart));
		} else {
			$startWeek++;
			return mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7*$startWeek, date("Y", $wStart))-1;
		}
	}








} /* END of class */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_tmdcinema_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>