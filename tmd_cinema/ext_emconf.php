<?php

########################################################################
# Extension Manager/Repository config file for ext "tmd_cinema".
#
# Auto generated 30-12-2009 22:22
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
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
	'_md5_values_when_last_written' => 'a:70:{s:9:"ChangeLog";s:4:"76c9";s:10:"README.txt";s:4:"9fa9";s:35:"class.tx_tmd_cinema_configcheck.php";s:4:"336b";s:34:"class.tx_tmd_cinema_prolongate.php";s:4:"8d3f";s:12:"develope.txt";s:4:"5b88";s:21:"ext_conf_template.txt";s:4:"1c02";s:12:"ext_icon.gif";s:4:"ca8a";s:23:"ext_icon_prg_folder.gif";s:4:"e77c";s:17:"ext_localconf.php";s:4:"267b";s:14:"ext_tables.php";s:4:"2b5d";s:14:"ext_tables.sql";s:4:"a25f";s:15:"flexform_ds.xml";s:4:"1b1e";s:16:"folder Kopie.png";s:4:"2fcf";s:10:"folder.png";s:4:"2fcf";s:27:"icon_tmd_cinema_booking.gif";s:4:"475a";s:33:"icon_tmd_cinema_program Kopie.png";s:4:"b04c";s:27:"icon_tmd_cinema_program.png";s:4:"dca3";s:36:"icon_tmd_cinema_program__h Kopie.png";s:4:"34ff";s:30:"icon_tmd_cinema_program__h.png";s:4:"009c";s:28:"icon_tmd_cinema_showtype.gif";s:4:"401f";s:16:"locallang_db.xml";s:4:"b519";s:17:"locallang_tca.xml";s:4:"5df6";s:7:"tca.php";s:4:"a75b";s:14:"doc/manual.sxw";s:4:"4fa0";s:19:"doc/wizard_form.dat";s:4:"9734";s:20:"doc/wizard_form.html";s:4:"5bd7";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"5bdd";s:13:"mod1/hook.txt";s:4:"6138";s:14:"mod1/index.php";s:4:"d1e4";s:19:"mod1/index.php.mine";s:4:"2720";s:21:"mod1/index.php.r26797";s:4:"efcf";s:21:"mod1/index.php.r28047";s:4:"e313";s:18:"mod1/locallang.xml";s:4:"6982";s:22:"mod1/locallang_mod.xml";s:4:"8342";s:19:"mod1/moduleicon.gif";s:4:"ca8a";s:21:"mod2/--moduleicon.gif";s:4:"ca8a";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"3195";s:15:"mod2/folder.png";s:4:"7681";s:14:"mod2/index.php";s:4:"3fb5";s:18:"mod2/locallang.xml";s:4:"d277";s:22:"mod2/locallang_mod.xml";s:4:"f3d2";s:19:"mod2/moduleicon.png";s:4:"91ba";s:28:"pi1/class.tmd_cinema_pi1.php";s:4:"bf38";s:15:"pi1/history.txt";s:4:"9e27";s:17:"pi1/locallang.xml";s:4:"f455";s:20:"pi1/reserve_form.xml";s:4:"c0f7";s:25:"pi1/form/booking_form.xml";s:4:"62ff";s:27:"pi1/form/form_locallang.xml";s:4:"ad6c";s:28:"pi1/res/cinema_template.html";s:4:"e8bc";s:17:"pi1/res/dummy.jpg";s:4:"f643";s:24:"pi1/res/rss_newsfeed.xml";s:4:"9e6b";s:24:"pi1/static/editorcfg.txt";s:4:"4814";s:20:"pi1/static/setup.txt";s:4:"8aa8";s:33:"pi1/static/rss_feed/constants.txt";s:4:"e4ee";s:29:"pi1/static/rss_feed/setup.txt";s:4:"31bf";s:28:"pi2/class.tmd_cinema_pi2.php";s:4:"eb29";s:17:"pi2/locallang.xml";s:4:"37be";s:22:"pi2/form/book_form.xml";s:4:"701b";s:27:"pi2/form/form_locallang.xml";s:4:"ad6c";s:25:"pi2/res/emailTemplate.txt";s:4:"7e5c";s:24:"pi2/static/editorcfg.txt";s:4:"5685";s:20:"pi2/static/setup.txt";s:4:"5100";s:38:"tx_tmdcinema_program_program/clear.gif";s:4:"cc11";s:37:"tx_tmdcinema_program_program/conf.php";s:4:"1113";s:38:"tx_tmdcinema_program_program/index.php";s:4:"1696";s:42:"tx_tmdcinema_program_program/locallang.xml";s:4:"a5d9";s:44:"tx_tmdcinema_program_program/wizard_icon.gif";s:4:"6886";s:44:"tx_tmdcinema_program_program/wizard_icon.png";s:4:"163b";}',
	'suggests' => array(
	),
);

?>