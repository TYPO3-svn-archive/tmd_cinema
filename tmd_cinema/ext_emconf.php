<?php

########################################################################
# Extension Manager/Repository config file for ext: "tmd_cinema"
#
# Auto generated 25-12-2009 16:18
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Kinoprogramm',
	'description' => 'Kino Programm',
	'category' => 'plugin',
	'author' => 'Christian Tauscher',
	'author_email' => 'cms@media-distillery.de',
	'shy' => '',
	'dependencies' => 'tmd_movie',
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
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:53:{s:9:"ChangeLog";s:4:"76c9";s:10:"README.txt";s:4:"9fa9";s:35:"class.tx_tmd_cinema_configcheck.php";s:4:"336b";s:12:"develope.txt";s:4:"5b88";s:21:"ext_conf_template.txt";s:4:"1c02";s:12:"ext_icon.gif";s:4:"ca8a";s:23:"ext_icon_prg_folder.gif";s:4:"d7bd";s:17:"ext_localconf.php";s:4:"6e14";s:14:"ext_tables.php";s:4:"c52d";s:14:"ext_tables.sql";s:4:"415e";s:15:"flexform_ds.xml";s:4:"d6d8";s:27:"icon_tmd_cinema_booking.gif";s:4:"475a";s:27:"icon_tmd_cinema_program.gif";s:4:"a4f5";s:28:"icon_tmd_cinema_showtype.gif";s:4:"401f";s:16:"locallang_db.xml";s:4:"398f";s:17:"locallang_tca.xml";s:4:"5df6";s:7:"tca.php";s:4:"c0f6";s:28:"pi2/class.tmd_cinema_pi2.php";s:4:"f4fc";s:15:"pi2/history.txt";s:4:"7d40";s:17:"pi2/locallang.xml";s:4:"37be";s:25:"pi2/res/emailTemplate.txt";s:4:"7e5c";s:22:"pi2/form/book_form.xml";s:4:"701b";s:27:"pi2/form/form_locallang.xml";s:4:"ad6c";s:24:"pi2/static/editorcfg.txt";s:4:"5685";s:20:"pi2/static/setup.txt";s:4:"5100";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"c602";s:14:"mod1/index.php";s:4:"efcf";s:18:"mod1/locallang.xml";s:4:"9363";s:22:"mod1/locallang_mod.xml";s:4:"e989";s:19:"mod1/moduleicon.gif";s:4:"ca8a";s:28:"pi1/class.tmd_cinema_pi1.php";s:4:"a72a";s:31:"pi1/class.user_cinema_pi1.php--";s:4:"42c5";s:15:"pi1/history.txt";s:4:"9e27";s:17:"pi1/locallang.xml";s:4:"f455";s:20:"pi1/reserve_form.xml";s:4:"c0f7";s:28:"pi1/res/cinema_template.html";s:4:"e8bc";s:17:"pi1/res/dummy.jpg";s:4:"f643";s:24:"pi1/res/rss_newsfeed.xml";s:4:"9e6b";s:25:"pi1/form/booking_form.xml";s:4:"c535";s:27:"pi1/form/form_locallang.xml";s:4:"ad6c";s:24:"pi1/static/editorcfg.txt";s:4:"4814";s:20:"pi1/static/setup.txt";s:4:"8aa8";s:33:"pi1/static/rss_feed/constants.txt";s:4:"e4ee";s:29:"pi1/static/rss_feed/setup.txt";s:4:"31bf";s:14:"doc/manual.sxw";s:4:"37e6";s:19:"doc/wizard_form.dat";s:4:"de57";s:20:"doc/wizard_form.html";s:4:"d110";s:38:"tx_tmdcinema_program_program/clear.gif";s:4:"cc11";s:37:"tx_tmdcinema_program_program/conf.php";s:4:"1113";s:38:"tx_tmdcinema_program_program/index.php";s:4:"1696";s:42:"tx_tmdcinema_program_program/locallang.xml";s:4:"a5d9";s:44:"tx_tmdcinema_program_program/wizard_icon.gif";s:4:"6886";}',
);

?>