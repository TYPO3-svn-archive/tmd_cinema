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

/* oelib
require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_templatehelper.php');
require_once(PATH_formidableapi);
*/

/**
 * Plugin 'Cinema Program' for the 'tmd_cinema' extension.
 *
 * @author	Christian Tauscher <cms@media-distillery.de>
 * @package	TYPO3
 * @subpackage	tmd_cinema
 */
class tx_tmdcinema_pi3 extends tslib_pibase {
/* oelib
 * class tx_tmdcinema_pi3 extends tx_oelib_templatehelper {
 */
	var $prefixId      = 'tx_tmdcinema_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tmd_cinema_pi3.php';	// Path to this script relative to the extension dir.
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
	function main($content,$conf)
		{
		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		$this->film = t3lib_div::makeInstance("tx_tmdmovie");
		$this->initFF();
		
#debug($this->piVars);

		$now = mktime(0, 0, 0, date("m", time()) ,date("d", time())  , date("Y", time()));
		if($this->piVars['week'] == '')  $this->piVars['week']  = $now;
		if($this->piVars['boDay'] == '') $this->piVars['boDay'] = $now;
		
		
		$now = $this->piVars['week'];
		switch(strftime("%u", $now)) { # %u = Tag der Woche 1= Montag
			case 1: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)-4, date("Y", $now)); break; # Mo
			case 2: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)-5, date("Y", $now)); break; # DI
			case 3: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)-6, date("Y", $now)); break; # MI
			case 4: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)  , date("Y", $now)); break; # DO
			case 5: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)-1, date("Y", $now)); break; # FR
			case 6: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)-2, date("Y", $now)); break; # SA
			case 7: $wStart = mktime(0, 0, 0, date("m", $now) ,date("d", $now)-3, date("Y", $now)); break; # SO
		}
		$this->wStart = mktime(00, 00, 00, date("m", $wStart) ,date("d", $wStart)  , date("Y", $wStart));
		$this->wEnd   = mktime(23, 59, 59, date("m", $wStart) ,date("d", $wStart)+6, date("Y", $wStart));		
debug(strftime("%a %d.%m.%y %H:%M:%S", $this->wStart).'-'.strftime("%a %d.%m.%y  %H:%M:%S", $this->wEnd), "Zeitspanne");
		

 		if($this->piVars['visitors'] || $this->piVars['visitors']) {
 			$this->addNewData();
 		}

 		
			/* Todo: gegen FF ersetzten
			 *
			 * inkl Recursion die PIDs als Liste ermitteln.
			 * Da gibt es eine Funktion...
			 */
		if (strstr($this->cObj->currentRecord,'tt_content')) {
			$this->conf['pidList'] = $this->cObj->data['pages'];
			$this->conf['recursive'] = $this->cObj->data['recursive'];
		}

		return $this->pi_wrapInBaseClass($this->program(0, 1, 'long'));
	}




	
	function addNewData(){
		#$boxOffice = t3lib_div::xml2array($this->internal['currentRow']['boxoffice']);
		debug($this->internal['currentRow']);

			# alte daten holen
		$dbRecord = $this->pi_getRecord('tx_tmdcinema_program', $this->piVars['prgId']);
		if(is_array($dbRecord)) {
			$dbRecord = $dbRecord['boxoffice'];
			$dbRecord = t3lib_div::xml2array($dbRecord);
		}

			# neue Daten Vorbereiten
		foreach($this->piVars['money'] as $key => $val){
			$boxOffice[$this->piVars['boDay']][$key]['money'] = $val;
			$boxOffice[$this->piVars['boDay']][$key]['visitors'] = $this->piVars['visitors'][$key];
		}

#debug(array($dbRecord, $boxOffice), "BO transformed");
		
			# Daten zusammenführen
		if(is_array($dbRecord)) {
			$boxOffice = t3lib_div::array_merge($dbRecord, (array)$boxOffice);
		}


		$boxOffice = t3lib_div::array2xml_cs($boxOffice, $docTag='boxoffice');

				
		$table = 'tx_tmdcinema_program';
		$where = 'uid = '.$this->piVars['prgId'];
		$fields_values = array('boxoffice' => $boxOffice);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$fields_values,$no_quote_fields=FALSE); 
	}








		/**
		 * Zeit Große Übersicht
		 *
		 * @param string	Welche Art Programm
		 * @param int Startwoche, 0 = aktuelle Woche, 1=nächste Woche, 2=übernächste u.s.w.
		 * @param int Wieviele Wochen?
		 */
	function program() {
		$now = mktime();
		$wStart = $this->wStart;
		$wEnd   = $this->wEnd;		
		$wStart_tmp = $wStart; # für "kein progamm" zwischenspeichern
#debug(strftime("%a %d.%m.%y %H:%M:%S", $wStart).'-'.strftime("%a %d.%m.%y  %H:%M:%S", $wEnd), "Zeitspanne");

		$whereClause .= 'AND date >= '.$wStart.' AND date < '.$wEnd;
		$whereClause .= " AND cinema IN (".$this->ff['def']['cinema'].")";

			// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_tmdcinema_program', 0, $whereClause, $mm_cat='', $groupBy, $sortBy);

			// Make list table rows
		$items=array();
		$noDate=array();
		$cinemaOrder = explode(",", $this->ff['def']['cinema']);

		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tempTime = $this->internal['currentRow']['date'];
			list($day, $month, $year) = explode(",", strftime("%e,%m,%Y", $tempTime));
			$this->internal['currentRow']['date'] = mktime(0,0,0,$month,$day, $year);
			
			$all[] = $this->internal['currentRow'];
		}

		if (count($all)  == 0) {  /* Es gibt noch kein Programm */
			return $this->substituteMarkers("PROGRAMM_BOXOFFICE");
		}
		
		foreach($cinemaOrder as $cinema) { 
			foreach($all as $this->internal['currentRow']) {

				if($this->internal['currentRow']['cinema'] == $cinema) { # Sammelüberschrift
					if($wStart != $this->internal['currentRow']['date'] && $type != 'special'/* && $type != 'RSS'*/) {
						$wStart = $this->internal['currentRow']['date'];

						if($wStart > 0 && $this->conf['showWeekDate'] != 0 ) {
							$items[] = '<h2>'.((!$startWeek)?"Programm": "")." ab ".strftime($this->conf['timeFormat'], $wStart)."</h2>";
						} elseif($wStart == 0) {
							$noDate[] = '<h2>Demnächst</h2>';
						}
					}

					# Film ausgeben
					$items[] = '<a name="'.$this->prefixId."-".$this->internal['currentRow']['uid'].'"></a>'.
								$this->substituteMarkers("PROGRAMM_BOXOFFICE");
				}
			}	
		}

		if(is_array($items)) {
			if(count($items) > 0) {
				$prg .= implode(chr(10), $items);
			}
		}
		if(is_array($noDate)) {
			if(count($noDate) > 0) {
				$prg .= implode(chr(10), $noDate);
			}
		}

		unset($items);
		unset($noDate);
	
		$out .= '<div class="'.$this->pi_getClassName($type.'Program').'">';
		$out .= $prg;
		$out .= '</div>';

		return $out;
	}



	/**
	 * Returns the detail View
	 *
	 * @return	the view
	 */
	function singleView() {
		// Spammern das Handwerk legen
		// Res Limit berücksichtigen und das Kino auch.
		if(	$this->internal['currentRow']['date'] > 0 && (
				7*24*60*60-$this->conf['resLimit'] < time()-$this->internal['currentRow']['date']  ||
				!in_array($this->internal['currentRow']['cinema'], explode(",", $this->conf['myCinema']))   )
			) {
			$content = $this->pi_getLL("noValidPRG", "_noValidPRG_");
		} else {
			$content = $this->substituteMarkers("SINGLE_VIEW");
		}

		return $content;
	}



	
	
	function substituteMarkers($subPart, $markerArray="") {
		$template = $this->cObj->fileResource($this->conf['template']);
		$template = $GLOBALS['TSFE']->cObj->getSubpart($template, "###".$subPart."###");
		if(strlen($template) < 1) {
			debug("Kein Template!! ###".$subPart."###");
			return;
		}

			// oben weil ein array zurück kommt
#		$markerArray = $this->getFieldContent('movie_media');

		$markerArray['###MOVIE_TITLE###'] 			= $this->getFieldContent('movie_title');
		$markerArray['###MOVIE_TITLE_ORIGINAL###']  = $this->getFieldContent('movie_originaltitle');
		$markerArray['###MOVIE_TITLE_SHORT###']		= $this->getFieldContent('movie_subtitle');
		$markerArray['###MOVIE_TITLE_SHORT_FIRST###'] = $this->getFieldContent('movie_subtitle_first');

		$markerArray['###MOVIE_IMAGE###'] 			= $this->getFieldContent('movie_image');
		$markerArray['###MOVIE_RATING###'] 			= $this->getFieldContent('movie_fsk');
		$markerArray['###MOVIE_RATINGTOOLTIP###'] 	= $this->getFieldContent('movie_fskTooltip');
		$markerArray['###MOVIE_TIME###'] 			= $this->getFieldContent('movie_time');
		$markerArray['###MOVIE_RELEASEDATE###']		= $this->getFieldContent('movie_start');
		$markerArray['###MOVIE_FORMAT###'] 			= $this->getFieldContent('movie_format');
		$markerArray['###MOVIE_SOUND###'] 			= $this->getFieldContent('movie_sound');
		$markerArray['###MOVIE_DISTRIBUTOR###'] 	= $this->getFieldContent('movie_distributor');
		$markerArray['###MOVIE_WWW###'] 			= $this->getFieldContent('movie_www');
		$markerArray['###MOVIE_YOUTUBE###'] 		= $this->getFieldContent('movie_youtube');
		$markerArray['###MOVIE_DESCRIPTION###'] 	= $this->getFieldContent('movie_summary');
		$markerArray['###MOVIE_DESCRIPTION_SHORT###'] =	$this->getFieldContent('movie_summary_short');
		$markerArray['###MOVIE_EXTRAPICS###'] 		= $this->getFieldContent('movie_mediafile');
		$markerArray['###MOVIE_SUBTITLE###'] 		= $this->getFieldContent('movie_subtitle');
		$markerArray['###MOVIE_IMAGE_LINK###'] 		= $this->getFieldContent('movie_imageLink');
		$markerArray['###MOVIE_FBW###'] 			= $this->getFieldContent('movie_fbw');
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

		$markerArray['###PRG_WEEK###'] 				= $this->getFieldContent('week');
		$markerArray['###PRG_SHOWTYPE###'] 			= $this->getFieldContent('showtype');
		$markerArray['###PRG_TIMETABLE###'] 		= $this->getFieldContent('program');
		$markerArray['###PRG_INFO###'] 				= $this->getFieldContent('info');
		$markerArray['###PRG_INFO2###'] 			= $this->getFieldContent('info2');
		$markerArray['###PRG_THEATRE###'] 			= $this->getFieldContent('cinema');
		$markerArray['###PRG_FIRSTDAY###'] 			= $this->getFieldContent('firstday');
		$markerArray['###PRG_STARTDAY###'] 			= $this->getFieldContent('date');


		
		$t = mktime(00, 00, 00, date("m", $this->wStart) ,date("d", $this->wStart)-7, date("Y", $this->wStart));
		$markerArray['###WEEK_PREV###'] = $this->pi_linkTP_keepPIvars(strftime("%d.%m.%y", $t), array('week' => $t), $cache=0,$clearAnyway=1,$altPageId=0);
		
		$t = mktime(00, 00, 00, date("m", $this->wStart) ,date("d", $this->wStart)  , date("Y", $this->wStart));  
		$markerArray['###WEEK_NOW###'] 	= strftime("%d.%m.%y", $t);
		
		$t = mktime(00, 00, 00, date("m", $this->wStart) ,date("d", $this->wStart)+7, date("Y", $this->wStart));
		$markerArray['###WEEK_NEXT###'] = $this->pi_linkTP_keepPIvars(strftime("%d.%m.%y", $t), array('week' => $t), $cache=0,$clearAnyway=0,$altPageId=0);

/*
 *
 * Marker zum Program mit anker und marker zur einzelansicht!!!!!!!!!!!!
 *
 * Das ist zum Programm
 *
*/
			# Programm verlinken
		$conf = array(
						"section" => $this->prefixId."-".$this->internal['currentRow']['uid'],
						"parameter" => $this->conf['linkImagePage']
						);
		$link['###LINK_PROGRAMM###'] = explode('|', $this->cObj->typoLink("|", $conf));

			# Einzelansicht verlinken
		$conf = $this->pi_list_linkSingle(
					"|",
					$this->internal['currentRow']['uid'],
					TRUE,
					$mergeArr=array(),
					$urlOnly=FALSE,
					$this->conf['linkImagePage']);
		$link['###LINK_SINGLE###'] = explode('|', $conf);



		#Read more: http://dmitry-dulepov.com/article/why-substitutemarkerarraycached-is-bad.html#ixzz0dC2MuVq9
		$template = $this->cObj->substituteMarkerArray($template, $markerArray);
		foreach ($link as $subPart => $subContent) {
		    $template = $this->cObj->substituteSubpart($template, $subPart, $subContent);
		}



		return $template;
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
		 * Hier wird die Spielzeitentabelle zusammengebastelt
		 *
		 * @return string Time Table
		 */
	function buildTimeTable() { 
		$actionURL = $this->pi_linkTP_keepPIvars_url(array('prgId' => $this->internal['currentRow']['uid']),$cache=0,$clearAnyway=0,$altPageId=0);
#debug($this->piVars);
#debug($actionURL, "action");
		if($this->internal['currentRow']['program']) {
				# Uhr auf 0 Uhr stellen!
				# Erster Programmtag
			$theDay = mktime(0, 0, 0, date("m", $this->internal['currentRow']['date']), date("d", $this->internal['currentRow']['date']), date("Y", $this->internal['currentRow']['date']));

			if( (time() > $theDay ) && 	( time() < mktime(23, 59, 59, date("m")  , date("d")+6, date("Y")))) {
				$todaysNr = strftime("%u", time()) + 3;
				if($todaysNr > 6) $todaysNr = $todaysNr%7;
			} else {
				$todaysNr = -1;
			}

				# tHead
			$head = '<thead><tr>';
			for($i=0; $i<7; $i++)
				{
				$time[$i] = mktime(0, 0, 0, date("m", $theDay), date("d", $theDay)+$i, date("Y", $theDay));# +60*60*24*$i;

				$head .= '<th align="center">';
				$str = $this->cObj->wrap(strftime($this->conf['tableTime'], $time[$i]), $this->conf['wrap.']['PRG_TIMETABLE_TH']);
				$head .= $this->pi_linkTP_keepPIvars($str, array('boDay' => $time[$i]),$cache=0,$clearAnyway=1,$altPageId=0);  
				$head .= '</th>';
			}
			
				# BoxOffice Kopf
			$head .= '<th class="bo_head">';
			$head .= $this->cObj->wrap(strftime($this->conf['tableTimeBO'], $this->piVars['boDay']), $this->conf['wrap.']['PRG_TIMETABLE_TH']);
			$head .= '</th>';
			
			$head .= '</tr></thead>';


			$boxOffice = array();
			$boxOffice = t3lib_div::xml2array($this->internal['currentRow']['boxoffice']);
#debug($boxOffice, "bo-xml");

				# tBody
			$temp = $this->internal['currentRow']['program'];
			$temp = explode("\n", trim($temp));

			$i=0;
			foreach($temp as $row => $val) {
				$temp[$i] = explode("|", $val);
				$emptyFlag = false;
				
				foreach($temp[$i] as $key1 => $timeString) {
					$timeString = trim($timeString); # Zeilenende bereinigen
					
					if($timeString) {
						$temp[$i][$key1] = $timeString;
					} else { # leere Zelle
						$temp[$i][$key1] = $this->conf['emptyTable'];
						$emptyFlag = true;
					}

					if(is_Array($boxOffice)) {
						$money    = $boxOffice[$this->piVars['boDay']][$i+1]['money'];
						$visitors = $boxOffice[$this->piVars['boDay']][$i+1]['visitors'];
					}  
					$temp[$i]['bo'] = '	<input type="text" name="'.$this->prefixId.'[money]['.($i+1).']" 	size="5" value="'.$money.'">
										<input type="text" name="'.$this->prefixId.'[visitors]['.($i+1).']" size="5" value="'.$visitors.'">';
					
				}
				$i++;
			}


			# Tabellenzeilen zusammenbauen
			$i=0;
			foreach($temp as $key => $val) {
				$tmp[$i] = "<tr>";
				foreach($val as $key1 => $val1) {
					$val1 = $this->cObj->wrap($val1, $this->conf['wrap.']['PRG_TIMETABLE_TD']);
					if($key1 == $todaysNr) {# Heute!
						$tmp[$i] .= '<td style="'.$this->conf['wrap.']['PRG_TIMETABLE_TD_STYLE'].';">'.$val1.'</td>';
					} else {
						$tmp[$i] .= '<td style="'.$this->conf['wrap.']['PRG_TIMETABLE_TD_STYLE'].'">'.$val1.'</td>';
					}
				}
				$tmp[$i] .= "</tr>";
				$i++;
			}



				# Umsatz anzeigen
			$bo = t3lib_div::xml2array($this->internal['currentRow']['boxoffice']);
#			asort($bo);
#debug($bo);

			$tmp[$i]  = "<tr>";
			for($col=0; $col<7; $col++) {
				$theDay = mktime(0, 0, 0, date("m", $this->wStart), date("d", $this->wStart)+$col, date("Y", $this->wStart));
				$customer = 0; 
				$boxoffice = 0;
				
				foreach($bo[$theDay] as $data) {
					$customer += $data['visitors']; 
					$boxoffice += $data['money'];
				}

			$tmp[$i] .= '<td class="boxoffice" style="'.$this->conf['wrap.']['PRG_TIMETABLE_TD_STYLE'].';">'.$boxoffice.'<br />'.$customer.'</td>';
			}
/*			
			for($t=$col; $t<7; $t++) {
				$tmp[$i] .= '<td class="boxoffice" colspstyle="'.$this->conf['wrap.']['PRG_TIMETABLE_TD_STYLE'].';">&nbsp;</td>';
			}
*/
			$tmp[$i] .= '<td class="boxoffice" style="'.$this->conf['wrap.']['PRG_TIMETABLE_TD_STYLE'].';">summe?<br /><input type="submit" value="speichern"></td>';
			$tmp[$i] .= "</tr>";
			

			
			
			
			$temp  = '<form name="tx-tmdcinema-pi3" action="'.$actionURL.'" method="post" >';
			$temp .= '<table class="program" style="'.$this->conf['wrap.']['PRG_TIMETABLE_STYLE'].'">'.$head."<tbody>".implode(chr(10),$tmp).'</tbody></table>';
			$temp .= '</form>'; 
		} else { # Spielplan nicht bekannt
			$temp = "<br /><b>";
			$temp .= $this->pi_getLL('timeNN');
			$temp .= '</b>';
		}
	return $temp;
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
				$out = $this->film->rating;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_RATING']);
					return $out;
				}
			break;
			case 'movie_fskTooltip':
				$out = $this->film->ratingTooltip;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_RATINGTOOLTIP']);
					return $out;
				}
			break;

			/* kommt aus anderer Tabelle */
			case 'movie_distributor':
				$out = $this->film->distibutor;

				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_DISTRIBUTOR']);
					return $out;
				}
			break;
			case 'movie_fbw':
				$field = $this->film->fbw;
				if($field) {
					return $this->cObj->wrap(trim($this->conf['fbw.'][$field]), $this->conf['wrap.']['MOVIE_FBW.'][$field]);
				}
			break;
			case 'movie_image':
				if($this->film->poster) {
					$temp = explode(',', $this->film->poster); # mehrere Poster?
					$temp = $temp[rand(0,count($temp)-1)];

					if($this->conf['image.']['file'] == 'GIFBUILDER') {
						$this->conf['image.']['file.']['10.']['file'] = $this->uploadPath.$temp;
					} else {
						$this->conf['image.']['file'] = $this->uploadPath.$temp;
#						$this->conf['image.']['file.']['width'] = $this->conf['image.']['file.']['width'];
					}
				} else { // Media File als Alternative
					if($this->conf['image.']['file'] == 'GIFBUILDER') {
						$this->conf['image.']['file.']['10.']['file'] = $this->uploadPath.$this->getFieldContent('movie_media-random');

						if($this->conf['image.']['file.']['10.']['file'] == '') {
							$this->conf['image.']['file.']['10.']['file'] = $this->conf['dummyPoster'];
						}
					} else {
							$this->conf['image.']['file'] = $this->uploadPath.$this->getFieldContent('movie_media-random');
					}
				}

				if($this->conf['image.']['file'] == 'uploads/tx_tmdmovie/') {
					$this->conf['image.']['file'] = $this->conf['dummyPoster'];
				}

				$this->conf['image.']['altText'] = $this->film->titel;
				$out = $this->cObj->IMAGE($this->conf['image.']);
				$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_IMAGE']);
				return $out;
			break;
			

			/* kommt aus der Film-Tabelle */
			case 'movie_title':
				$out = $this->cObj->wrap($this->film->titel, $this->conf['wrap.']['MOVIE_TITLE']);
				return 	$out;
			break;
			case 'movie_originaltitle':
				$out = $this->film->originaltitle;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_TITLE_ORIGINAL']);
					return 	$out;
				}
			break;
			case 'movie_subtitle':
				$out = $this->film->short;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_TITLE_SHORT']);
					return $out;
				}
			break;
			case 'movie_subtitle_first':
				if($this->film->short)
					$out = $this->film->short;
				else
					$out = $this->film->titel;

				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_TITLE_SHORT']);
					return $out;
					}
			break;
			case 'movie_time':
				$out = $this->film->length;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_TIME']);
					return $out;
				}
			break;
			case 'movie_start':
				$out = $this->film->releasedate;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['MOVIE_RELEASEDATE']);
					return $out;
				}
			break;

			

			/* Ab hier die Programm-Tabelle */
			case 'uid':
				return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			case 'firstday':
			case 'date':
				$out = strftime($this->conf['timeFormat'], $this->internal['currentRow']['date']);
				$out = $this->cObj->wrap($out, $this->conf['wrap.']['PRG_DATE']);
				return $out;
			break;
			case 'firstday_raw':
				return $this->internal['currentRow']['date'];
			break;
			case "program":
				$out = $this->cObj->wrap($this->buildTimeTable(), $this->conf['wrap.']['PRG_TIMETABLE']);
				return $out;
			break;
			case "program_raw":
				return $this->internal['currentRow']['program'];
			break;
			case 'info':
				$out = $this->internal['currentRow']['info'];
				if($out)
					{
#debug($this->conf['wrap.']['PRG_INFO']);

					$out = $this->cObj->wrap($out, $this->conf['wrap.']['PRG_INFO']);
					return $out;
					}
			break;
			case 'info2':
				$out = $this->internal['currentRow']['info2'];
				if($out)
					{
					$out = $this->cObj->wrap($this->pi_RTEcssText($out), $this->conf['wrap.']['PRG_INFO2']);
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
						$out = $this->showType[$this->internal['currentRow']['showtype']]['showtype'];
					}
				}

				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.']['PRG_SHOWTYPE']);
					return $out;
				}
			break;
			case 'week':
				$out = $this->cObj->wrap($this->internal['currentRow']['week'], $this->conf['wrap.']['PRG_WEEK']);
				return 	$out;
			break;
			case 'cinema':
				if(!$this->adrCache[$this->internal['currentRow'][$fN]]) {
					$this->adrCache[$this->internal['currentRow'][$fN]] = $this->pi_getRecord("tt_address", $this->internal['currentRow'][$fN]);
				}

				$out = $this->cObj->wrap($this->adrCache[$this->internal['currentRow'][$fN]]['company'], $this->conf['wrap.']['PRG_THEATRE']);

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

		
		
		
		/**
		 * Flex Form Parameter werden ausgelesen und initialisiert
		 *
		 * @todo TYPOscript parameter berücksichtigen und eventuell Überschreiben
		 */
	function initFF() {
			# FF Parsen
		$this->pi_initPIflexForm();
		$this->ff['def']['cinema'] 		= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cinema',	 		's_DEF');

	}
		
	
	
	
	


	} /* END of CLASS */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi3/class.tmd_cinema_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi3/class.tmd_cinema_pi3.php']);
}
?>