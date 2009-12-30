<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Christian Tauscher <cms@media-distillery.de>
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

$LANG->includeLLFile('EXT:tmd_movie/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Film Informations' for the 'tmd_cinema' extension.
 *
 * @author	Christian Tauscher <cms@media-distillery.de>
 * @package	TYPO3
 * @subpackage	tx_tmdcinema
 */
class  tx_tmdmovie_module1 extends t3lib_SCbase {
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
				'5' => $LANG->getLL('function5'), # Bundesstart
				'6' => $LANG->getLL('function6'), # Neueste Filme
				'7' => $LANG->getLL('function7'), # Filme dieser Site
				),
			);

		parent::menuConfig();
		}

		
		
	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		
		switch((string)$this->MOD_SETTINGS['function'])	{
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
		 * Listet die neuesten Filme auf als übersicht
		 *
		 * @todo
		 */
	function listSelectedMovies($sorting='crdate DESC', $where = "1 = 1 ", $count=20) {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$TYPO3_DB, $SOBE;
		
		$fields = '*';
		$table = 'tx_tmdmovie_movie';
		$where .= " AND hidden IN(1,0) AND deleted = 0";
		
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
				// "Delete" link:
				$params='&cmd[tx_tmdmovie_movie]['.$this->row['uid'].'][delete]=1';
				$out .= '<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').t3lib_BEfunc::referenceCount('tx_tmdmovie_movie',$this->row['uid'],' (There are %s reference(s) to this record!)')).')) {jumpToUrl(\''.$SOBE->doc->issueCommand($params).'\');} return false;').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete').'" alt="" />'.
						'</a>';
				
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

	

	
			
} /* END of class */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_movie/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_movie/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_tmdmovie_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>