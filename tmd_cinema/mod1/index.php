<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Christian Tauscher <cms@media-distillery.de>
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

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

		
	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		
#		$cObj = t3lib_div::makeInstance('tslib_cObj');
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
					$this->doc->JScode = '
						<script language="javascript" type="text/javascript">
							script_ended = 0;
							function jumpToUrl(URL)	{
								document.location = URL;
							}
					
							/* from kj_recycler - Thanks a lot! */
						function confirm_delete(uid,pid) {
							var relocate_to;
							Check = confirm("Delete this record?");
						
							if(Check == false);
							if(Check == true)  {
							relocate_to = "index.php?id="+pid+"&action=delete&uid="+uid;
							alert("hier: "+relocate_to);
							window.location.replace(relocate_to);
							}
						}
						
						
						function checkData()
							{
							var company = document.from.company.value; 
							var checked = true;
						/*
							if (company.length == 0)
								{
								alert ("Dieses Feld ist ein Pflichtfeld!");
								checked = false;
								}
						*/
							if (checked == true)
								{
								document.form.method = "post";
								document.form.action = "";
								document.form.submit();
								}
							
							return checked;
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
				'1' => $LANG->getLL('function7'), # PROGRAMM
				'2' => $LANG->getLL('function6'), # Bundesstart
				'3' => $LANG->getLL('function1'), # Übersicht
				'5' => $LANG->getLL('function3'), # Neueste Filme
				'6' => $LANG->getLL('function4'), # Alle Filme
				'7' => $LANG->getLL('function5'), # Filme dieser Site
				)
			);
		parent::menuConfig();
		}

		
		
	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{

		debug(t3lib_div::GPvar("action"), "gpvar");

			switch(t3lib_div::GPvar("action"))
				{
				case 'edit':
					$header = "Datensatz ".$_GET['uid']." bearbeiten";
					$content = $this->editProgram($_GET['uid']);
				break;

				case 'add':
					$header = "Datensatz hinzu";
					$content .= '<a href="index.php?id='.$this->id.'&function=add">Neue Adresse anlegen</a>';					
					$this->updateRecord('add');
				break;
				case 'delete':
					$header = "Datensatz ".$_GET['uid']." GELÖSCHT";
					debug($_GET['uid']);
				break;
				
				case 'update':
					$header = "Datensatz ".$_GET['uid']." wurde gespeichert";
					$this->updateRecord('update');
				default:
					/*
					if($this->MOD_SETTINGS['function'] == 'add')
						{
						$header = "Neue Adresse";
						$content = $this->editAddress('add');
						}
					elseif ($this->MOD_SETTINGS['function'] == 'search' || $this->MOD_SETTINGS['function'] == '---')
						{
						$content = $this->search();
						}
					else
						#$content .= $this->listAdresses($this->MOD_SETTINGS['function']);
						*/
				break;
				}
			
		#	$this->content.=$this->doc->section($header, $content ,0,1);
		
		
		
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content= $this->listProgram();
				$this->content.=$this->doc->section('Programm:',$content,0,1);
			break;
			case 3:
				$content= $this->listSelctedMovies();
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break;
			case 6:
				$content= $this->listMoviesComplete();
				$this->content.=$this->doc->section('Message #4:',$content,0,1);
			break;
			case 7:
				$content= $this->listMoviesFromCurrentPage();
				$this->content.=$this->doc->section('Message #5:',$content,0,1);
			break;
			case 2:
				$content= $this->listSelctedMovies('releasedate DESC');
				$this->content.=$this->doc->section('Message #3: Bundesstart',$content,0,1);
			break;
			
		}
	}

	
	

		/**
		 * listet das aktuelle Programm auf
		 */
	function listProgram() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB;

		if($this->pageinfo['module'] != 'cinema_prg')
			return "Wählen Sie ein Seite  mit Programmdaten aus!";

		$fields = '*';
		$table = 'tx_tmdcinema_program';
/*		Seiten mit Filmen hier:  
			t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],
					'mod.'.$GLOBALS['MCONF']['name'].'.movieRoot'), 'mod.'.$GLOBALS['MCONF']['name'].'.movieRoot')
*/
		$where = 'pid = '.$this->pageinfo['uid'].t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', ' sorting ASC ');
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) 
			{
			while ($this->row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) 
				{
					// Datumszeile wenn es sich geändert hat.
				$date1 = $this->getFieldContentPrg('date');
				if($date1 != $date2) {
					$out .= '<tr><td><br /><b>'.$date1.'</b></td></tr>';
				} 
				$date2 = $date1;
				
				$rec = t3lib_BEfunc::getRecord('tx_tmdmovie_movie',$this->row['movie'],$fields='uid,title,poster',$where=''); 
				$this->row['title'] = $rec['title'];
				$this->row['poster'] = $rec['poster'];
				$this->row['mov_uid'] = $rec['uid'];
				
				$out .= '<tr>';
				$out .= '<td style="vertical-align: top;" colspan="5">'.$this->getFieldContentMovie('title').'</td>';
				$out .= '</tr>';
				
				$out .= '<tr>';
				$out .= '<td style="vertical-align: top;">'.$this->getFieldContentMovie('posterOne').'</td>';
				$out .= '<td style="vertical-align: top;">';
				$out .= 	$this->getFieldContentPrg('showtype').'<br />';
				$out .= 	$this->getFieldContentPrg('cinema').'<br />';
				$out .= 	'Woche: '.$this->getFieldContentPrg('week');
				$out .= 	($this->getFieldContentPrg('nores')) ? '<br />NoRes' : '';
				$out .= '</td>';

				$out .= '<td style="vertical-align: top;">';
				$out .= $this->getFieldContentPrg('info');
				$out .= ($this->getFieldContentPrg('info2')) ? '<hr />'.$this->getFieldContentPrg('info2') : '';
				$out .= '</td>';
				
				$out .= '<td style="vertical-align: top;">'.$this->getFieldContentPrg('program').'</td>';
				
				/* functionen */
				$out .= '<td  style="vertical-align: top;" bgcolor="#D9D5C9">';
				
				$out .= '<img src="pildown.gif">';
				$out .= '<img src="pilup.gif">';
				$out .= '<br />';


$params = "&edit[tx_tmdcinema_program][".$this->row[uid]."]=edit";
/*
<a href="#" onclick="window.location.href='alt_doc.php?returnUrl='+T3_THIS_LOCATION+'&amp;edit[tx_tmdcinema_program][2626]=edit'; return false;">
<a href="#" onclick="window.location.href='alt_doc.php?returnUrl='+T3_THIS_LOCATION+'&amp;edit['tx_tmdcinema_program']['837']=edit'; return false;">
$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath,-1)).'">'.
*/
$out .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->doc->backPath,-1)).'">'.
		'<img src="edit.gif" width="11" height="12" title="bearbeiten" alt="" />'.
		'</a>';


				$out .= '<br />';
				
				$out .= '<a href="index.php?id='.$this->id.(t3lib_div::GPvar("search")?'&search='.t3lib_div::GPvar('search'):'').'" onClick="confirm_delete(\''.$this->getFieldContentPrg('uid').'\',\''.$this->id.'\')">
								<img src="'.$GLOBALS['BACK_PATH'].'gfx/i/recycler.gif" border="0" title="Diese Adresse LÖSCHEN!"></a>';
				$out .= '<br />';
				
				/*
				$out .= '<a href="#" onclick="' . htmlspecialchars('return jumpExt(\'' . $this->backPath . 'db_new.php?id=' . $this->id . '\');') . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/new_el.gif') . ' title="' . $LANG->getLL('newRecordGeneral', 1) . '" alt="" />' .
						'</a>';
				*/
				$out .= '<img src="new_el.gif">';
				
				$out .= '</tr>';
				
				
				}
			}
		else 
			{
			$out = '<tr><td bgcolor="#D9D5C9" colspan="4">Keine Programm auf dieser Seite</td></tr>';
			}
			
		$out = '<table border=1 cellpadding=1 cellspacing=1 width="100%"
				 style="empty-cells:show;">'.$out.'</table>';

		return $out;
	}


	
	
		/**
		 * Listet die Filme auf der aktuellen Seite 
		 *
		 * @todo	Recursion übder den Seiten baum
		 */
	function listMoviesFromCurrentPage() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB;

		if(!$this->pageinfo['uid'])
			return "Wählen Sie ein Seite  mit Adressdaten aus!";

		$fields = '*';
		$table = 'tx_tmdmovie_movie';
/*		Seiten mit Filmen hier:  
			t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],
					'mod.'.$GLOBALS['MCONF']['name'].'.movieRoot'), 'mod.'.$GLOBALS['MCONF']['name'].'.movieRoot')
*/
		$where = 'pid = '.$this->pageinfo['uid'].t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);

		if(!$add_where)
			{
#			$where .= ' AND '.$GLOBALS['TYPO3_DB']->listQuery('tt_address.tx_furnier_branch', $branch, 'tt_address');
			}
		else
			{
#			$where .= " AND ".$add_where	;
			}
			
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', ' crdate DESC ');

		$out .= '<tr>';
		$out .= '<td><b>title</b></td>';
		$out .= '<td>runningtime</td>';
		$out .= '<td>rating</td>';
		$out .= '<td>distributor</td>';
		$out .= '<td>releasedate</td>';
		$out .= '<td>sound</td>';
		$out .= '<td>summary</td>';
		$out .= '<td>poster</td>';
		$out .= '<td>mediafile</td>';
		$out .= '</tr>';

		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) 
			{
			while ($this->row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) 
				{
				$out .= '<tr>';
				$out .= '<td><b>'.$this->getFieldContentMovie('title').'</b></td>';
				$out .= '<td>'.$this->getFieldContentMovie('runningtime').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('rating').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('distributor').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('releasedate').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('sound').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('summaryCrop').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('poster').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('mediafile').'</td>';
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
		 * Listet die neuesten Filme auf als übersicht
		 *
		 * @todo
		 */
	function listSelctedMovies($sorting='crdate DESC', $where = "1 = 1 ") {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB;

		$fields = '*';
		$table = 'tx_tmdmovie_movie';

		$where = '1 = 1 '.t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', $sorting, 20);

		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) 
			{
			while ($this->row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) 
				{
				$out .= '<tr>';
				$out .= '<td><b>'.$this->getFieldContentMovie('title').'</b></td>';
				$out .= '<td>'.$this->getFieldContentMovie('poster').'</td>';
				$out .= '<td>';
				$out .= 	'Dauer:  '.$this->getFieldContentMovie('runningtime').'<br />';
				$out .= 	'FSK:    '.$this->getFieldContentMovie('rating').'<br />';
				$out .= 	'Verleih:'.$this->getFieldContentMovie('distributor').'<br />';
				$out .= 	'Start:  '.$this->getFieldContentMovie('releasedate').'<br />';
				$out .= '</td>';
				$out .= '<td>'.$this->getFieldContentMovie('summaryCrop').'</td>';
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
		 * Listet alle Filme auf 
		 *
		 * @todo	Recursion übder den Seiten baum
		 * 			zusammenfassen mit listMoviesFromCurrentPage
		 */
	function listMoviesComplete() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB;

		$fields = '*';
		$table = 'tx_tmdmovie_movie';

		$where = "1=1 ".t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', ' crdate DESC ', 20);
 
		$out .= '<tr>';
		$out .= '<td><b>title</b></td>';
		$out .= '<td>runningtime</td>';
		$out .= '<td>rating</td>';
		$out .= '<td>distributor</td>';
		$out .= '<td>releasedate</td>';
		$out .= '<td>sound</td>';
		$out .= '<td>summary</td>';
		$out .= '<td>poster</td>';
		$out .= '<td>mediafile</td>';
		$out .= '</tr>';
	
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) 
			{
			while ($this->row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) 
				{
				$out .= '<tr>';
				$out .= '<td><b>'.$this->getFieldContentMovie('title').'</b></td>';
				$out .= '<td>'.$this->getFieldContentMovie('runningtime').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('rating').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('distributor').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('releasedate').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('sound').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('summary').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('poster').'</td>';
				$out .= '<td>'.$this->getFieldContentMovie('mediafile').'</td>';
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
				return strftime("%d.%m.%Y", $this->row[$fN]);
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
				return nl2br($this->row[$fN]);
			break;
			default:
				return $this->row[$fN];
		}
	}

		
	function getFieldContentMovie($fN) {
		switch($fN) {
			/* tx_movie */
			case 'title':
					return $this->row[$fN];
			break;
			case 'summary':
					return 'inhalt';
			break;
			case 'fsk':
				return 'fsk'.$this->row[$fN];
			break;
			case 'releasedate':
				return strftime('%d.%m.%y', $this->row[$fN]);
			break;
			case 'distributor':
				return 'Verleih'.$this->row[$fN];
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
				if(!$this->row[$fN]) return "Kein Bild verfügbar!";
				$img = explode(',', $this->row[$fN]);
				
				foreach($img as $key => $val) {
					$thumbScript = '../../../../t3lib/thumbs.php';
					$theFile =  PATH_site."uploads/tx_tmdmovie/".$val;
					$tparams='';
					$size='70x70';
				 
					if(file_exists($theFile)) {
						$out .= t3lib_BEfunc::getThumbNail($thumbScript,$theFile,$tparams,$size);
					}
				}
				return $out;				
			break;
			case 'summaryCrop':
				$out = $this->row['summary'];
				$out = str_split($out, 100);
				return $out[0];
			break;
			
			default:
				return $this->row[$fN];
		}
	}

	
	
		function deleteRecord($id)
			{
			$where = " uid=".$id;
			$data = array("hidden" => 1);
die('delete');
			#$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_address', $where, $data);
			}
	
			/**
			 * Suchen von Daten
			 */
		function search()
			{
			$out = ' 
				<form name="searchForm" action="" method="get">
				<fieldset>
					<legend>Adressensuche</legend> 
						<input type="input" name="search" value="'.t3lib_div::GPvar("search").'" size="40" tabindex="1" /><br /> <!-- onfocus="this.value=\'\'" -->
				<input type="submit" value="Suchen" tabindex="36" />
				</fieldset>
				</form>
				
				';
			
			if(t3lib_div::GPvar("search"))
				{
				$fields = array(first_name,last_name,title,email,phone,mobile,www,address,company,city,zip,region,country,fax,tx_furnier_position,tx_furnier_companyaddition,tx_furnier_postbox,tx_furnier_user_address,tx_furnier_user_postboxzip,tx_furnier_user_city,tx_furnier_user_zip,tx_furnier_user_email,tx_furnier_user_phone,tx_furnier_info_fs_was,tx_furnier_info_fs_menge,tx_furnier_info_fs_wer);
				$words = explode(" ", t3lib_div::GPvar("search"));
				$where = $GLOBALS['TYPO3_DB']->searchQuery($words, $fields, "tt_address");
		
				$out .= $this->listAdresses(0, $where);
				}
			
			return $out;
			}


			/**
			 * Datensatz aktualisieren
			 */
		function updateRecord($cmd)
			{
			$data = $_POST;
#debug($data);
			unset($data['SET']);
			unset($data['action']);
			
				// Ein paar werte umrechnen f�r die Datenbank
				#tx_furnier_branch
			foreach($data[tx_furnier_branch] as $key => $val)
				{
				$temp[] = $val;
				}
			$data[tx_furnier_branch] = implode(",", $temp);

			
				#tx_furnier_info_fs_since
			if($data[tx_furnier_info_fs_since][m] && $data[tx_furnier_info_fs_since][d] && $data[tx_furnier_info_fs_since][y])
				{
				$data[tx_furnier_info_fs_since] = mktime(1,0,0, (int)$data[tx_furnier_info_fs_since][m], (int)$data[tx_furnier_info_fs_since][d], (int)$data[tx_furnier_info_fs_since][y]);
				}
			else
				unset($data[tx_furnier_info_fs_since]);
			
				# Checkboxen m�ssen explizit gesetzt wrden.
			if(!$data['tx_furnier_newsletter']) 	$data['tx_furnier_newsletter']		= 0;
			if(!$data['module_sys_dmail_html']) 	$data['module_sys_dmail_html'] 		= 0;
			if(!$data['tx_furnier_info_top30'])		$data['tx_furnier_info_top30']		= 0;
			if(!$data['tx_furnier_info_producer'])	$data['tx_furnier_info_producer']	= 0;	
			if(!$data['tx_furnier_agb'])			$data['tx_furnier_agb']				= 0;
			if(!$data['tx_furnier_view_fe'])		$data['tx_furnier_view_fe'] 		= 0;
			
			# debug($data, "UPDATE");
			if($cmd == 'update')
				{
				$where = " uid=".$data['uid']; unset($data[uid]);
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_address',$where,$data);
				}
			
			if($cmd == 'add')
				{
				$data['pid'] = $this->id;
				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_address',$data);	
				}
			
			}


			/**
			 * Programm bearbeiten
			 * 
			 */
			 
		function editProgram($id='')
			{
			$out = $_GET['uid']." ".$_GET['action'];

			if($adrID != 'add')
				{
				$row = t3lib_BEfunc::getRecord('tx_tmdcinema_program', $adrID);
				} 
			
 
/*
temp_title
week
date
info
info2
nores
cinema
showtype
movie
program
*/
			$out .= '
			<form name="adrForm" >

				<fieldset>
					<legend>Firma</legend> 
					<div style="width: 150px; float: left;"><label>Film: </label></div>   	<input type="input" name="movie" 	value="'.$row['movie'].'" size="40" tabindex="1" /><br />
					<div style="width: 150px; float: left;"><label>Woche: </label></div>  	<input type="input" name="week" 	value="'.$row['week'].'" size="40" tabindex="2" /><br />
					<div style="width: 150px; float: left;"><label>Datum: </label></div>  	<input type="input" name="date" 	value="'.$row['date'].'" size="40" tabindex="2" /><br />
					<div style="width: 150px; float: left;"><label>Kino: </label></div>   	<input type="input" name="cinema" 	value="'.$row['cinema'].'" size="40" tabindex="4" /><br />
					<div style="width: 150px; float: left;"><label>NoRes: </label></div>  	<input type="input" name="nores" 	value="'.$row['nores'].'" size="40" tabindex="5" /><br />
					<div style="width: 150px; float: left;"><label>ShowType: </label></div> <input type="checkbox" name="showtype" value="1" '.($row['showtype']? 'checked="checked"' : "").' tabindex="6" />
					<div style="width: 150px; float: left;"><label>Program: </label></div> 	<textarea name="program" rows="4" cols="40" tabindex="7">'.$row['program'].'</textarea><br />
					<div style="width: 150px; float: left;"><label>info: </label></div>		<textarea name="program" rows="4" cols="40" tabindex="7">'.$row['info1'].'</textarea><br />
					<div style="width: 150px; float: left;"><label>info2: </label></div>	<textarea name="program" rows="4" cols="40" tabindex="7">'.$row['info2'].'</textarea><br />
				</fieldset>
				
				<input type="submit" value="Eintragen" onClick="checkData()" tabindex="36" />
				</form>
				
				';
			
#			$out .= t3lib_div::view_array($row);
			
			return $out;	
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