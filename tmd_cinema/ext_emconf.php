<?php

########################################################################
# Extension Manager/Repository config file for ext: "tmd_cinema"
#
# Auto generated 18-07-2009 21:18
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Cinema',
	'description' => 'Manage the programm for your cinema-screens',
	'category' => 'plugin',
	'author' => 'Christian Tauscher',
	'author_email' => 'cms@media-distillery.de',
	'shy' => '',
	'dependencies' => 'tmd_movie,ameos_formidable,oelib',
	'conflicts' => '',
	'priority' => '',
	'module' => 'tx_tmdcinema_program_program,mod1',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_tmdcinema/rte/',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'tmd_movie' => '',
			'ameos_formidable' => '2.0.0',
			'oelib' => '0.6.2-'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:48:{s:9:"ChangeLog";s:4:"76c9";s:10:"README.txt";s:4:"9fa9";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"3771";s:14:"ext_tables.php";s:4:"0283";s:14:"ext_tables.sql";s:4:"f7db";s:15:"flexform_ds.xml";s:4:"0886";s:27:"icon_tmd_cinema_program.gif";s:4:"475a";s:28:"icon_tmd_cinema_reserved.gif";s:4:"475a";s:28:"icon_tmd_cinema_showtype.gif";s:4:"401f";s:16:"locallang_db.xml";s:4:"a542";s:17:"locallang_tca.xml";s:4:"5df6";s:7:"tca.php";s:4:"ebf7";s:28:"pi1/class.tmd_cinema_pi1.php";s:4:"017c";s:31:"pi1/class.user_cinema_pi1.php--";s:4:"42c5";s:15:"pi1/history.txt";s:4:"9e27";s:17:"pi1/locallang.xml";s:4:"f455";s:20:"pi1/reserve_form.xml";s:4:"c0f7";s:28:"pi1/res/cinema_template.html";s:4:"e8bc";s:17:"pi1/res/dummy.jpg";s:4:"f643";s:24:"pi1/res/rss_newsfeed.xml";s:4:"9e6b";s:24:"pi1/static/editorcfg.txt";s:4:"4814";s:20:"pi1/static/setup.txt";s:4:"82a7";s:33:"pi1/static/rss_feed/constants.txt";s:4:"e4ee";s:29:"pi1/static/rss_feed/setup.txt";s:4:"31bf";s:27:"pi1/form/form_locallang.xml";s:4:"ad6c";s:24:"pi1/form/reservation.xml";s:4:"5c78";s:28:"pi2/class.tmd_cinema_pi2.php";s:4:"79dc";s:15:"pi2/history.txt";s:4:"7d40";s:17:"pi2/locallang.xml";s:4:"37be";s:25:"pi2/res/emailTemplate.txt";s:4:"7e5c";s:24:"pi2/static/editorcfg.txt";s:4:"5685";s:20:"pi2/static/setup.txt";s:4:"9d24";s:22:"pi2/form/book_form.xml";s:4:"2f5d";s:27:"pi2/form/form_locallang.xml";s:4:"ad6c";s:19:"doc/wizard_form.dat";s:4:"79c3";s:20:"doc/wizard_form.html";s:4:"0458";s:38:"tx_tmdcinema_program_program/clear.gif";s:4:"cc11";s:37:"tx_tmdcinema_program_program/conf.php";s:4:"27fe";s:38:"tx_tmdcinema_program_program/index.php";s:4:"1f4a";s:42:"tx_tmdcinema_program_program/locallang.xml";s:4:"a5d9";s:44:"tx_tmdcinema_program_program/wizard_icon.gif";s:4:"1bdc";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"be76";s:14:"mod1/index.php";s:4:"b5e6";s:18:"mod1/locallang.xml";s:4:"75e3";s:22:"mod1/locallang_mod.xml";s:4:"e989";s:19:"mod1/moduleicon.gif";s:4:"8074";}',
	'suggests' => array(
	),
);

?>