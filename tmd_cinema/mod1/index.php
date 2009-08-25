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
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
				)
			);
		parent::menuConfig();
		}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		
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
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content='<div align="center"><strong>Hello World!</strong></div><br />
					The "Kickstarter" has made this module automatically, it contains a default framework for a backend module but apart from that it does nothing useful until you open the script '.substr(t3lib_extMgm::extPath('tmd_cinema'),strlen(PATH_site)).$pathSuffix.'index.php and edit it!
					<hr />
					<br />This is the GET/POST vars sent to the script:<br />'.
					'GET:'.t3lib_div::view_array($_GET).'<br />'.
					'POST:'.t3lib_div::view_array($_POST).'<br />'.
					'';
				
				debug(t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],
					'mod.'.$GLOBALS['MCONF']['name'].'.movieRoot'), 'mod.'.$GLOBALS['MCONF']['name'].'.movieRoot');

				$this->content.=$this->doc->section('Message #1:',$content,0,1);
			break;
			case 2:
				$content= $this->listProgramm();
				$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 3:
				$content= $this->listMovies();
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break;
		}
	}
	
		/**
		 * listet das aktuelle Programm auf
		 */
	function listProgramm() {
			return '<div align=center><strong>Menu item #2...</strong></div>';
	}

		/**
		 * Listet die Filme auf 
		 *
		 */
	function listMovies() {
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
#				$where .= ' AND '.$GLOBALS['TYPO3_DB']->listQuery('tt_address.tx_furnier_branch', $branch, 'tt_address');
				}
			else
				{
#				$where .= " AND ".$add_where	;
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
					$out .= '<td><b>'.$this->getFieldContent('title').'</b></td>';
					$out .= '<td>'.$this->getFieldContent('runningtime').'</td>';
					$out .= '<td>'.$this->getFieldContent('rating').'</td>';
					$out .= '<td>'.$this->getFieldContent('distributor').'</td>';
					$out .= '<td>'.$this->getFieldContent('releasedate').'</td>';
					$out .= '<td>'.$this->getFieldContent('sound').'</td>';
					$out .= '<td>'.$this->getFieldContent('summary').'</td>';
					$out .= '<td>'.$this->getFieldContent('poster').'</td>';
					$out .= '<td>'.$this->getFieldContent('mediafile').'</td>';
					$out .= '</tr>';
					}
				}
			else 
				{
				if($branch == 0)
					$out = "Es konnten Keine Adressen auf dieser Seite gefunden werden";
				else
					$out = '<tr><td bgcolor="#D9D5C9" colspan="4">Keine Adressen auf dieser Seite</td></tr>';
				}
			
			$out = '<table border=1 cellpadding=1 cellspacing=1 width="100%">'.$out.'</table>';

			return $out;
			}

			
	function getFieldContent($fN) {
		switch($fN) {
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
			case 'poster':
				

#$cObj->IMAGE(...); 		

				list($img) = explode(',', $this->row[$fN]);
				$img = "uploads/tx_tmdmovie/$img";
				return $img;
			break;
			case 'mediafile':
				list($img) = explode(',', $this->row[$fN]);
				$img = "uploads/tx_tmdmovie/$img";
				return $img;
			break;
			default:
				return $this->row[$fN];
		}
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