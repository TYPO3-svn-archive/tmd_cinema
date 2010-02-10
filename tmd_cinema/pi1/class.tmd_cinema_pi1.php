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
class tx_tmdcinema_pi1 extends tslib_pibase {
/* oelib
 * class tx_tmdcinema_pi1 extends tx_oelib_templatehelper {
 */
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
	function main($content,$conf)
		{

		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

/* oelib
		$this->init($conf);
		$confCheck = $this->checkConfiguration();
		if($confCheck) return $this->pi_wrapInBaseClass("->".$confCheck."<-");
#debug($this->getConfiguration(), "hier");
*/
		$this->initFF();
		$this->initTemplate();


		$this->film = t3lib_div::makeInstance("tx_tmdmovie");

			/* Todo: gegen FF ersetzten
			 *
			 * inkl Recursion die PIDs als Liste ermitteln.
			 * Da gibt es eine Funktion...
			 */
		if (strstr($this->cObj->currentRecord,'tt_content'))
			{
			$this->conf['pidList'] = $this->cObj->data['pages'];
			$this->conf['recursive'] = $this->cObj->data['recursive'];
			}

		return ($this->ff['def']['mode'] != 'RSS' ) ? $this->pi_wrapInBaseClass($this->view()) : $this->view();
		}



		/**
		 * Shows a list of database entries
		 *
		 * @param	string		$content: content of the PlugIn
		 * @param	array		$conf: PlugIn Configuration
		 * @return	HTML list of table entries
		 */
	function view() {
		switch($this->ff['def']['mode']) {
			case 'shortView':
			 	$content = $this->program($this->ff['def']['previewMin'], $this->ff['def']['previewMax'], "short");
			break;
			case 'special':
			 	$content = $this->program($this->ff['def']['previewMin'], $this->ff['def']['previewMax'], "special");
			break;
			case 'longView':
				$content = $this->program($this->ff['def']['previewMin'], $this->ff['def']['previewMax'], "long");
			break;
			case 'RSS':
				$xmlHeader = $this->getXmlHeader();
				$content = $this->cObj->substituteMarkerArrayCached($this->template, $xmlHeader, '', $wrappedSubpartArray);
				$items = $this->program(0, 1, "RSS");
				$content = $this->cObj->substituteMarkerArrayCached($content, $items, '', $wrappedSubpartArray);
				$content = $this->substituteMarkers("PROGRAMM_RSS2", $content);
			break;
			case 'booking':
				$content = $this->booking();
			break;
			case 'singleView':
				$this->internal['currentTable'] = 'tx_tmdcinema_program';
				$this->internal['currentRow'] = $this->pi_getRecord('tx_tmdcinema_program',$this->piVars['showUid']);
				$content = $this->singleView();
			break;
		}

		return $content;
	}



	/**
	 * builds the XML header (array of markers to substitute)
	 * aus tt_news
	 * @return	array		the filled XML header markers
	 */
	function getXmlHeader() {
		$markerArray = array();

		$markerArray['###SITE_TITLE###'] = $this->conf['displayXML.']['xmlTitle'];
		$markerArray['###SITE_LINK###'] = $this->config['siteUrl'];
		$markerArray['###SITE_DESCRIPTION###'] = $this->conf['displayXML.']['xmlDesc'];
		if(!empty($markerArray['###SITE_DESCRIPTION###'])) {
			if($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
				$markerArray['###SITE_DESCRIPTION###'] = '<tagline>'.$markerArray['###SITE_DESCRIPTION###'].'</tagline>';
			} elseif($this->conf['displayXML.']['xmlFormat'] == 'atom1') {
				$markerArray['###SITE_DESCRIPTION###'] = '<subtitle>'.$markerArray['###SITE_DESCRIPTION###'].'</subtitle>';
			}
		}

		$markerArray['###SITE_LANG###'] = $this->conf['displayXML.']['xmlLang'];
		if($this->conf['displayXML.']['xmlFormat'] == 'rss2') {
			$markerArray['###SITE_LANG###'] = '<language>'.$markerArray['###SITE_LANG###'].'</language>';
		} elseif($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
			$markerArray['###SITE_LANG###'] = ' xml:lang="'.$markerArray['###SITE_LANG###'].'"';
		}
		if(empty($this->conf['displayXML.']['xmlLang'])) {
			$markerArray['###SITE_LANG###'] = '';
		}

		$markerArray['###IMG###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgFile = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgSize = is_file($imgFile)?getimagesize($imgFile):
		'';

		$markerArray['###IMG_W###'] = $imgSize[0];
		$markerArray['###IMG_H###'] = $imgSize[1];

		$markerArray['###NEWS_WEBMASTER###'] = $this->conf['displayXML.']['xmlWebMaster'];
		$markerArray['###NEWS_MANAGINGEDITOR###'] = $this->conf['displayXML.']['xmlManagingEditor'];

#		$selectConf = Array();
#		$selectConf['pidInList'] = $this->pid_list;
#		// select only normal news (type=0) for the RSS feed. You can override this with other types with the TS-var 'xmlNewsTypes'
#		$selectConf['selectFields'] = 'max(datetime) as maxval';

#		$res = $this->exec_getQuery('tt_news', $selectConf);


#		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		// optional tags
		if ($this->conf['displayXML.']['xmlLastBuildDate']) {
			$markerArray['###NEWS_LASTBUILD###'] = '<lastBuildDate>' . date('D, d M Y H:i:s O', $row['maxval']) . '</lastBuildDate>';
		} else {
			$markerArray['###NEWS_LASTBUILD###'] = '';
		}

		if($this->conf['displayXML.']['xmlFormat'] == 'atom03' ||
		   $this->conf['displayXML.']['xmlFormat'] == 'atom1') {
			$markerArray['###NEWS_LASTBUILD###'] = $this->getW3cDate($row['maxval']);
		}

		if ($this->conf['displayXML.']['xmlWebMaster']) {
			$markerArray['###NEWS_WEBMASTER###'] = '<webMaster>' . $this->conf['displayXML.']['xmlWebMaster'] . '</webMaster>';
		} else {
			$markerArray['###NEWS_WEBMASTER###'] = '';
		}

		if ($this->conf['displayXML.']['xmlManagingEditor']) {
			$markerArray['###NEWS_MANAGINGEDITOR###'] = '<managingEditor>' . $this->conf['displayXML.']['xmlManagingEditor'] . '</managingEditor>';
		} else {
			$markerArray['###NEWS_MANAGINGEDITOR###'] = '';
		}

		if ($this->conf['displayXML.']['xmlCopyright']) {
			if($this->conf['displayXML.']['xmlFormat'] == 'atom1') {
				$markerArray['###NEWS_COPYRIGHT###'] = '<rights>' . $this->conf['displayXML.']['xmlCopyright'] . '</rights>';
			} else {
				$markerArray['###NEWS_COPYRIGHT###'] = '<copyright>' . $this->conf['displayXML.']['xmlCopyright'] . '</copyright>';
			}
		} else {
			$markerArray['###NEWS_COPYRIGHT###'] = '';
		}

		$charset = ($GLOBALS['TSFE']->metaCharset?$GLOBALS['TSFE']->metaCharset:'iso-8859-1');
		if ($this->conf['displayXML.']['xmlDeclaration']) {
			$markerArray['###XML_DECLARATION###'] = trim($this->conf['displayXML.']['xmlDeclaration']);
		} else {
			$markerArray['###XML_DECLARATION###'] = '<?xml version="1.0" encoding="'.$charset.'"?>';
		}

		// promoting TYPO3 in atom feeds, supress the subversion
		$version = explode('.',($GLOBALS['TYPO3_VERSION']?$GLOBALS['TYPO3_VERSION']:$GLOBALS['TYPO_VERSION']));
		unset($version[2]);
		$markerArray['###TYPO3_VERSION###'] = implode($version,'.');

		return $markerArray;
	}






		/**
		 * Plätze Reservieren
		 */
	function booking() {
			# Daten vorbereiten
		if($this->conf['cryptTime'] == 1) # verschlüsseltes entschlüsseln
			list($this->piVars['res'], $this->piVars['uid'], $this->piVars['cinema']) = explode("-", $this->decrypt($this->piVars[crypt]));
		else
			list($this->piVars['res'], $this->piVars['uid'], $this->piVars['cinema']) = explode("-", $this->piVars[crypt]);

		if($this->conf['DEBUG']) {
			$out .= t3lib_div::view_array($this->conf);
			$out .= t3lib_div::view_array(array($this->piVars['res'], $this->piVars['uid'], $this->piVars['cinema']));
		}

		$this->internal['currentRow']['movie'] = $this->piVars['uid'];

		// Formular wird direkt aufgerufen?
		if (!$this->piVars['crypt']) {
		    $out .= "Bitte dieses Formular nicht direkt aufrufen!<br />";
			$out .= "W&auml;hlen Sie einen Film &uuml;ber die Programm&uuml;bericht<br />";
			$out .= "und klicken Sie auf die gew&uuml;nschte Spielzeit!<br/><br/>";
			$out .= $this->pi_linkToPage('Zur Programmübersicht', $this->conf['prgPid']);
			return $out;
		}

		// Spammern das Handwerk legen
		// Res Limit berücksichtigen und das Kino auch.
		if($this->conf['resLimit'] < time()-$this->piVars['res'] || !in_array($this->piVars['cinema'], explode(",", $this->conf['myCinema'])) ) {
			$out .= "W&auml;hlen Sie einen Film &uuml;ber die Programm&uuml;bericht<br />";
			$out .= "und klicken Sie auf die gew&uuml;nschte Spielzeit!<br/><br/>";
			$out .= $this->pi_linkToPage('Zur Programm&uuml;bersicht', $this->conf['prgPid']);

			return $out;
		}

 		$this->oForm = t3lib_div::makeInstance("tx_ameosformidable");
		$this->oForm->init($this, t3lib_extmgm::extPath($this->extKey) . "pi1/form/booking_form.xml");
		$out .= $this->oForm->render();

		return $out;
	}




		/**
		 * Zeit Große Übersicht
		 *
		 * @param string	Welche Art Programm
		 * @param int Startwoche, 0 = aktuelle Woche, 1=nächste Woche, 2=übernächste u.s.w.
		 * @param int Wieviele Wochen?
		 */
	function program($startWeek, $nextWeeks=1, $type="long") {
		$oneDay = 60*60*24;
		$oneWeek = $oneDay * 7;

		$now = mktime();
		switch(strftime("%u", $now)) { # %u = Tag der Woche 1= Montag
			case 1: $wStart = $now - $oneDay*4; break; # Mo
			case 2: $wStart = $now - $oneDay*5; break; # DI
			case 3: $wStart = $now - $oneDay*6; break; # MI
			case 4: $wStart = $now - $oneDay*0; break; # DO
			case 5: $wStart = $now - $oneDay*1; break; # FR
			case 6: $wStart = $now - $oneDay*2; break; # SA
			case 7: $wStart = $now - $oneDay*3; break; # SO
		}
#debug(strftime("%u  %d.%m.%y", $wStart), 'start');

		$wStart += $startWeek*$oneWeek; #-$oneDay;	# n-te Woche vom jetzigen DO an - 1 tag damit Mittwoch das Ende ist.
		if(strftime("%u", $now) != 3) {
			$wStart -= $oneDay;
		}
#debug(strftime("%u  %d.%m.%y $startWeek", $wStart), 'start');

		$wStart = mktime ( 0, 0, 0, strftime("%m", $wStart), strftime("%d", $wStart),strftime("%Y", $wStart)); # Auf 0:00 Uhr setzten!
		$wStart_tmp = $wStart; # für "kein progamm" zwischenspeichern

			# Programm vorzeitig wechseln
		if(mktime() + $this->conf['switchPrgBevore']*60*60 >= $wStart+$oneWeek) {
			$wStart += $oneWeek;
#			debug($this->conf['switchPrgBevore'], "sw");
		}

		$wEnd   = $wStart + $oneWeek*$nextWeeks;
#debug(strftime("%d.%m.%y", $wStart).'-'.strftime("%d.%m.%y", $wEnd));

		if($startWeek == 0) {
			if ($type != "RSS") {
				$items[] = "<h1>Programm ab ".strftime($this->conf['timeFormat'], $wStart)."</h1>";
			}
			$whereClause   .= 'AND date >= '.$wStart.' AND date < '.($wEnd);
		} else {
			if ($type != "RSS") {
				$items[] = "<h2>Vorschau ab ".strftime($this->conf['timeFormat'], $wStart)."</h2>";
				#." bis ".strftime($this->conf['timeFormat'], $wEnd-1)."</h2>";
			}
			$whereClause .= ' AND (date = 0 OR (date >= '.$wStart.' AND date < '.$wEnd.'))';
		}
		$whereClause .= " AND cinema IN (".$this->ff['def']['cinema'].")";
		if($type=="special") {
			$whereClause .= " AND showtype IN (".$this->ff['def']['special'].")";
		}

#		$sortBy = $this->ff['def']['sortBy'];
		$sortBy = "cinema,date,sorting ASC";

			// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_tmdcinema_program', 0, $whereClause,$mm_cat='',$groupBy, $sortBy);

			// Make list table rows
		$items=array();
		$noDate=array();
#		$wStart = -1;
		$cinemaOrder = explode(",", $this->ff['def']['cinema']);

		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$all[] = $this->internal['currentRow'];
		}

		if (count($all)  == 0) {  /* Es gibt noch kein Programm */
			if($this->ff['def']['previewNotice'])
				$out = sprintf($this->ff['def']['previewNotice'], strftime($this->conf['timeFormat'], $wStart+$oneDay));
			else
				$out = sprintf($this->conf['previewNotice'], strftime($this->conf['timeFormat'], $wStart+$oneDay));

			return $out;
		}
		#return "Das Programm ab ".strftime($this->conf['timeFormat'], $wStart)." ist noch nicht bekannt.";

		foreach($cinemaOrder as $cinema) {
			foreach($all as $this->internal['currentRow']) {
				if($this->internal['currentRow']['cinema'] == $cinema) { # Sammelüberschrift
					if($wStart != $this->internal['currentRow']['date'] && $type != 'special'/* && $type != 'RSS'*/) {
						$wStart = $this->internal['currentRow']['date'];

						if($wStart > 0 && $this->conf['showWeekDate'] != 0 ) {
							if ($type != "RSS")
								$items[] = '<h2>'.((!$startWeek)?"Programm": "")." ab ".strftime($this->conf['timeFormat'], $wStart)."</h2>";
						} elseif($wStart == 0) {
							$noDate[] = '<h2>Demnächst</h2>';
						}
					}

						# Film ausgeben
					switch($type) {
						case 'short':
							if($wStart > 0) {
								$items[] = $this->substituteMarkers("PROGRAMM_SHORTVIEW");
							} else {
								$noDate[] = $this->substituteMarkers("PROGRAMM_SHORTVIEW");
							}
						break;
						case 'special':
							$items[] = $this->substituteMarkers("PROGRAMM_SPECIAL");
						break;
						case 'RSS':
							$items[] = $this->substituteMarkers("PROGRAMM_RSS2_ITEM");
						break;
						default:
							if($wStart > 0) {
								$items[] = '<a name="'.$this->prefixId."-".$this->internal['currentRow']['uid'].'"></a>'.
									$this->substituteMarkers("PROGRAMM_FULLVIEW");
							} else {
								$noDate[] = '<a name="'.$this->prefixId."-".$this->internal['currentRow']['uid'].'"></a>'.
									$this->substituteMarkers("PROGRAMM_FULLVIEW");
							}
					} # END switch

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
			}

		if($type == 'RSS') {
			$out = nl2br(htmlentities($prg));
		} else {
			$out .= '<div class="'.$this->pi_getClassName($type.'Program').'">';
			$out .= $prg;
			$out .= '</div>';
		}

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
				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_RATING']);
					return $out;
					}
			break;
			case 'movie_fskTooltip':
				$out = $this->film->ratingTooltip;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_RATINGTOOLTIP']);
					return $out;
				}
			break;

			/* kommt aus anderer Tabelle */
			case 'movie_distributor':
				$out = $this->film->distibutor;

				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_DISTRIBUTOR']);
					return $out;
				}
			break;
			case 'movie_fbw':
				$field = $this->film->fbw;
				for($i = 0; $i < 2; $i++)
					if ($field & pow(2,$i))
						$res .= $this->conf['fbw.'][$i]." ";

				if($field) {
					$out = $this->cObj->wrap(trim($res), $this->conf[$this->ff['wrap.']['def']['mode'].'.']['MOVIE_FBW']);
					return $out;
				}
			break;


			/* kommt aus der Film-Tabelle */
			case 'movie_title':
				$out = $this->cObj->wrap($this->film->titel, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_TITLE']);
				return 	$out;
			break;
			case 'movie_originaltitle':
				$out = $this->film->originaltitle;
				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_TITLE_ORIGINAL']);
					return 	$out;
					}
			break;
			case 'movie_subtitle':
				$out = $this->film->short;
				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_TITLE_SHORT']);
					return $out;
					}
			break;
			case 'movie_subtitle_first':
				if($this->film->short)
					$out = $this->film->short;
				else
					$out = $this->film->titel;

				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_TITLE_SHORT']);
					return $out;
					}
			break;
			case 'movie_image':
				# Für welchen Bereich?
				switch($this->ff['def']['mode']) {
					case 'shortView': 	$this->conf['image.'] = $this->conf['listViewShort.']; 	break;
					case 'longView':	$this->conf['image.'] = $this->conf['listViewLong.'];	break;
					case 'singleView':	$this->conf['image.'] = $this->conf['imageSingle.'];	break;
					case 'special':		$this->conf['image.'] = $this->conf['imageSpecial.']; 	break;
				}


				if($this->film->poster) {
					$temp = explode(',', $this->film->poster); # mehrere Poster?

					if($this->conf['image.']['file'] == 'GIFBUILDER') {
						$this->conf['image.']['file.']['10.']['file'] = $this->uploadPath.$temp[rand(0,count($temp)-1)];
					} else {
						$this->conf['image.']['file'] = $this->uploadPath.$temp[rand(0,count($temp)-1)];
						$this->conf['image.']['file.']['width'] = $this->conf['image.']['file.']['width'];
					}
				} else { // Media File als Alternative
					if($this->conf['image.']['file'] == 'GIFBUILDER') {
						$this->conf['image.']['file.']['10.']['file'] = $this->uploadPath.$this->getFieldContent('movie_media-random');

					if($this->conf['image.']['file.']['10.']['file'] == '')
						$this->conf['image.']['file.']['10.']['file'] = $this->conf['dummyPoster'];
					} else {
						$this->conf['image.']['file'] = $this->uploadPath.$this->getFieldContent('movie_media-random');
					}
				}

				if($this->conf['image.']['file.']['10.']['file'] == 'uploads/tx_tmdmovie/') {
					$this->conf['image.']['file.']['10.']['file'] = $this->conf['dummyPoster'];
				}

				$this->conf['image.']['altText'] = $this->film->titel;
				$out = $this->cObj->IMAGE($this->conf['image.']);
				$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_IMAGE']);
				return $out;
			break;
			case 'movie_time':
				$out = $this->film->length;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_TIME']);
					return $out;
				}
			break;
			case 'movie_www':
				$out = $this->film->web;
				if($out) {
					$out =  $this->pi_linkToPage($this->pi_getLL("website", "web"), $this->film->web, "_blank");
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_WWW']);
					return $out;
				}
			break;
			case 'movie_youtube':
				$out = $this->film->youtube;
				if($out) {
					$out = $this->pi_linkToPage($this->pi_getLL("youtube", "_youtube_"), $this->film->youtube, "_blank");
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_YOUTUBE']);
					return $out;
				}
			break;
			case 'movie_start':
				$out = $this->film->releasedate;
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_RELEASEDATE']);
					return $out;
				}
			break;
			case 'movie_format':
				switch($this->film->screenformat) {
				  case 1: $out = $this->pi_getLL("WideScreen"); break;
				  case 2: $out = $this->pi_getLL("CinemaScope"); break;
				  case 3: $out = $this->pi_getLL("Normal"); break;
				  default: $out = "";
				  }
				if($out) {
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_FORMAT']);
					return $out;
				}
			break;
			case 'movie_sound':
				$out = explode(" ", $this->film->sound);
				$temp = explode(",", $this->conf['supportedSound']);

				foreach ($temp as $key=>$value)
					{
				    if (!in_array($value,$out))
				    	{
				        unset($temp[$key]);
				    	}
					}

				$out = implode(" ", $temp);

				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_SOUND']);
					return $out;
					}
			break;
			case 'movie_summary':
				$out = $this->film->summary;
				if($out)
					{
					$out = $this->pi_RTEcssText($out);
					$out = $this->cObj->stdWrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_DESCRIPTION']);
					return $out;
					}
			break;
			case 'movie_summary_short':
				$out = $this->film->summary;
				if($out)
					{
					$out = $this->cObj->stdWrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_DESCRIPTION_SHORT.']);
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_DESCRIPTION_SHORT']);

	#				return $this->pi_RTEcssText($out);
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

				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_DIRECTOR']);
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

				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_PRODUCER']);
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

				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_ACTOR']);
					return $out;
					}
			break;
			case 'movie_genre':
				$out = $this->film->genre;
				if($out)
					{
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['MOVIE_GENRE']);
					return $out;
					}
			break;
			case 'movie_media-1':
			case 'movie_media-2':
			case 'movie_media-3':
			case 'movie_media-4':
			case 'movie_media-5':
			case 'movie_media-random':
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

				$this->conf['media.']['file'] = 'uploads/tx_tmdmovie/'.$pic;
				$out = $this->cObj->IMAGE($this->conf['media.']);

				return $out;
			break;

			case 'version3d':
				if($this->film->version3D || $this->internal['currentRow']['3d'] ) {
					return $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['VERSION3D']);
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
			case "date":
				$out = strftime($this->conf['timeFormat'], $this->internal['currentRow']['date']);
				$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode']]['PRG_DATE']);
				return $out;
			break;
			case "program":
				$out = $this->cObj->wrap($this->buildTimeTable(), $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE']);
				return $out;
			break;
			case 'info':
				$out = $this->internal['currentRow']['info'];
				if($out)
					{
#debug($this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_INFO']);

					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_INFO']);
					return $out;
					}
			break;
			case 'info2':
				$out = $this->internal['currentRow']['info2'];
				if($out)
					{
					$out = $this->cObj->wrap($this->pi_RTEcssText($out), $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_INFO2']);
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
					$out = $this->cObj->wrap($out, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_SHOWTYPE']);
					return $out;
				}
			break;
			case 'week':
				$out = $this->cObj->wrap($this->internal['currentRow']['week'], $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_WEEK']);
				return 	$out;
			break;
			case 'cinema':
				if(!$this->adrCache[$this->internal['currentRow'][$fN]]) {
					$this->adrCache[$this->internal['currentRow'][$fN]] = $this->pi_getRecord("tt_address", $this->internal['currentRow'][$fN]);
				}

				$out = $this->cObj->wrap($this->adrCache[$this->internal['currentRow'][$fN]]['name'], $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_THEATRE']);

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

			# Werte auslesen
		$this->ff['def']['mode']  		= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mode', 			's_DEF');
		$this->ff['def']['noRes'] 		= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disableReserved', 's_DEF');
		$this->ff['def']['previewMin'] 	= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'previewMin',		's_DEF');
		$this->ff['def']['previewMax'] 	= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'previewMax', 		's_DEF');
		$this->ff['def']['previewNotice'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'previewNotice',	's_DEF');

		$this->ff['def']['cinema'] 		= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cinema',	 		's_DEF');
		$this->ff['def']['special'] 	= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'special',	 		's_DEF');
		$this->conf['pageReserve']	= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pageReserve', 		's_DEF');

		if(empty($this->conf['image.']['file.']['width']))
			$this->conf['image.']['file.']['width']	= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 's_image');
		$this->ff['image']['colums']		= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'colums', 		's_image');
		$this->ff['image']['clickEnlarge'] 	= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'clickEnlarge', 's_image');
		$this->conf['linkImagePage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkImagePage','s_image');

			# besondere Vorrangregelungen
		if($this->conf['mode'] == 'RSS') {
			$this->ff['def']['mode']  	= 'RSS';
			$this->ff['def']['cinema']	= $this->conf['myCinema'];
		}
	}



	function initTemplate() {
		$this->template = $this->cObj->fileResource($this->conf['template']);
		#debug($this->template, $this->conf['template']);
	}



	function substituteMarkers($subPart, $subpartArray="")
		{
		$template = $GLOBALS['TSFE']->cObj->getSubpart($this->template, "###".$subPart."###");

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

		$markerArray['###ANCHOR###']				= $this->getFieldContent('anchor');

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
		 * Hier wird die Spielzeitentabelle zusammengebastelt
		 *
		 * @return string Time Table
		 */
	function buildTimeTable()
		{

		if($this->internal['currentRow']['program'])
			{
				# Uhr auf 0 Uhr stellen!
				# Erster Programmtag
			$theDay = $this->internal['currentRow']['date'];
			$theDay = mktime ( 0, 0, 0, strftime("%m", $theDay),strftime("%d", $theDay), strftime("%Y", $theDay));

			if( (time() > $theDay ) && 	( time() < $theDay + 7*60*60*24 ) )
				{
				$todaysNr = strftime("%u", time()) + 3;
				if($todaysNr > 6) $todaysNr = $todaysNr%7;
				}
			else
				{
				$todaysNr = -1;
				}

				# tHead
			$head = '<thead><tr>';
			for($i=0; $i<7; $i++)
				{
				$time[$i] = $theDay+60*60*24*$i;
				$time[$i] = $this->checkSummerWinterBug($time[$i]); /* Sommerzeit-Bug */

				if($i == $todaysNr)
					{
					$head .= '<th style="background-color:'.$this->conf['todaysColor'].';">';

					if(date("d", $time[$i]) == date("d", time()) && $this->conf['todayStyle'] == 'today')
						$head .= $this->cObj->wrap($this->pi_getLL("today"), $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TH']);

					else
						$head .= $this->cObj->wrap(strftime($this->conf['tableTime'], $time[$i]), $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TH']);

					$head .= '</th>';
					}
				else
					{
					$head .= '<th align="center">';
					$head .= $this->cObj->wrap(strftime($this->conf['tableTime'], $time[$i]), $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TH']);
					$head .= '</th>';
					}
				}
			unset($this->correctDate); /* Sommerzeit-Bug  - damit alle Filme berücksichtigt werden */

			$head .= '</tr></thead>';

				# tBody
			$temp = $this->internal['currentRow']['program'];
			$temp = explode("\n", trim($temp));

			# Mit Zeit verlinken
			$i=0; $n =0;
			foreach($temp as $row => $val)
				{
				#if(strlen($temp)>0) debug(trim($temp));
				
				$temp[$i] = explode("|", $val);

				foreach($temp[$i] as $key1 => $timeString) {
					$timeString = trim($timeString); # Zeilenende bereinigen

					if(preg_match( '/[0-9]?[0-9]:[0-9][0-9]/m', $timeString)) /* Exchte Uhrzeit */
						{
						list($theHour, $theMinute) = explode(":",$timeString);

						$theTime = mktime((int)$theHour, (int)$theMinute, 0, strftime("%m", $time[$n]), strftime("%d", $time[$n]), strftime("%Y", $time[$n]));

						$linkconf = array(
				    		 "title" => $this->pi_getLL("howtoReserve"),
						     "parameter" => $this->conf['pageReserve'],
						     );

						if($this->conf['cryptTime'] == 1)
							$linkconf["additionalParams"] = "&".$this->prefixId."[crypt]=".$this->encrypt($theTime."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema']);
						else
							$linkconf["additionalParams"] = "&".$this->prefixId."[crypt]=".$theTime."-".$this->internal['currentRow']['movie']."-".$this->internal['currentRow']['cinema'];

						if($this->conf['noRes'] == 'pageLinkOnly') unset($linkconf['additionalParams']);
						if($this->conf['noRes'] == 'ticket')
							{
							$linkconf = $this->ticketLink($theTime);
#							debug($linkconf, $theTime);
							}
						$link = $this->cObj->typoLink($timeString, $linkconf);

							# Reservierungsschluss?
						$thisDay = time();
						$thisDay = mktime ( 0, 0, 0, strftime("%m", $thisDay),strftime("%d", $thisDay), strftime("%Y", $thisDay));

							# z.B. 5 Stunden vorher = t-60*60*5
						if(	(time() > $theTime-60*60*$this->conf['resLimit']) ||
							$this->ff['def']['noRes'] ||
							$this->conf['noRes'] == '1' ||
							$this->internal['currentRow']['nores'] )
							{
							#debug(strftime("%H:%M", $theTime));
							$temp[$i][$key1] = $timeString;
							}
						else
							{
							$temp[$i][$key1] = $link;
							}
						}
					elseif(preg_match( '/[0-9]?[0-9]\.[0-9][0-9]/m', $timeString)) # keine Reservierung
						{
						$temp[$i][$key1] = str_replace(".", ":", $temp[$i][$key1]);
						}
					else # leere Zelle
						{
						$temp[$i][$key1] = $this->conf['emptyTable'];
						}

					$n++;
					}
				$i++;
				$n = 0;
				}


			# Tabellenzeilen zusammenbauen
			$i=0;
			foreach($temp as $key => $val)
				{
				$tmp[$i] = "<tr>";
				foreach($val as $key1 => $val1)
					{
					$val1 = $this->cObj->wrap($val1, $this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TD']);
					if($key1 == $todaysNr) {# Heute!
						if(isset($this->conf['todaysColor'])) { # eigene Hintergundfarbe
							$tmp[$i] .= '<td style="'.$this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TD_STYLE'].' background-color: '.$this->conf['todaysColor'].';">'.$val1.'</td>';
						} else { 
							$tmp[$i] .= '<td style="'.$this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TD_STYLE'].';">'.$val1.'</td>';
						}
					} else {
						$tmp[$i] .= '<td style="'.$this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_TD_STYLE'].'">'.$val1.'</td>';
					}

					}
				$tmp[$i] .= "</tr>";
				$i++;
				}

			$temp = '<table class="program" style="'.$this->conf['wrap.'][$this->ff['def']['mode'].'.']['PRG_TIMETABLE_STYLE'].'">'.$head."<tbody>".implode(chr(10),$tmp).'</tbody></table>';
			}
		else
			{ # Spielplan nicht bekannt
			$temp = "<br /><b>";
			if($this->internal['currentRow']['date'])
				{
				$temp .= 'Ab '.$this->getFieldContent('date').'<br />';
				}
			$temp .= $this->pi_getLL('timeNN');
			$temp .= '</b>';
			}

		return $temp;
		}



		/**
		 * The Dates of the day are calculated wrong if the time is changed for Summer/winter time
		 *
		 * @param timestamp UNIX timestamp to be checked
		 * @return timestamp UNIX timestanp corrected, if needed
		 */
	function checkSummerWinterBug($theDay) {
		if($this->correctDate)
			return $theDay+$this->correctDate;

		$oneDay = 24*60*60;
		$oneHour = 60*60;

		if(date("I", $theDay-$oneDay) != date("I", $theDay)) {
			if(!date("I", $theDay)) { 	# Sommer -> Winter
				$this->correctDate = $oneHour*24;
			}
		}

		return $theDay+$this->correctDate;
	}



		/**
		 * Reservierung für .ticket System anzapfen
		 */
	function ticketLink($theTime)
		{
#debug(strftime("%H:%M %d.%m", $theTime), "ticket");
		$oneWeek = 7*24*60*60;

		$today0 = mktime ( 0, 0, 0, strftime("%m", time()),strftime("%d", time()), strftime("%Y", time()));
		$dif = $theTime-$today0;

		$weekCount =  (int) ($dif / $oneWeek);

		$link  = $this->conf['noRes.']['server'];
		$link .= "?Week=".$weekCount;
		$link .= "&UserCenterID=".$this->conf['noRes.']['UserCenterID'];

		$link .= "&SiteID=".$this->conf['noRes.']['SiteID.'][$this->getFieldContent('cinemaID')];

		$out['parameter'] = $this->cObj->substituteMarker($this->conf['noRes.']['window.']['parameter'], '###URL###', $link);

		return $out;
		}

/*
		 * Adresse als Session speichern
		 * wird f�r den Link zum Newsletter ben�tigt!
		 *
	function saveAddressToSession()
		{
		$my_vars = $GLOBALS["TSFE"]->fe_user->getKey('ses','tx_myextension');

		you then will add your own values the same way:

		$my_vars['somevalue'] = "Hello World";

		Again, Don't forget to save the data to the session in the end:

		$GLOBALS["TSFE"]->fe_user->setKey('ses','tx_myextension',$my_vars);


		$fields_values = array(
			"salutation" => $this->piVars[salutation],
			"title" => $this->piVars[title],
			"firstname" => $this->piVars[firstname],
			"lastname" => $this->piVars[lastname],
			"street" => $this->piVars[street],
			"streetnr" => $this->piVars[streetNr],
			"zip" => $this->piVars[zip],
			"city" => $this->piVars[city],
			"country" => $this->piVars[country],
			"email" => $this->piVars[email],
			"fon" => $this->piVars[fon],
			"fax" => $this->piVars[fax],
			);

		$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->prefixId, $fields_values);
		}

 */


	} /* END of CLASS */



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi1/class.tmd_cinema_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tmd_cinema/pi1/class.tmd_cinema_pi1.php']);
}
?>