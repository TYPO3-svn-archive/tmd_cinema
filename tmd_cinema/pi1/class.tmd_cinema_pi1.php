<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 Christian Tauscher <cms@media-distillery.de>
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

require_once(t3lib_extMgm::extPath('tmd_movie').'pi1/class.tx_tmdmovie.php');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_formidableapi);


/**
 * Plugin 'Cinema Program' for the 'tmd_cinema' extension.
 *
 * @author	Christian Tauscher <cms@media-distillery.de>
 * @package	TYPO3
 * @subpackage	tmd_cinema
 */
class tx_tmdcinema_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_tmdcinema_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tmd_cinema_pi1.php';	// Path to this script relative to the extension dir.
	var $uploadPath 	= 'uploads/tx_tmdmovie/';
	var $extKey        = 'tmd_cinema';	// The extension key.
	var $pi_checkCHash = true;



		/**
		 * Main method of your PlugIn
		 *
		 * @param	string		$content: The content of the PlugIn
		 * @param	array		$conf: The PlugIn Configuration
		 * @return	The content that should be displayed on the website
		 */
	function main($content,$conf) {
		$this->conf = $conf;			// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();				// Loading the LOCAL_LANG values

		$this->initFF();				// FlexForm Werte

		if($this->conf['DEBUG'] == 1 && strlen($this->conf['DEBUG.']['day']) > 1)  {
			list($d, $m, $y) = explode('-', $this->conf['DEBUG.']['day']);
			$this->theDay = mktime(0,0,0,$m,$d,$y);
		}



		$this->film = t3lib_div::makeInstance("tx_tmdmovie");

			/* Todo: gegen FF ersetzten
			 *
			 * inkl Recursion die PIDs als Liste ermitteln.
			 * Da gibt es eine Funktion...
			 */
		if (strstr($this->cObj->currentRecord,'tt_content')) {
			$this->conf['pidList'] = $this->cObj->data['pages'];
			$this->conf['recursive'] = $this->cObj->data['recursive'];
		}

#debug($this->conf[$this->templateNameTS]);
#debug($this->ff['mode']);

		switch($this->ff['mode']) {

			case 'singleView':
				$content = $this->singleView();
			break;

			case 'bookingView':
				$content = $this->booking();
			break;

			case 'tipAFriendView':
				$this->internal['currentTable'] = 'tx_tmdcinema_program';
				$this->internal['currentRow'] = $this->pi_getRecord('tx_tmdcinema_program',$this->piVars['showUid']);
				$content = $this->tipaFriendView();
			break;
			
			case 'trailerView':
				$content = $this->trailerView($this->ff['previewMin'], $this->ff['previewMax']);
			break;
			
			default:
			 	$content = $this->program($this->ff['previewMin'], $this->ff['previewMax']);
			break;
			
		}

#debug($this->ff['mode']);		
	return $this->pi_wrapInBaseClass($content);
	}






		/**
		 * Plätze Reservieren
		 */
	function booking() {
	
			# Daten vorbereiten
		if($this->conf['cryptTime'] == 1) { # verschlüsseltes entschlüsseln
			list($this->piVars['res'], $this->piVars['uid'], $this->piVars['cinema']) = explode("-", $this->decrypt($this->piVars['crypt']));
		} else {
			list($this->piVars['res'], $this->piVars['uid'], $this->piVars['cinema']) = explode("-", $this->piVars['show']);
		}

#debug($this->piVars);
		
		// Spammern das Handwerk legen
		// Res Limit berücksichtigen und das Kino auch.
		if(		 time()-$this->conf['resLimit'] > $this->piVars['res'] 
				|| !in_array($this->piVars['cinema'], explode(",", $this->conf['cinema']))
				) {
			return $this->substituteMarkers('ERROR_VIEW');
		}
		
		
		$this->internal['currentRow']['movie'] = $this->piVars['uid'];

 		$this->oForm = t3lib_div::makeInstance("tx_ameosformidable");
		$this->oForm->init($this, t3lib_extmgm::extPath($this->extKey) . "pi1/form/booking_form.xml");
		$out .= $this->oForm->render();
		
		$out .= $this->substituteMarkers();
		
		return $out;
	}




	/**
	 * Returns the detail View
	 *
	 * @return	the view
	 */
	function singleView() {

#		debug($this->piVars['showUid'], "showUid");


		// Spammern das Handwerk legen
		// Res Limit berücksichtigen und das Kino auch.
#		$this->internal['currentTable'] = 'tx_tmdcinema_program';
		$this->internal['currentRow'] = $this->pi_getRecord('tx_tmdcinema_program', $this->piVars['showUid']);

		if(	$this->internal['currentRow']['date'] > 0 && (
				7*24*60*60-$this->conf['resLimit'] < time()-$this->internal['currentRow']['date']  ||
				!in_array($this->internal['currentRow']['cinema'], explode(",", $this->ff['cinema']))   )
			) {
			$content = $this->pi_getLL("noValidPRG", "_noValidPRG_");
		} else {
			$content = $this->substituteMarkers();
		}

		return $content;
	}




		/**
		 * Zeit Große Übersicht
		 *
		 * @param string	Welche Art Programm
		 * @param int Startwoche, 0 = aktuelle Woche, 1=nächste Woche, 2=übernächste u.s.w.
		 * @param int Wieviele Wochen?
		 */
	function program($startWeek, $nextWeeks=1) {

		
		if($this->conf['DEBUG'] == 1 && strlen($this->conf['DEBUG.']['day']) > 1)  {
			$now = $this->theDay;
		} else {
			$now = mktime();
		}

#debug(strftime("%d.%m.%y", $now));
		switch(strftime("%u", $now)) { # %u = Tag der Woche 1= Montag
			case 1: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-4, date("Y", $now)); break; # Mo
			case 2: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-5, date("Y", $now)); break; # DI
			case 3: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-6, date("Y", $now)); break; # MI
			case 4: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-0, date("Y", $now)); break; # DO
			case 5: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-1, date("Y", $now)); break; # FR
			case 6: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-2, date("Y", $now)); break; # SA
			case 7: $wStart = mktime(0, 0, 0, date("m", $now), date("d", $now)-3, date("Y", $now)); break; # SO
		}

			# n-te Woche in die Zukunft
		$wStart = mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7*$startWeek, date("Y", $wStart));
		$wEnd   = mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7*$nextWeeks, date("Y", $wStart))-1;

			# Programm vorzeitig wechseln
		if($now >= ($wEnd - $this->conf['switchPrgBevore']*60*60) ) {
			$wStart = mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7, date("Y", $wStart));
			$wEnd   = mktime(0, 0, 0, date("m", $wEnd  ), date("d", $wEnd  )+7, date("Y", $wEnd  ));
		}


#debug(array("start" => strftime("%d.%m.%y %H:%M:%S", $wStart), "stop" => strftime("%d.%m.%y %H:%M:%S", $wEnd)));


		
		
		if($this->ff['showUndefinedStart'] == 0) {
			$whereClause = 'AND tx_tmdcinema_program.date >= '.$wStart.' AND tx_tmdcinema_program.date <= '.($wEnd);
		} else { # Demnächst, ohne definiertem Start auch
			$whereClause = ' AND ( tx_tmdcinema_program.date = 0 OR (tx_tmdcinema_program.date >= '.$wStart.' AND tx_tmdcinema_program.date < '.$wEnd.'))';
		}
		
		$whereClause .= " AND tx_tmdcinema_program.cinema IN (".$this->ff['cinema'].")";

		if($this->ff['special']) {
			$whereClause .= " AND tx_tmdcinema_program.showtype IN (".$this->ff['special'].")";
		}
		
		if(strlen($this->conf['additionalWhere']) > 0) { # z.B. Kinderkino
			$whereClause .= ' AND '.$this->conf['additionalWhere'];
		}

		$whereClause .= ' AND tx_tmdcinema_program.movie = tx_tmdmovie_movie.uid AND tx_tmdmovie_movie.hidden = 0 AND tx_tmdmovie_movie.deleted = 0';
		$whereClause .= $this->cObj->enableFields('tx_tmdcinema_program');

		$sortBy = "tx_tmdcinema_program.cinema, tx_tmdcinema_program.date, sorting ASC";





#debug($whereClause);

			// Make listing query, pass query to SQL database:
			# ACHTUNG Reichen folge der tabellen wegen UID!!
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT *', 'tx_tmdmovie_movie, tx_tmdcinema_program', "1=1 ".$whereClause, $groupBy, $sortBy);




			// Make list table rows
		$noDate=array();
		$cinemaOrder = explode(",", $this->ff['cinema']);

		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tempTime = $this->internal['currentRow']['date'];
			list($day, $month, $year) = explode(",", strftime("%e,%m,%Y", $tempTime));
			$this->internal['currentRow']['date'] = mktime(0,0,0,  $month,  $day, $year);
			$all[] = $this->internal['currentRow'];
		}
#debug($all);


		if (count($all)  == 0) {  /* Es gibt noch kein Programm */
			if($this->ff['previewNotice']) {
				$out = sprintf($this->ff['previewNotice'], strftime($this->conf['timeFormat'], $wStart));
				$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['mode'].'.']['PREVIEW_NOTE']);
			} else {
				$out = sprintf($this->conf['previewNotice'], strftime($this->conf['timeFormat'], $wStart));
			}

			return $out;
		}



		foreach($cinemaOrder as $cinema) {
			foreach($all as $this->internal['currentRow']) {

				if(! ($this->conf['hideExpiredProgram'] && $this->checkExpiredProgram())) {

					if($this->internal['currentRow']['cinema'] == $cinema) { # Sammelüberschrift

						if($this->internal['currentRow']['date'] > 0) {
							$date = $this->internal['currentRow']['date'];
	
							if($date != $lastDate) { 
								$this->internal['currentRow']['ifDateChanges'] = sprintf($this->pi_getLL('programmBeginning'), strftime($this->conf['timeFormat'], $date));
								$lastDate = $date;
							} 
							
							$items[] = '<a name="'.$this->prefixId."-".$this->internal['currentRow']['uid'].'"></a>'.$this->substituteMarkers();
						} else {
							$this->internal['currentRow']['ifDateChanges'] = "Demnächst";
						
							$noDate[] = '<a name="'.$this->prefixId."-".$this->internal['currentRow']['uid'].'"></a>'.$this->substituteMarkers();
						}
					}
				} # END checkExpiredProgram
			}

			if(is_array($items)) {
				if(count($items) > 0) {
					$out .= implode(chr(10), $items);
				}
			}
			if(is_array($noDate)) {
				if(count($noDate) > 0) {
					$out .= implode(chr(10), $noDate);
				}
			}
	
			unset($items);
			unset($noDate);
		} # END foreach cinema
		

		if($type == 'trailerView') { # wrap für trailerListe
			if(strlen($out) > 1) {
				$out = '<table class="trailerList">'.$prg.'</table>';
			} else {
				$out = "Keine Trailer vorhanden";
			}
		}
		return $out;
	}







		/**
		 * Trailer Übersicht
		 *
		 * @param string	Welche Art Programm
		 * @param int Startwoche, 0 = aktuelle Woche, 1=nächste Woche, 2=übernächste u.s.w.
		 * @param int Wieviele Wochen?
		 */
	function trailerView($startWeek, $nextWeeks=1) {

		
		if($this->conf['DEBUG'] == 1 && strlen($this->conf['DEBUG.']['day']) > 1)  {
			$now = $this->theDay;
		} else {
			$now = mktime();
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

			# n-te Woche in die Zukunft
		$wStart = mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7*$startWeek, date("Y", $wStart));
		$wEnd   = mktime(0, 0, 0, date("m", $wStart), date("d", $wStart)+7*$nextWeeks, date("Y", $wStart))-1;


		if($startWeek == 0) {
			$whereClause   .= 'AND tx_tmdcinema_program.date >= '.$wStart.' AND tx_tmdcinema_program.date <= '.($wEnd);
		} else {
			$whereClause .= ' AND (tx_tmdcinema_program.date = 0 OR (tx_tmdcinema_program.date >= '.$wStart.' AND tx_tmdcinema_program.date < '.$wEnd.'))';
		}
		$whereClause .= " AND tx_tmdcinema_program.cinema IN (".$this->ff['cinema'].")";

		if($this->ff['special']) {
			$whereClause .= " AND tx_tmdcinema_program.showtype IN (".$this->ff['special'].")";
		}


		if(strlen($this->conf['additionalWhere']) > 0) {
			$whereClause .= ' AND '.$this->conf['additionalWhere'];
		}
		$whereClause .= ' AND tx_tmdcinema_program.movie = tx_tmdmovie_movie.uid AND tx_tmdmovie_movie.hidden = 0 AND tx_tmdmovie_movie.deleted = 0';
		$whereClause .= $this->cObj->enableFields('tx_tmdcinema_program');

		$sortBy = "tx_tmdcinema_program.cinema, tx_tmdcinema_program.date, sorting ASC";





#debug($whereClause);

			// Make listing query, pass query to SQL database:
			# ACHTUNG Reichen folge der tabellen wegen UID!!
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT *', '  tx_tmdmovie_movie,tx_tmdcinema_program', "1=1 ".$whereClause, $groupBy, $sortBy);




			// Make list table rows
		$noDate=array();
		$cinemaOrder = explode(",", $this->ff['cinema']);

		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tempTime = $this->internal['currentRow']['date'];
			list($day, $month, $year) = explode(",", strftime("%e,%m,%Y", $tempTime));
			$this->internal['currentRow']['date'] = mktime(0,0,0,  $month,  $day, $year);
			$all[] = $this->internal['currentRow'];
		}
#debug($all);


		if (count($all)  == 0) {  /* Es gibt noch kein Programm */
			if($this->ff['previewNotice']) {
				$out = sprintf($this->ff['previewNotice'], strftime($this->conf['timeFormat'], $wStart));
				$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['mode'].'.']['PREVIEW_NOTE']);
			} 

			return $out;
		}



		foreach($all as $this->internal['currentRow']) {
			$items[ $this->internal['currentRow']['title'] ] = $this->substituteMarkers();
		}


		if(is_array($items)) {
			if(count($items) > 0) {
				$out = implode(chr(10), $items);
			}
		}

		
		

		if(strlen($out) > 1) {
			$out = '<table class="trailerList">'.$out.'</table>';
		} else {
			$out = "Keine Trailer vorhanden";
		}

		return $out;
	}

	
	
	
	
	
	
	/**
	 * Returns the detail View
	 *
	 * @return	the view
	 */
	function tipaFriendView() {
		// Spammern das Handwerk legen
		// Res Limit berücksichtigen und das Kino auch.
		if(	$this->internal['currentRow']['date'] > 0 && (
				7*24*60*60-$this->conf['resLimit'] < time()-$this->internal['currentRow']['date']  ||
				!in_array($this->internal['currentRow']['cinema'], explode(",", $this->ff['cinema']))   )
			) {
			$content = $this->pi_getLL("noValidPRG", "_noValidPRG_");
		} else {
			$GLOBALS['TSFE']->set_no_cache();

			if(isset($this->piVars['step1'])) {
				unset($this->piVars['step1']);
				$this->piVars['step'] = 1;
			}
			if(isset($this->piVars['step2'])) {
				unset($this->piVars['step2']);
				$this->piVars['step'] = 2;
			}
			if(isset($this->piVars['step3'])) {
				unset($this->piVars['step3']);
				$this->piVars['step'] = 3;
			}

			#debug($this->piVars);
			if($this->conf['DEBUG'] == 1) {
				$this->piVars['myName'] = "Christian";
				$this->piVars['myEMail'] = "crtausch@tmd.dynalias.net";
				$this->piVars['friendName']['1'] = "Freund 1";
				$this->piVars['friendMail']['1'] = "crtausch@tmd.dynalias.net";
				$this->piVars['message'] = "Deine Nachricht";
			}

				/* Validieren wenn abgeschickt */
			$markerArray['###ERROR###'] = '&nbsp;';
			if($this->piVars['step'] > 1) {
				list($this->piVars['step'], $markerArray['###ERROR###'])  = $this->validateStep1();
			}



			switch($this->piVars['step']) {
				case '1': # Formular
					$markerArray['###ACTION_URL###'] = $this->pi_list_linkSingle($str,$uid,FALSE,array('step' => '', 'showUid'=>$this->piVars[showUid]),$urlOnly=TRUE,$altPageId=0);

					if($this->piVars['timestamp']) {
						$markerArray['###TIMESTAMP###'] = $this->piVars['timestamp'];
					} else {
						$markerArray['###TIMESTAMP###'] = time();
						$GLOBALS["TSFE"]->fe_user->setKey("ses", $this->prefixId."[".$markerArray['###TIMESTAMP###']."]", "NEW");
					}

					#http://www.rustylime.com/show_article.php?id=338
					if($this->conf['honeyPot'] == true) {		#document . the name of the form . the name of the field to shift focus to
						$markerArray['###HONEYPOT###'] = '<input id="pritt" type="text" size=30 name="tx_tmdcinema_pi1[pritt]" onfocus="document.tx_tmdcinema_pi1_tipafriend.startfield.focus();"  value="">';
						if($this->conf['DEBUG.']['forceHoneyPot'] == 1)
							$markerArray['###HONEYPOT###'] = '<input id="pritt" type="text" size=30 name="tx_tmdcinema_pi1[pritt]" value="Ich bin Spammer">';
					} else {
						$markerArray['###HONEYPOT###'] = '';
					}

					if (t3lib_extMgm::isLoaded('captcha') && $this->conf['captcha'] == true)	{
						$markerArray['###CAPTCHA###']  = '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="" />';
						$markerArray['###CAPTCHA###'] .= '<input type="text" size=30 name="tx_tmdcinema_pi1[captchaResponse]" value="">';
					} else {
						$markerArray['###CAPTCHA###'] = '';
					}

					$markerArray['###MYNAME###']  = $this->piVars['myName']  ? htmlspecialchars($this->piVars['myName'])  : '';
					$markerArray['###MYEMAIL###'] = $this->piVars['myEMail'] ? htmlspecialchars($this->piVars['myEMail']) : '';

					for($n = 1; $n<=$this->conf['friendCount']; $n++) {
						$markerArray['###FRIENDNAME_'.$n.'###'] = ($this->piVars['friendName'][$n]) ? htmlspecialchars($this->piVars['friendName'][$n]) : '';
						$markerArray['###FRIENDMAIL_'.$n.'###'] = ($this->piVars['friendMail'][$n]) ? htmlspecialchars($this->piVars['friendMail'][$n]) : '';
					}

					$markerArray['###MESSAGE###'] = ($this->piVars['message']) ? htmlspecialchars($this->piVars['message']) : '';

					$markerArray['###ERROR###'] = implode("<br />", (array)$markerArray['###ERROR###']);
					$markerArray['###ERROR###'] = $this->cObj->wrap($markerArray['###ERROR###'], '<div class="error">|</div>');

					$content = $this->substituteMarkers("TIPAFRIEND_FORM", $markerArray);
				break;

				case '2': # Vorschau
					$formStatus = $GLOBALS["TSFE"]->fe_user->getKey("ses", $this->prefixId."[".$this->piVars['timestamp']."]");

					if($formStatus == 'SENT') { # schon mal abgeschickt worden
						$content = $this->pi_linkToPage($this->pi_getLL("backToStart", "_backToStart_"), $this->conf['prgPid']);
						break;
					} else { #OK
						$GLOBALS["TSFE"]->fe_user->setKey("ses", $this->prefixId."[".$this->piVars['timestamp']."]", "OK");
					}

					$markerArray['###ACTION_URL###'] = $this->pi_list_linkSingle($str,$uid,FALSE,array('step' => '', 'showUid'=>$this->piVars[showUid]),$urlOnly=TRUE,$altPageId=0);
					$markerArray['###TIPDATE###'] = $this->piVars['tipDate'];
					$markerArray['###MYNAME###'] = $this->piVars['myName'];
					$markerArray['###MYEMAIL###'] = $this->piVars['myEMail'];
					$n=0;
					foreach($this->piVars['friendName'] as $key => $name) {
						$n++;
						$markerArray['###FRIENDNAME_'.$n.'###'] .= $name;
						$markerArray['###FRIENDMAIL_'.$n.'###'] .= $this->piVars['friendMail'][$key];
					}
					$markerArray['###MESSAGE###'] = nl2br($this->piVars['message']);

					list($date, $movie, $cinema) = explode("-", $this->decrypt($this->piVars['tipDate']));
					$markerArray['###TIPDATE_DECRYPT###'] = strftime($this->conf['dateString'], $date);
					$markerArray['###TIPDATE###'] = $this->piVars['tipDate'];

					$content = $this->substituteMarkers("TIPAFRIEND_PREVIEW", $markerArray);
				break;

				case '3': # Absenden
					$formStatus = $GLOBALS["TSFE"]->fe_user->getKey("ses", $this->prefixId."[".$this->piVars['timestamp']."]");
					if($formStatus === 'NEW' || $formStatus === 'OK') {
						$GLOBALS["TSFE"]->fe_user->setKey("ses", $this->prefixId."[".$this->piVars['timestamp']."]", "SENT");
					} else {
						$content  = $this->pi_getLL("err_alreadySent", "_err_alreadySent_")."<br />";
						$content .= $this->pi_linkToPage($this->pi_getLL("backToStart", "_backToStart_"), $this->conf['prgPid']);
						break;
					}


					$n = 0;
					foreach($this->piVars['friendName'] as $key => $name) {
						$recipient[$n]['name']  = htmlspecialchars($name);
						$recipient[$n]['EMail'] = htmlspecialchars($this->piVars['friendMail'][$key]);
						$n++;
					}

#					debug($this->piVars['friendName']);
					if($this->conf['sendMeMailToo']) {
						$recipient[$n]['name']  = htmlspecialchars($this->piVars['myName']);
						$recipient[$n]['EMail'] = htmlspecialchars($this->piVars['myEMail']);
					}

					foreach($recipient as $key => $data){
						if ($data['EMail'] && t3lib_div::validEmail($data['EMail'])) {
							list($date, $movie, $cinema) 			= explode("-", $this->decrypt($this->piVars['tipDate']));
							$markerArray['###MYNAME###'] 			= htmlspecialchars($this->piVars['myName']);
							$markerArray['###MYEMAIL###'] 			= htmlspecialchars($this->piVars['myEMail']);
							$markerArray['###MESSAGE###'] 			= nl2br(htmlspecialchars($this->piVars['message']));
							$markerArray['###TIPDATE_DECRYPT###'] 	= strftime($this->conf['dateString'], $date);
							$markerArray['###BASEURL###']			= $this->conf['baseURL'];

							$subject = sprintf($this->conf['subject'], $markerArray['###MYNAME###']);
							$email['html'] = $this->substituteMarkers("TIPAFRIEND_HTMLEMAIL", $markerArray);
							$email['txt']  = html_entity_decode($this->substituteMarkers("TIPAFRIEND_TXTEMAIL",  $markerArray),  ENT_COMPAT, "utf-8" );

							$this->sendTip($markerArray['###MYEMAIL###'], $markerArray['###MYNAME###'], $data['name'], $data['EMail'], $subject, $email);

							if($this->conf['spamLogPID'] > 0 && $this->conf['logMail'] == 1) { # log every entry
								$this->writeTAFLogfile(0,1); # anonym
							}

						}
					}

					$content = $this->substituteMarkers("TIPAFRIEND_SENT", $markerArray);
				break;

				case 'timelimit':
					$content = $this->getLL('err_timeLimit', "_err_timeLimit_");
				break;

				case 'honeyPot':
					$content = $this->pi_getLL("err_honeyPot", "_err_honeyPot_");

					if($this->conf['spamLogPid'] > 0 && $this->conf['logSpam'] == 1) {
						$this->writeTAFLogfile(1,0); # log everything
					}
				default:
					$content = $content;
				break;
			}
		}

		return $content;
	}



		/**
		 * Writes Logfileentry to DB
		 * part of Tip A Friend
		 *
		 * @param $spam		bool	spam flag
		 * @param $anonym	bool	no names, email
		 * @return void
		 */
	function writeTAFLogfile($spam, $anonym=1) {
		list($date, $movie, $cinema) = explode("-", $this->decrypt($this->piVars['tipDate']));
		$table = 'tx_tmdcinema_spamlog';
		$this->conf['wrap.'][$this->ff['mode'].'.']['MOVIE_TITLE'] = '|'; # nicht schön.....
		$this->conf['wrap.'][$this->ff['mode'].'.']['PRG_THEATRE'] = '|';
		$fields_values = array(
    		'pid' 		=> $this->conf['spamLog'],
		    'tstamp'	=> time(),
		    'crdate'	=> time(),
		    'ip'		=> getenv(REMOTE_ADDR),
		    'sender'	=> $anonym ? '' : htmlspecialchars($this->piVars['myName']).' '.htmlspecialchars($this->piVars['myEMail']),
		    'recipient'	=> $anonym ? '' : htmlspecialchars(implode(', ', $this->piVars['friendMail'])),
		    'msg'		=> $anonym ? '' : htmlspecialchars($this->piVars['message']),
			'spam'		=> $spam,
			'showdata'	=> $this->getFieldContent('movie_title').' - '.strftime("%d.%m.%y %H:%M", $date).' - '.$this->getFieldContent('cinema'),
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$fields_values,$no_quote_fields=FALSE);
	}



	function validateStep1() {
		$setStepTo = $this->piVars['step'];

		# Captcha test
		if (t3lib_extMgm::isLoaded('captcha') && $this->conf['captcha'] == true)	{
			session_start();
			$captchaStr = $_SESSION['tx_captcha_string'];
		#	$_SESSION['tx_captcha_string'] = ''; # auskommentiert weil captcha über einen weiteren Schritt gerettet werden muss.
		} else {
			$captchaStr = -1;
		}

		if ($captchaStr===-1 || $this->piVars['captchaResponse']===$captchaStr
			) {
			# OK
		} else {
			$setStepTo  = 1; /* Formular nochmal ausfüllen! */
			$error[] = $this->pi_getLL("err_captcha", "_err_captcha_");
		}

		if($this->piVars['step'] == 3) { #Session löschen? Nötig? Wird oben gemacht
			$_SESSION['tx_captcha_string'] = '';
		}

			# Auf den Leim gegangen, elender Spammer
		if($this->conf['honeyPot'] == true && strlen($this->piVars['pritt']) >1 ) {
			$this->piVars['step'] = 'honeyPot'; /* Formular nochmal ausfüllen! */
		}

			# fehlerhaft oder sonstwas falsches
		if(isset($this->piVars['tipDate'])) {
			list($theTimeDate, $theMovie, $theCinema) = explode("-", $this->decrypt($this->piVars['tipDate']));

			if(!t3lib_div::inList($this->conf['cinema'], $theCinema)) { # Kino ist nicht korrekt
				$error[] = $this->pi_getLL('err_wrongCinema', "_err_wrongCinema_"); /* Formular nochmal ausfüllen! */
			}

			if(time() > ($theTimeDate - $this->conf['resLimit']*60*60)) { # Resevierungsschluß
				$error[] = $this->pi_getLL('err_timeLimit', "_err_timeLimit_"); /* Formular nochmal ausfüllen! */
			}
		} else { # No time chosen
			$error[] = $this->pi_getLL("err_chooseShow", "_err_chooseShow_");
		}



		if($this->piVars['message']) {
			$this->piVars['message'] = t3lib_div::fixed_lgd_cs($this->piVars['message'], $this->conf['maxMsgLength'], '');
		}


		if(!$this->piVars['myName']) {
			$error[] = $this->pi_getLL("err_yourName", "_err_yourName_");
		}
		if(!t3lib_div::validEmail($this->piVars['myEMail'])) {
			$error[] = $this->pi_getLL("err_yourMail", "_err_yourMail_");
		}

		$n = 0;
		foreach($this->piVars['friendName'] as $name) {
			$n++;

			if(strlen($this->piVars['friendName'][$n]) < 1 && strlen($this->piVars['friendMail'][$n]) < 1) {
				$missCount++;
			}

			if($this->piVars['friendName'][$n] || $this->piVars['friendMail'][$n]) {
				if(strlen($this->piVars['friendName'][$n]) < 1) {
					$error[] = sprintf($this->pi_getLL("err_nameFriend", "Name %s fehlt!"), $n);
				}
				if(!t3lib_div::validEmail($this->piVars['friendMail'][$n])) {
					$error[] = sprintf($this->pi_getLL("err_mailFriend", "_err_mailFriend_"), $n);
				}
			}
		}

		if($missCount == $this->conf['friendCount']) {
			$error[] = $this->pi_getLL("err_missFriendMail", "_err_missFriendMail_");
		}

		$setStepTo = (count($error)) ? 1 : $this->piVars['step'];

		return array($setStepTo, $error);
	}






	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$tipData: ...
	 * @param	[type]		$url: ...
	 * @return	[type]		...
	 */
	function sendTip($senderEMail, $senderName, $recipientName, $recipientEMail, $subject, $msg)	{
		$headers[]='FROM: '.$senderName.' <'.$senderEMail.'>';

		$plain_message = trim(strip_tags($msg['txt']));

			// HTML
		$cls=t3lib_div::makeInstanceClassName('t3lib_htmlmail');

		if ($this->conf['sendHTML'] && class_exists($cls))	{	// If htmlmail lib is included, then generate a nice HTML-email
			$Typo3_htmlmail = t3lib_div::makeInstance('t3lib_htmlmail');

			$Typo3_htmlmail->start();
			$Typo3_htmlmail->useBase64();

			$Typo3_htmlmail->subject = $subject;
			$Typo3_htmlmail->from_email = $senderEMail;
			$Typo3_htmlmail->from_name = $senderName;
			$Typo3_htmlmail->replyto_email = $senderEMail;
			$Typo3_htmlmail->replyto_name = $senderName;
			$Typo3_htmlmail->organisation = '';
			$Typo3_htmlmail->priority = 3;

				// this will fail if the url is password protected!
			$Typo3_htmlmail->addPlain($plain_message);
			$Typo3_htmlmail->setHTML($Typo3_htmlmail->encodeMsg($msg['html']));

			$Typo3_htmlmail->setHeaders();
			$Typo3_htmlmail->setContent();
			($recipientName) ? $Typo3_htmlmail->setRecipient($recipientName.' <'.$recipientEMail.'>') : $Typo3_htmlmail->setRecipient($recipientEMail);

			$Typo3_htmlmail->sendtheMail();
		} else {
			$this->cObj->sendNotifyEmail($subject.chr(10).$plain_message, $recipientEMail, $cc, $senderEMail, $senderName, $senderEMail);
		}
	}




		/**
		 * Checks if there are still shows upcoming this week
		 * @return unknown_type
		 */
	function checkExpiredProgram() {
		if($this->getFieldContent('firstday_raw') > mktime() || $this->internal['currentRow']['date'] <= 0) { // Programm in der Zukunft
			return false;
		}

		$today= strftime("%w", time()); # 0 = Sonntag
		$table = $this->getFieldContent('program_raw');
		$lines = explode(chr(10), $table);

		foreach($lines as $line) {
			#   ($do,$fr,$sa,$so,$mo,$di,$mi)
			# Achtung: Zuweisung in umgekehrter Reihenfolge!!!
			$temp = explode('|', trim($line));
			$d[4] = $temp[0];
			$d[5] = $temp[1];
			$d[6] = $temp[2];
			$d[0] = $temp[3];
			$d[1] = $temp[4];
			$d[2] = $temp[5];
			$d[3] = $temp[6];

			$foundToday = false;
			$n=0;
			foreach($d as $day => $time) {
				if($day == $today) {
					$foundToday = true;
				}

				if($foundToday && $time) {
					return false;
				}

				$n++;
			}
		}


		return true; # Program Expired
	}






		/**
		 *	encrypts a given string to a HEX number
		 *	@param string string to be encrypted
		 * 	@return string encrypted String as HEX number
		 */
	function encrypt($string) {
		$hex="";
	    $length=strlen($string);
	    for ($i=0; $i<$length; $i++) {
		    $hex[] = dechex(ord(substr($string, $i, 1)));
		}
		$hex = implode("", $hex);

	    return $hex;
	}



		/**
		 *	decrypts a given string
		 *	@param string string encrypted
		 * 	@return string decrypted string
		 */
	function decrypt($string) {
		$dec="";
	    $length=strlen($string);
	    for ($i=0; $i<$length; $i+=2) {
		    $dec[] = chr(hexdec(substr($string, $i, 2)));
		}

		if(is_array($dec)) $dec = implode("", $dec);

	    return $dec;
	}



		/**
		 * Flex Form Parameter werden ausgelesen und initialisiert
		 *
		 * @todo TYPOscript parameter berücksichtigen und eventuell Überschreiben
		 */
	function initFF() {
			# FF Parsen
		$this->pi_initPIflexForm();


		/*
		<cinemaConfig>
				<cinema>
				<special>
				<previewMin>
				<previewMax>
		*/
		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cinema', 		'cinemaConfig');
		$val ? $this->ff['cinema'] = $val : $this->ff['cinema'] = $this->conf['cinema'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'special',		'cinemaConfig');
		$val ? $this->ff['special'] = $val : $this->ff['special'] = $this->conf['special'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'previewMin',	'cinemaConfig');
		$val ? $this->ff['previewMin'] = $val : $this->ff['previewMin'] = $this->conf['previewMin'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'previewMax', 	'cinemaConfig');
		$val ? $this->ff['previewMax'] = $val : $this->ff['previewMax'] = $this->conf['previewMax'];


		/*
		 <template>
				<mode>
				<templateFile>
				<previewNote>
		*/
		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mode',	 		'template');
		$val ? $this->ff['mode'] = $val : $this->ff['mode'] = $this->conf['mode'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateFile',	'template');
		$val ? $this->ff['templateFile'] = $val : $this->ff['templateFile'] = $this->conf['templateFile'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'previewNote',		'template');
		$val ? $this->ff['previewNote'] = $val : $this->ff['previewNote'] = $this->conf['previewNote'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disallowBooking',	'template');
		$val ? $this->ff['disallowBooking'] = $val : $this->ff['disallowBooking'] = $this->conf['disallowBooking'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showUndefinedStart',	'template');
		$val ? $this->ff['showUndefinedStart'] = $val : $this->ff['showUndefinedStart'] = $this->conf['showUndefinedStart'];
		
		
			# Template name für internes
			# basisname der Datei $this->ff['templateFile']
		$this->template = $this->cObj->fileResource($this->conf['templatePath'].$this->ff['templateFile']);
		$this->templateNameTS = substr($this->ff['templateFile'], 0, strrpos($this->ff['templateFile'], '.')) . '.';


		/*
		<imageLinks>
				<pageSingleView>
				<pageProgram>
				<pagePreview>
				<width>
				<clickEnlarge>
				<disallowBooking>
				<bookingPage>
		*/
		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pageSingleView',	'imageLinks');
		$val ? $this->ff['pageSingleView'] = $val : $this->ff['pageSingleView'] = $this->conf['pageSingleView'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pageProgram',		'imageLinks');
		$val ? $this->ff['pageProgram'] = $val : $this->ff['pageProgram'] = $this->conf['pageProgram'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pagePreview',		'imageLinks');
		$val ? $this->ff['pagePreview'] = $val : $this->ff['pagePreview'] = $this->conf['pagePreview'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pageTipAFriend', 	'imageLinks');
		$val ? $this->ff['pageTipAFriend'] = $val : $this->ff['pageTipAFriend'] = $this->conf['pageTipAFriend'];

		$val = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pageBooking',	 	'imageLinks');
		$val ? $this->ff['pageBooking'] = $val : $this->ff['pageBooking'] = $this->conf['pageBooking'];

		


			# TS für Bilder, unterschied IMAGE oder GIFBUILDER
			# Erst TS laden
		$w = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width',	'imageLinks');
		$h = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height',	'imageLinks');

		if($this->conf[$this->templateNameTS]['poster'] == 'IMAGE') {
			$this->ff['poster']	= 'IMAGE';
			$this->ff['poster.'] = $this->conf[$this->templateNameTS]['poster.'];

			if($w || $h) { # FF hat vorrang
				$this->ff['poster.']['file.']['width']  = $w;
				$this->ff['poster.']['file.']['height'] = $h;
			} else { # dann halt TS
				$this->ff['poster.'] = $this->conf[$this->templateNameTS]['poster.'];
			}

				# ClickEnlage
			if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'clickEnlarge',	'imageLinks')) {
				$this->ff['poster.']['imageLinkWrap'] = 1;
				$this->ff['poster.']['imageLinkWrap.'] = $this->conf['imageLinkWrap.'];
			}
		} elseif($this->conf[$this->templateNameTS]['poster.']['file'] == 'GIFBUILDER') {
			$this->ff['poster']	= 'GIFBUILDER';
			$this->ff['poster.'] = $this->conf[$this->templateNameTS]['poster.'];

			if($w || $h) { # FF hat vorrang
				$this->ff['poster.']['file.']['10.']['file.']['width']  = $w;
				$this->ff['poster.']['file.']['10.']['file.']['height'] = $h;
			} else { # dann halt TS
				$this->ff['poster.'] = $this->conf[$this->templateNameTS]['poster.'];
			}

			# Keine Klickvergrößerung bei GIFUILDER !!!!
		}


	
#		debug($this->ff);
	}








	function substituteMarkers($subPart='PROGRAMM_VIEW', $markerArray="") {

		$template = $GLOBALS['TSFE']->cObj->getSubpart($this->template, "###$subPart###");
		if(strlen($template) < 1) {
			debug("Kein Template!! ###".$subPart."###", $this->ff['templateFile']);
			return;
		}

			// oben weil ein array zurück kommt
#		$markerArray = $this->getFieldContent('movie_media');

		$markerArray['###MOVIE_TITLE###'] 			= $this->getFieldContent('movie_title');
		$markerArray['###MOVIE_TITLE_ORIGINAL###']  = $this->getFieldContent('movie_originaltitle');
		$markerArray['###MOVIE_TITLE_SHORT###']		= $this->getFieldContent('movie_subtitle');
		$markerArray['###MOVIE_TITLE_SHORT_FIRST###'] = $this->getFieldContent('movie_subtitle_first');

		$markerArray['###MOVIE_POSTER###'] 			= $this->getFieldContent('movie_poster');
		$markerArray['###MOVIE_RATING###'] 			= $this->getFieldContent('movie_fsk');
		$markerArray['###MOVIE_RATINGTOOLTIP###'] 	= $this->getFieldContent('movie_fskTooltip');
		$markerArray['###MOVIE_TIME###'] 			= $this->getFieldContent('movie_time');
		$markerArray['###MOVIE_RELEASEDATE###']		= $this->getFieldContent('movie_start');
		$markerArray['###MOVIE_FORMAT###'] 			= $this->getFieldContent('movie_format');
		$markerArray['###MOVIE_SOUND###'] 			= $this->getFieldContent('movie_sound');
		$markerArray['###MOVIE_DISTRIBUTOR###'] 	= $this->getFieldContent('movie_distributor');
		$markerArray['###MOVIE_WWW###'] 			= $this->getFieldContent('movie_www');
		$markerArray['###MOVIE_TRAILER###'] 		= $this->getFieldContent('movie_trailer');
		$markerArray['###MOVIE_DESCRIPTION###'] 	= $this->getFieldContent('movie_summary');
		$markerArray['###MOVIE_DESCRIPTION_SHORT###'] =	$this->getFieldContent('movie_summary_short');
		$markerArray['###MOVIE_EXTRAPICS###'] 		= $this->getFieldContent('movie_mediafile');
		$markerArray['###MOVIE_SUBTITLE###'] 		= $this->getFieldContent('movie_subtitle');
		$markerArray['###MOVIE_IMAGE_LINK###'] 		= $this->getFieldContent('movie_imageLink');
		$markerArray['###MOVIE_FBW###'] 			= $this->getFieldContent('movie_fbw');
		$markerArray['###MOVIE_TRAILER_SINGLE###']	= $this->getFieldContent('movie_trailer_single');
		$markerArray['###MOVIE_TRAILER_LIST###']	= $this->getFieldContent('movie_trailer_list');

		$markerArray['###MEDIA_1###'] 				= $this->getFieldContent('movie_media-1');
		$markerArray['###MEDIA_2###'] 				= $this->getFieldContent('movie_media-2');
		$markerArray['###MEDIA_3###'] 				= $this->getFieldContent('movie_media-3');
		$markerArray['###MEDIA_4###'] 				= $this->getFieldContent('movie_media-4');
		$markerArray['###MEDIA_5###'] 				= $this->getFieldContent('movie_media-5');


		$markerArray['###MOVIE_DIRECTOR###'] 		= $this->getFieldContent('movie_director');
		$markerArray['###MOVIE_PRODUCER###'] 		= $this->getFieldContent('movie_producer');
		$markerArray['###MOVIE_ACTOR###'] 			= $this->getFieldContent('movie_actor');
		$markerArray['###MOVIE_GENRE###'] 			= $this->getFieldContent('movie_genre');

		$markerArray['###VERSION3D###'] 			= $this->getFieldContent('version3d');
		$markerArray['###IFDATECHANGES###'] 		= $this->getFieldContent('ifDateChanges');
		
		$markerArray['###PRG_WEEK###'] 				= $this->getFieldContent('week');
		$markerArray['###PRG_SHOWTYPE###'] 			= $this->getFieldContent('showtype');
		$markerArray['###PRG_TIMETABLE###'] 		= $this->getFieldContent('program');
		$markerArray['###PRG_INFO###'] 				= $this->getFieldContent('info');
		$markerArray['###PRG_INFO2###'] 			= $this->getFieldContent('info2');
		$markerArray['###PRG_THEATRE###'] 			= $this->getFieldContent('cinema');
		$markerArray['###PRG_FIRSTDAY###'] 			= $this->getFieldContent('firstday');
		$markerArray['###PRG_STARTDAY###'] 			= $this->getFieldContent('date');

		$markerArray['###DEBUG_PGRDATE###'] 			= $this->getFieldContent('debug_firstday');

			// ACHTUNG: Reihenfolge beachten:
			// diese Feld NACH ###PRG_TIMETABLE###
		$inviteImPossible = $this->checkExpiredProgram();
		if($this->ff['mode'] == 'tipAFriendView' && !$inviteImPossible) {
			$markerArray['###INVITE_POSSIBLE###'] = ''; # OK
		} elseif($this->ff['mode'] == 'tipAFriendView') {
			$markerArray['###INVITE_POSSIBLE###'] = $this->pi_getLL("err_invitePossible", "_err_invitePossible_");
			$markerArray['###PRG_TIMETABLE###'] = '';
		}

		$markerArray['###ANCHOR###']				= $this->getFieldContent('anchor');

/*
 *
 * Marker zum Program mit anker und marker zur einzelansicht!!!!!!!!!!!!
 *
 * Das ist zum Programm
 *
*/
#		$this->ff['pageSingleView']
#		$this->ff['pageProgram']
#		$this->ff['pagePreview']
#		$this->ff['tipAFriend']
#		$this->ff['pageBooking']

			# Programm verlinken
		$conf = array(
						"section" => $this->prefixId."-".$this->internal['currentRow']['uid'],
						"parameter" => $this->ff['pageProgram'],
						);
		$link['###LINK_PROGRAMM###'] = explode('|', $this->cObj->typoLink("|", $conf));

			# Vorschau verlinken
		$conf = array(
						"section" => $this->prefixId."-".$this->internal['currentRow']['uid'],
						"parameter" => $this->ff['pagePreview'],
						);
		$link['###LINK_PREVIEW###'] = explode('|', $this->cObj->typoLink("|", $conf));


			# Einzelansicht verlinken
		$conf = $this->pi_list_linkSingle(
					"|",
					$this->internal['currentRow']['uid'],
					TRUE,
					$mergeArr=array(),
					$urlOnly=FALSE,
					$this->ff['pageSingleView']);
		$link['###LINK_SINGLE###'] = explode('|', $conf);

			# Einen Freund einladen
		$conf = $this->pi_list_linkSingle(
					"|",
					$this->internal['currentRow']['uid'],
					FALSE,
					array('step' => 1),
					$urlOnly=FALSE,
					$this->ff['pageTipAFriend']);
		if($inviteImPossible == false) {
			$link['###TIPAFRIEND###'] = explode('|', $conf);
		} else {
			$link['###TIPAFRIEND###'] = '';
		}


				# Reservierungs Link
		$linkconf = array(
	    	 "title" => $this->pi_getLL("howtoBook"),
		     "parameter" => $this->conf['pageBooking'],
		     );
		list($date, $movie, $cinema) = explode("-", $this->decrypt($this->piVars['tipDate']));
		if($this->conf['cryptTime'] == 1) {
			$linkconf["additionalParams"] = "&".$this->prefixId."[crypt]=".$this->encrypt($date."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema']);
		} else {
			$linkconf["additionalParams"] = "&".$this->prefixId."[show]=".$date."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema'];
		}
		if($this->ff['disallowBooking'] == 'pageLinkOnly') unset($linkconf['additionalParams']);
		if($this->ff['disallowBooking'] == 'ticket') {
			list($date, $movie, $cinema) = explode("-", $this->decrypt($this->piVars['tipDate']));
			$linkconf = $this->ticketLink($date);
		}
		$conf = $this->cObj->typoLink('|', $linkconf);
		$link['###BOOKLINK###'] = explode('|', $conf);









		#Read more: http://dmitry-dulepov.com/article/why-substitutemarkerarraycached-is-bad.html#ixzz0dC2MuVq9
		$template = $this->cObj->substituteMarkerArray($template, $markerArray);
		foreach ($link as $subPart => $subContent) {
		    $template = $this->cObj->substituteSubpart($template, $subPart, $subContent);
		}



		return $template;
	}




		/**
		 * Hier wird die Spielzeitentabelle zusammengebastelt
		 *
		 * @return string Time Table
		 */
	function buildTimeTable() {

		if($this->internal['currentRow']['program']) {
				# Uhr auf 0 Uhr stellen!
				# Erster Programmtag
			$theDay = $this->internal['currentRow']['date'];
			$theDay = mktime ( 0, 0, 0, strftime("%m", $theDay),strftime("%d", $theDay), strftime("%Y", $theDay));

			if( (time() > $theDay ) && 	( time() < mktime(0, 0, 0, strftime("%m", $theDay),strftime("%d", $theDay)+7, strftime("%Y", $theDay)) ) ) {
				$todaysNr = strftime("%u", time()) + 3; #  Do!!
				if($todaysNr > 6) $todaysNr = $todaysNr%7;
			} else {
				$todaysNr = -1;
			}

				# tHead
			$head = '<thead><tr>';
			for($i=0; $i<7; $i++)
				{
				$time[$i] = mktime(0, 0, 0, date("m", $theDay), date("d", $theDay)+1*$i,   date("Y",$theDay));

				if($i == $todaysNr) {
					$head .= '<th style="background-color:'.$this->conf['todaysColor'].';">';

					if(date("d", $time[$i]) == date("d", time()) && $this->conf['todayStyle'] == 'today') {
						$head .= $this->cObj->wrap($this->pi_getLL("today"), $this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TH']);
					} else {
						$head .= $this->cObj->wrap(strftime($this->conf['tableTime'], $time[$i]), $this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TH']);
					}

					$head .= '</th>';
				} else {
					$head .= '<th align="center">';
					$head .= $this->cObj->wrap(strftime($this->conf['tableTime'], $time[$i]), $this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TH']);
					$head .= '</th>';
				}
			}

			$head .= '</tr></thead>';

				# tBody
			$temp = $this->internal['currentRow']['program'];
			$temp = explode("\n", trim($temp));

			# Mit Zeit verlinken
			$i=0; $n =0;
			foreach($temp as $row => $val) {
				$temp[$i] = explode("|", $val);

				foreach($temp[$i] as $key1 => $timeString) {
					$timeString = trim($timeString); # Zeilenende bereinigen

					if(preg_match( '/[0-9]?[0-9]:[0-9][0-9]/m', $timeString)) { /* Exakte Uhrzeit */ 
						list($theHour, $theMinute) = explode(":",$timeString);

						$theTime = mktime((int)$theHour, (int)$theMinute, 0, strftime("%m", $time[$n]), strftime("%d", $time[$n]), strftime("%Y", $time[$n]));

						$linkconf = array(
				    		 "title" => $this->pi_getLL("howtoBook"),
						     "parameter" => $this->conf['pageBooking'],
						     );

						if($this->conf['cryptTime'] == 1) {
							$linkconf["additionalParams"] = "&".$this->prefixId."[crypt]=".$this->encrypt($theTime."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema']);
						} else {
							$linkconf["additionalParams"] = "&".$this->prefixId."[show]=".$theTime."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema'];
						}

						if($this->ff['disallowBooking'] == 'pageLinkOnly') unset($linkconf['additionalParams']);

						if($this->ff['disallowBooking'] == 'ticket') {
							$linkconf = $this->ticketLink($theTime);
						}

							# Reservierungsschluss?
							# z.B. 5 Stunden vorher = t-60*60*5
						if(	(time() > $theTime-60*60*$this->conf['resLimit']) ||
							$this->ff['disallowBooking'] ||
							$this->internal['currentRow']['nores'] ||
							$this->ff['mode'] == 'tipAFriend'
							) {
							$temp[$i][$key1] = $timeString;  
						} else {
							$temp[$i][$key1] = $this->cObj->typoLink($timeString, $linkconf);
						}

							/* Tip A Friend */
						if($this->ff['mode'] == 'tipAFriendView' && (time() < $theTime-60*60*$this->conf['resLimit'])) {
							$value = $theTime."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema'];
							$value = $this->encrypt($value);
							if($this->piVars['tipDate'] == $value) {
								$temp[$i][$key1] = '<input class="tipaf-select" type="radio" name="tx_tmdcinema_pi1[tipDate]" value="'.$value.'" checked="checked" ><br />'.$temp[$i][$key1];
							} else {
								$temp[$i][$key1] = '<input class="tipaf-select" type="radio" name="tx_tmdcinema_pi1[tipDate]" value="'.$value.'"><br />'.$temp[$i][$key1];
							}
						}


					} elseif(preg_match( '/[0-9]?[0-9]\.[0-9][0-9]/m', $timeString)) { # keine Reservierung
						$temp[$i][$key1] = str_replace(".", ":", $temp[$i][$key1]);
					} else { # leere Zelle
						$temp[$i][$key1] = $this->conf['emptyTable'];
					}

					$n++;
				}
			$i++;
			$n = 0;
			}


			# Tabellenzeilen zusammenbauen
			$i=0;
			foreach($temp as $key => $val) {
				$tmp[$i] = "<tr>";
				foreach($val as $key1 => $val1) {
					$val1 = $this->cObj->wrap($val1, $this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TD']);
					if($key1 == $todaysNr) {# Heute!
						if(isset($this->conf['todaysColor'])) { # eigene Hintergundfarbe
							$tmp[$i] .= '<td style="'.$this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TD_STYLE'].' background-color: '.$this->conf['todaysColor'].';">'.$val1.'</td>';
						} else {
							$tmp[$i] .= '<td style="'.$this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TD_STYLE'].';">'.$val1.'</td>';
						}
					} else {
						$tmp[$i] .= '<td style="'.$this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_TD_STYLE'].'">'.$val1.'</td>';
					}
				}
				$tmp[$i] .= "</tr>";
				$i++;
			}

			$temp = '<table class="program" style="'.$this->conf['wrap.'][$this->ff['mode'].'.']['PRG_TIMETABLE_STYLE'].'">'.$head."<tbody>".implode(chr(10),$tmp).'</tbody></table>';
		} else { # Spielplan nicht bekannt
			$temp = "<br /><b>";
			if($this->internal['currentRow']['date'] > mktime()) {
				$temp .= 'Ab '.$this->getFieldContent('date').'<br />';
			}
			$temp .= $this->pi_getLL('timeNN');
			$temp .= '</b>';
		}

	return $temp;
	}





		/**
		 * Reservierung für .ticket System anzapfen
		 */
	function ticketLink($theTime) {
#debug(strftime("%H:%M %d.%m", $theTime), "ticket");
		$oneWeek = 7*24*60*60;

		$today0 = mktime ( 0, 0, 0, strftime("%m", time()),strftime("%d", time()), strftime("%Y", time()));
		$dif = $theTime-$today0;

		$weekCount =  (int) ($dif / $oneWeek);

		$link  = $this->ff['disallowBooking.']['server'];
		$link .= "?Week=".$weekCount;
		$link .= "&UserCenterID=".$this->ff['disallowBooking.']['UserCenterID'];

		$link .= "&SiteID=".$this->ff['disallowBooking.']['SiteID.'][$this->getFieldContent('cinemaID')];

		$out['parameter'] = $this->cObj->substituteMarker($this->ff['disallowBooking.']['window.']['parameter'], '###URL###', $link);

		return $out;
	}







	/**
	 * Returns the content of a given field
	 *
	 * @param	string		$fN: name of table field
	 * @return	Value of the field
	 */
	function getFieldContent($fN) {
			/* Alles über den Film rausfinden */
		if($this->film->id != $this->internal['currentRow']['movie']) {
			$this->film->getMovieById($this->internal['currentRow']['movie']);
#			$this->film->debug();
		}

		switch($fN)	{
			case 'movie_fsk':
					/* FSK-Cache erstellen */
				$out = $this->film->rating;
				if($out){
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_RATING.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_RATING']);
					return $out;
				}
			break;
			case 'movie_fskTooltip':
				$out = $this->film->ratingTooltip;
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_RATINGTOOLTIP.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_RATINGTOOLTIP']);
					return $out;
				}
			break;

			/* kommt aus anderer Tabelle */
			case 'movie_distributor':
				$out = $this->film->distibutor;

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_DISTRIBUTOR.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_DISTRIBUTOR']);
					return $out;
				}
			break;
			case 'movie_fbw':
				$field = $this->film->fbw;

				$out = $this->conf['fbw.'][$field];

				if($field) {
					$out = trim($out);
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_FBW.'][$field.'.']);
					$out = $this->cObj->wrap($out,    $this->conf[$this->templateNameTS]['MOVIE_FBW.'][$field]);
					return $out;
				}
			break;


			/* kommt aus der Film-Tabelle */
			case 'movie_title':
				$out = $this->film->titel;

				$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE.']);
				$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE']);
				return 	$out;
			break;
			case 'movie_originaltitle':
				$out = $this->film->originaltitle;
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE_ORIGINAL.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE_ORIGINAL']);
					return 	$out;
				}
			break;
			case 'movie_subtitle':
				$out = $this->film->short;
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE_SHORT.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE_SHORT']);
					return $out;
				}
			break;
			case 'movie_subtitle_first':
				if($this->film->short)
					$out = $this->film->short;
				else
					$out = $this->film->titel;

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE_SHORT.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_TITLE_SHORT']);
					return $out;
					}
			break;
			case 'movie_poster':
				# first get the imageFile
				if($this->film->poster) {
					$temp = explode(',', $this->film->poster); # mehrere Poster?
					$temp = $temp[rand(0,count($temp)-1)];

					$theImage = $this->uploadPath.$temp;
				} else { // Media File als Alternative
					$theImage = $this->uploadPath.$this->getFieldContent('movie_media-random');
				}
					# Kein File? -> Dummy Bild
				if(! @is_file($theImage)) {
					$theImage = $this->conf['dummyPoster'];
				}

					# TAF braucht eine Extrawurst
				if($this->ff['mode'] == 'tipAFriendView') {
					$this->ff['poster.'] = $this->conf['imageTipAFriend1.'];
					if($this->piVars['step'] == 2) $this->ff['poster.'] = $this->conf['imageTipAFriend2.'];
					if($this->piVars['step'] == 3) $this->ff['poster.'] = $this->conf['imageTipAFriend3.'];
				}

				# IMAGE oder GIFBUILDER
				if($this->ff['poster'] == 'IMAGE') {
					$this->ff['poster.']['file'] = $theImage;
				} elseif($this->ff['poster'] == 'GIFBUILDER') {
					$this->ff['poster.']['file.']['10.']['file'] = $theImage;
				}
						
				$this->ff['poster.']['altText'] = $this->film->titel;

				$out = $this->cObj->IMAGE($this->ff['poster.']);

				$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_IMAGE.']);
				$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_IMAGE']);
				return $out;
			break;

			case 'movie_trailer_single':
			case 'movie_trailer_list':
				if($this->film->trailer) {
					$width  = ($fN=='movie_trailer_single') ? $this->conf['trailer.']['single_width']  : $this->conf['trailer.']['list_width'];
					$height = ($fN=='movie_trailer_single') ? $this->conf['trailer.']['single_height'] : $this->conf['trailer.']['list_height'];

					$out = $this->cObj->fileResource($this->conf['templatePath'].$this->conf['templateTrailer']);
					$out = $GLOBALS['TSFE']->cObj->getSubpart($out, "###TRAILER_TEMPLATE###");

					$out = $this->cObj->substituteMarker($out, '###MOVIE_TRAILER_WIDTH###',  $width);
					$out = $this->cObj->substituteMarker($out, '###MOVIE_TRAILER_HEIGHT###', $height);
					$out = $this->cObj->substituteMarker($out, '###MOVIE_TRAILER_LINK###', 	$this->film->trailer);

					return ($fN=='movie_trailer_single') ? $out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['wrap.']['MOVIE_TRAILER_SINGLE']) : $out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['wrap.']['MOVIE_TRAILER_LIST']);
				}
			break;



			case 'movie_time':
				$out = $this->film->length;
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_TIME.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_TIME']);
					return $out;
				}
			break;
			case 'movie_www':
				$out = $this->film->web;
				if($out) {
					$out =  $this->pi_linkToPage($this->pi_getLL("website", "web"), $this->film->web, "_blank");

					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_WWW.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_WWW']);
					return $out;
				}
			break;
			case 'movie_trailer':
				$out = $this->film->trailer;
				if($out) {
					$out = $this->pi_linkToPage($this->pi_getLL("trailer", "_trailer_"), $this->film->trailer, "_blank");

					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_TRAILER.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_TRAILER']);
					return $out;
				}
			break;
			case 'movie_start':
				$out = $this->film->releasedate;
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_RELEASEDATE.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_RELEASEDATE']);
					return $out;
				}
			break;
			case 'movie_format':
				$out = $this->conf['format.'][$this->film->screenformat];

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_FORMAT.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_FORMAT']);
					return $out;
				}
			break;
			case 'movie_sound':
				$out = explode(" ", $this->film->sound);
				$temp = explode(",", $this->conf['supportedSound']);

				foreach ($temp as $key=>$value) {
				    if (!in_array($value,$out)) {
				        unset($temp[$key]);
				    }
				}

				$out = implode(" ", $temp);

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_SOUND.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_SOUND']);
					return $out;
				}
			break;
			case 'movie_summary':
				$out = $this->film->summary;
				if($out) {
					$out = $this->pi_RTEcssText($out);

					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_DESCRIPTION.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_DESCRIPTION']);
					return $out;
				}
			break;
			case 'movie_summary_short':
				$out = $this->film->summary;
				if($out) {
#					$out = $this->pi_RTEcssText($out);


					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_DESCRIPTION_SHORT.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_DESCRIPTION_SHORT']);


					return strip_tags($out); // z.B. <b> die nicht geschlossen werden duch crop
				}
			break;
			case 'movie_director':
				$out = $this->film->director;
				$out = strip_tags($out);

				$fullName = explode(",", $out);
				foreach($fullName as $val){
					$parts= array();
					$parts = explode(" ", $val);
					foreach($parts as $namePart) {
						$correctedName[] = ucfirst(strtolower($namePart));
					}
					$names[] = implode(" ", $correctedName);
					$correctedName = '';
				}
				$out = implode(", ", $names);

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_DIRECTOR.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_DIRECTOR']);
					return $out;
				}
			break;
			case 'movie_producer':
				$out = $this->film->producer;
				$out = strip_tags($out);

				$fullName = explode(",", $out);
				foreach($fullName as $val){
					$parts= array();
					$parts = explode(" ", $val);
					foreach($parts as $namePart) {
						$correctedName[] = ucfirst(strtolower($namePart));
					}
					$names[] = implode(" ", $correctedName);
					$correctedName = '';
				}
				$out = implode(", ", $names);

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_PRODUCER.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_PRODUCER']);
					return $out;
				}
			break;
			case 'movie_actor':
				$out = $this->film->actor;
				$out = strip_tags($out);

				$fullName = explode(",", $out);

				foreach($fullName as $val){
					$parts= array();
					$parts = explode(" ", $val);
					foreach($parts as $namePart) {
						$correctedName[] = ucfirst(strtolower($namePart));
					}
					$names[] = implode(" ", $correctedName);
					$correctedName = '';
				}
				$out = implode(", ", $names);

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_ACTOR.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_ACTOR']);

					return $out;
				}
			break;
			case 'movie_genre':
#debug($this->conf[$this->templateNameTS]);
				$out = $this->film->genre;
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_GENRE']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_GENRE']);
					return $out;
				}
			break;
			case 'movie_media-1':
			case 'movie_media-2':
			case 'movie_media-3':
			case 'movie_media-4':
			case 'movie_media-5':
			case 'movie_media-random':
				$image = $this->conf[$this->templateNameTS]['media.'];

				list(,$nr) = explode("-", $fN);
				$pic = explode(",", $this->film->media);

				if($nr == 'random') {
					foreach($pic as $key => $val) {
						if($val == '') unset($pic[$key]);
					}

					$c = count($pic);
					if($c == 0)
						break;

					return $pic[rand(0,$c-1)];
				} else {
					$nr--; // bei 0 anfangen zu zählen
					$pic = $pic[$nr];
				}

				$image['file'] = 'uploads/tx_tmdmovie/'.$pic;

						
				$out = $this->cObj->IMAGE($image);

				
				return $out;
			break;
			case 'trailer_single':
			case 'trailer_list':
				$out = $this->film->trailer;
				if($out) {
					$width  = ($fN=='trailer_single') ? $this->conf['trailer.']['single_width']  : $this->conf['trailer.']['list_width'];
					$height = ($fN=='trailer_single') ? $this->conf['trailer.']['single_height'] : $this->conf['trailer.']['list_height'];

					$out = $this->cObj->fileResource($this->conf['template']);
					$out = $GLOBALS['TSFE']->cObj->getSubpart($template, "###TRAILER_TEMPLATE###");

					$out = $this->cObj->substituteMarker($out, '###TRAILER_WIDTH###', 	$width);
					$out = $this->cObj->substituteMarker($out, '###TRAILER_HEIGHT###',	$height);
					$out = $this->cObj->substituteMarker($out, '###TRAILER_LINK###', 	$out);

					if($out) {
						$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['MOVIE_YOUTUBE.']);
						$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['MOVIE_YOUTUBE']);
						return $out;
					}
				}
			break;




			case 'version3d':
				if($this->film->version3D || $this->internal['currentRow']['features']&1 ) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['VERSION3D.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['VERSION3D']);
					return $out;
				}
			break;
			case 'ifDateChanges':
				$out = $this->internal['currentRow']['ifDateChanges'];

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['IFDATECHANGES.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['IFDATECHANGES']);
					return $out;
				}
			break;
			




			/* Ab hier die Programm-Tabelle */
			case 'uid':
				return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			case 'anchor':
				return $this->prefixId."-".$this->internal['currentRow']['uid'];
			break;
			case 'firstday':
			case 'date':
				$out = strftime($this->conf['timeFormat'], $this->internal['currentRow']['date']);

				$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_DATE.']);
				$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_DATE']);
				return $out;
			break;
			case 'debug_firstday':
				return strftime("%d.%m.%y %H:%M:%S", $this->internal['currentRow']['date']);
			break;
			case 'firstday_raw':
				return $this->internal['currentRow']['date'];
			break;
			case "program":
				$out = $this->buildTimeTable();

				$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_TIMETABLE.']);
				$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_TIMETABLE']);
				return $out;
			break;
			case "program_raw":
				return $this->internal['currentRow']['program'];
			break;
			case 'info':
				$out = $this->internal['currentRow']['info'];
				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_INFO.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_INFO']);
					return $out;
					}
			break;
			case 'info2':
				$out = $this->internal['currentRow']['info2'];
				if($out) {
					$out = $this->pi_RTEcssText($out);
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_INFO2.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_INFO2']);
					return $out;
					}
			break;
			case 'showtype':
				/* ShowType-Cache erstellen */
				if(!$this->showType) {
					$select = 'uid,showtype,link';
					$local_table = 'tx_tmdcinema_showtype';
					$whereClause = "1=1 ".$GLOBALS['TSFE']->cObj->enableFields($local_table);
					$res = $GLOBALS[TYPO3_DB]->exec_SELECTquery($select,$local_table,$whereClause,$groupBy,$orderBy,$limit);
					while($erg = $GLOBALS[TYPO3_DB]->sql_fetch_assoc($res)) {
						$this->showType[$erg['uid']]['showtype'] = $erg['showtype'];
						$this->showType[$erg['uid']]['link'] = $erg['link'];
					}
				}

					/* Vorstellungen können mit Seiten verlinkt werden */
				if(!in_array($this->internal['currentRow']['showtype'], explode(",", $this->conf['hideType']))) {
					if($this->showType[$this->internal['currentRow']['showtype']]['link'] != "") {
						$out = $this->pi_linkToPage(
							$this->showType[$this->internal['currentRow']['showtype']]['showtype'],
							$this->showType[$this->internal['currentRow']['showtype']]['link']
							);
					} else {
						$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['wrap.']['showtype']);
					}
				}

				if($out) {
					$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_SHOWTYPE.']);
					$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_SHOWTYPE']);
					return $out;
				}
			break;
			case 'week':
				$out = $this->internal['currentRow']['week'];

				$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_WEEK.']);
				$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_WEEK']);
				return 	$out;
			break;
			case 'cinema':
				if(!$this->adrCache[$this->internal['currentRow'][$fN]]) {
					$this->adrCache[$this->internal['currentRow'][$fN]] = $this->pi_getRecord("tt_address", $this->internal['currentRow'][$fN]);
				}

				$out = $this->adrCache[$this->internal['currentRow'][$fN]]['company'];

				$out = $this->cObj->stdWrap($out, $this->conf[$this->templateNameTS]['PRG_THEATRE.']);
				$out = $this->cObj->wrap($out, $this->conf[$this->templateNameTS]['PRG_THEATRE']);

				return $out;
			break;
			case 'cinemaID':
				return $this->internal['currentRow']['cinema'];
			break;

			default:
				return "neues Feld?-> $fN ".$this->internal['currentRow'][$fN];
			break;
			}
		}



	} /* END of CLASS */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi1/class.tmd_cinema_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi1/class.tmd_cinema_pi1.php']);
}
?>