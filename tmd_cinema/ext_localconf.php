<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tmdcinema_program=1
');
t3lib_extMgm::addPageTSConfig('
    # ***************************************************************************************
    # CONFIGURATION of RTE in table "tmd_cinema_program", field "info2"
    # ***************************************************************************************
RTE.config.tx_tmdcinema_program.info2 {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tmd_cinema_pi1 = < plugin.tmd_cinema_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tmd_cinema_pi1.php','_pi1','list_type',1);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_tmdcinema_program = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_tmdcinema_program.CMD = singleView
',43);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tmd_cinema_pi2 = < plugin.tmd_cinema_pi2.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tmd_cinema_pi2.php','_pi2','list_type',0); /* USER-INT */


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tmd_cinema_booking = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi2
	tt_content.shortcut.20.0.conf.tmd_cinema_booking.CMD = singleView
',43);


/**
  *  Enable hook after saving page or content element
  */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:tmd_cinema/class.tx_tmd_cinema_prolongate.php:&tx_tmd_cinema_prolongate';
?>