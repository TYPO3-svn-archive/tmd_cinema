<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Christian Tauscher <cms@media-distillery.de>
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_formidableapi);


/**
 * Plugin 'Book Reservation' for the 'tmd_cinema' extension.
 *
 * @author	Christian Tauscher <cms@media-distillery.de>
 * @package	TYPO3
 * @subpackage	tmd_cinema
 */
class tx_tmdcinema_pi2 extends tslib_pibase
	{
	var $prefixId      = 'tx_tmdcinema_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tmd_cinema_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'tmd_cinema';	// The extension key.
	var $pi_checkCHash = false; // nicht nötig in diesem Bereich.



	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		$content: The content of the PlugIn
	 * @param	array		$conf: The PlugIn Configuration
	 * @return	The content that should be displayed on the website
	 */
	function main($content,$conf)
		{
		$this->internal['currentTable'] = 'tx_tmdcinema_booking';

		switch((string)$conf['CMD'])
			{
			case 'singleView':
				list($t) = explode(':',$this->cObj->currentRecord);
/*				$this->internal['currentTable']=$t; */
				$this->internal['currentRow']=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:
				if (strstr($this->cObj->currentRecord,'tt_content'))
					{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
					}
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
			}
		}



	/**
	 * Shows a list of database entries
	 *
	 * @param	string		$content: content of the PlugIn
	 * @param	array		$conf: PlugIn Configuration
	 * @return	HTML list of table entries
	 */
	function listView($content,$conf)
		{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		$lConf = $this->conf['listView.'];	// Local settings for the listView function

		if ($this->piVars['showUid'])
			{	// If a single element should be displayed:
			$content = $this->singleView($content,$conf);
			return $content;
			}
		else
			{
			$items=array(
				'1'=> $this->pi_getLL('list_mode_1','Mode 1'),
				'2'=> $this->pi_getLL('list_mode_2','Mode 2'),
				'3'=> $this->pi_getLL('list_mode_3','Mode 3'),
				);

			if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
			if (!isset($this->piVars['mode']))	$this->piVars['mode']=1;

				// Initializing the query parameters:
			list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
			$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,3);		// Number of results to show in a listing.
			$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
			$this->internal['searchFieldList']='name,email,note,movie';
			$this->internal['orderByList']='uid,name,email,movie';

				// Get number of records:
			$res = $this->pi_exec_query('tx_tmdcinema_booking',1, " AND pid=".$this->conf['bookingPid']);
			list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);


				// Make listing query, pass query to SQL database:
			if(!$this->internal['orderBy'])
				{
				$sortBy = "movie,timedate ASC";
				}
			$res = $this->pi_exec_query('tx_tmdcinema_booking',0 , " AND pid=".$this->conf['bookingPid'],$mm_cat='',$groupBy='', $sortBy);

				// Put the whole list together:
			$fullTable='';	// Clear var;
		#	$fullTable.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!

				// Adds the mode selector.
			$fullTable.=$this->pi_list_modeSelector($items);

				// Adds the whole list table
			$fullTable.=$this->pi_list_makelist($res);

				// Adds the result browser:
			$fullTable.=$this->pi_list_browseresults();

				// Adds the search box:
			$fullTable.=$this->pi_list_searchBox();

				// Returns the content from the plugin.
			return $fullTable;
			}
		}



	/**
	 * Display a single item from the database
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	HTML of a single database entry
	 */
	function singleView($content,$conf)
		{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->internal['currentTable'] = 'tx_tmdcinema_booking';
		$this->internal['currentRow'] = $this->pi_getRecord('tx_tmdcinema_booking',$this->piVars['showUid']);



 		$this->oForm = t3lib_div::makeInstance("tx_ameosformidable");
		$this->oForm->init($this, t3lib_extmgm::extPath($this->extKey) . "pi2/form/book_form.xml", $this->getFieldContent('uid'));

		$out ='<div'.$this->pi_classParam('singleView').'>
				<p'.$this->pi_classParam("singleViewField-timedate").'><h1>'.$this->getFieldContent('timedate')." - ".$this->getFieldContent('movie').'</h1></p>
				<p'.$this->pi_classParam("singleViewField-name").'><strong>'.$this->getFieldHeader('name').':</strong> '.$this->getFieldContent('name').'</p>
				<p'.$this->pi_classParam("singleViewField-seats").'><strong>'.$this->getFieldHeader('seats').':</strong> '.$this->getFieldContent('seats').'</p>
				<p'.$this->pi_classParam("singleViewField-note").'><strong>'.$this->getFieldHeader('note').':</strong> '.$this->getFieldContent('note').'</p>
				<p>'.$this->pi_list_linkSingle($this->pi_getLL('back','Back'),0).'</p></div>';

		$out .= $this->oForm->render();


		if(	$this->oForm->oDataHandler->_isFullySubmitted() &&
			$this->oForm->oDataHandler->_allIsValid())
			{
			//is valid
			$formInfo = $this->oForm->oDataHandler->_getFlatFormDataManaged();

			if(!$formInfo['delete'])
				{
				$formInfo['mail'] = $this->prepareMail($formInfo);
				$this->sendMail($this->getFieldContent('email'), $formInfo['mail']);
				}
			$out  = '<p>'.$this->pi_list_linkSingle($this->pi_getLL('back','Back'),0).'</p>';
			$out .= $this->updateDB($formInfo);
			$out .= '<pre style="font-family: courier;">'.$formInfo['mail'].'</pre>';
			}


		return $out;
		}



	/**
	 * Returns a single table row for list view
	 *
	 * @param	integer		$c: Counter for odd / even behavior
	 * @return	A HTML table row
	 */
	function pi_list_row($c)
		{
		$editPanel = $this->pi_getEditPanel();
		if ($editPanel)	$editPanel='<TD>'.$editPanel.'</TD>';

		return '<tr'.($c%2 ? $this->pi_classParam('listrow-odd') : '').'>
				<td><p>'.$this->getFieldContent('uid-link').'</p></td>
				<td valign="top"><p>'.$this->getFieldContent('name').'</p></td>
				<td valign="top"><p>'.$this->getFieldContent('movie').'</p></td>
				<td valign="top"><p>'.$this->getFieldContent('timedate').'</p></td>
			</tr>';
		}



	/**
	 * Returns a table row with column names of the table
	 *
	 * @return	A HTML table row
	 */
	function pi_list_header()
		{
		return '<tr'.$this->pi_classParam('listrow-header').'>
				<td><p>'.$this->getFieldHeader_sortLink('uid').'</p></td>
				<td><p>'.$this->getFieldHeader_sortLink('name').'</p></td>
				<td><p>'.$this->getFieldHeader_sortLink('movie').'</p></td>
				<td nowrap><p>'.$this->getFieldHeader('timedate').'</p></td>
			</tr>';
		}



	/**
	 * Returns the content of a given field
	 *
	 * @param	string		$fN: name of table field
	 * @return	Value of the field
	 */
	function getFieldContent($fN)
		{
		switch($fN)
			{
			case 'uid-link':
				return $this->pi_list_linkSingle($this->internal['currentRow']['uid'],$this->internal['currentRow']['uid'], 0);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			case 'name':
				/*
				return $this->cObj->typoLink($this->internal['currentRow']['name'],
											 array("parameter" => $this->internal['currentRow']['email'])
												);
				*/
				return '<a href="mailto:'.$this->internal['currentRow']['email'].'">'.$this->internal['currentRow']['name'].'</a>';
			break;
			case 'timedate':
				return strftime("%A, %d.%m.%y - %H:%M Uhr", $this->internal['currentRow']['timedate']);
			break;
			case 'note':
				$note=explode("|", $this->internal['currentRow']['note']);
				return implode("<br />", $note);
			break;

			default:
				return $this->internal['currentRow'][$fN];
			break;
			}
		}




	/**
	 * Returns the label for a fieldname from local language array
	 *
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldHeader($fN)
		{
		switch($fN)
			{
			default:
				return $this->pi_getLL('listFieldHeader_'.$fN,'['.$fN.']');
			break;
			}
		}



	/**
	 * Returns a sorting link for a column header
	 *
	 * @param	string		$fN: Fieldname
	 * @return	The fieldlabel wrapped in link that contains sorting vars
	 */
	function getFieldHeader_sortLink($fN)
		{

		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array('sort'=>$fN.':'.($this->internal['descFlag']?0:1)));
		}




	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$to: ...
	 * @param	[type]		$bcc: ...
	 * @param	[type]		$subject: ...
	 * @param	[type]		$order_number: ...
	 * @param	[type]		$dealer: ...
	 * @return	[type]		...
	 */
	 function prepareMail($formInfo)
	 	{
		$mail = $this->cObj->fileResource($this->conf['email.']['template']);

		if($formInfo['free'])
			$mail = $GLOBALS['TSFE']->cObj->getSubpart($mail, "###EMAIL-SHORT###");
		else
			$mail = $GLOBALS['TSFE']->cObj->getSubpart($mail, "###EMAIL-FULL###");

		$mail = $this->cObj->substituteMarker($mail, '###MOVIE_TITLE###', 	$this->internal['currentRow']['movie']);
		$mail = $this->cObj->substituteMarker($mail, '###DATE###',			strftime("%A, %d.%m.%y - %H:%M Uhr", $this->internal['currentRow']['timedate']));
		$mail = $this->cObj->substituteMarker($mail, '###AUDIENCE###',		$formInfo['cinema']);
		$mail = $this->cObj->substituteMarker($mail, '###ROW###',			$formInfo['row']);
		$mail = $this->cObj->substituteMarker($mail, '###SEAT###',			$formInfo['seats']);
		$mail = $this->cObj->substituteMarker($mail, '###CODE###',			$formInfo['resNr']);
		$mail = $this->cObj->substituteMarker($mail, '###SPECIAL_NOTE###',	$formInfo['note']);

		return $mail;
	 	}



	function updateDB($formInfo)
		{
		$where = "uid = '".$this->internal['currentRow']['uid']."'";
		$fields_values = array(	"hidden"	=> 1,
								"deleted"	=> $formInfo['delete'],
								"cruser_id" => $GLOBALS['TSFE']->fe_user->user['uid'],
								"sentmail"  => $formInfo['mail'],
								"tstamp"	=> time(),
								"note"		=> $formInfo['note'],
								);

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->internal['currentTable'],$where,$fields_values);
		if(!$res)
			{
			$content .= "Datensatz kann nicht aktualisiert werden!";
			}
		else
			{
			$content = "Datensatz ".$this->getFieldContent['uid']." ge&auml;ndert!"."<br>";
			}

		return $content;
		}


		/**
		 * Sends an Email to recipients using php mail() throug TYPO3 wrapper function
		 *
		 * @param string $email email address, comma-separated. See php mail()
		 * @param string Mailtext
		 * @return void
		 */
	function sendMail($email, $mailBody)
		{
		t3lib_div::plainMailEncoded(
		            $email,
		            $this->conf['email.']['subject'],
		            $mailBody,
		            'FROM: '.$this->conf['email.']['fromName'].'<'.$this->conf['email.']['from'].'>',
					'quoted-printable'
		            );       //End send

		}




	}	/* END of CLASS */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi2/class.tmd_cinema_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi2/class.tmd_cinema_pi2.php']);
}

?>