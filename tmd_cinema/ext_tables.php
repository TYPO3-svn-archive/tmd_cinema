<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

include_once(t3lib_extMgm::extPath('tmd_cinema').'class.tx_tmd_cinema_title.php');

$TCA["tx_tmdcinema_program"] = array (
    "ctrl" => array (
		'title' => 'LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program',

		'label' => 'movie',
		'label_userFunc' => 'tx_tmd_cinema_title->getRecordTitle',

        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
        'origUid' => 't3_origuid',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'hideAtCopy' => TRUE,
		'dividers2tabs' => TRUE,
        'enablecolumns' => array (
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tmd_cinema_program.png',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, starttime, endtime, date, info,info2,nores, movie, cinema, program, 3d,boxoffice",
    )
);


	// initalize "context sensitive help" (csh)
t3lib_extMgm::addLLrefForTCAdescr('tx_tmdcinema_program','EXT:tmd_cinema/locallang_csh_txtmdcinemaprogram.xml');

$TCA["tx_tmdcinema_booking"] = array (
	"ctrl" => array (
		'title' => 'LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking',
		'label' => 'movie',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY uid DESC",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tmd_cinema_booking.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, name, email, seats, note, movie, timedate, cinema, sentmail",
	)
);


$TCA["tx_tmdcinema_showtype"] = array (
    "ctrl" => array (
        'title' => 'LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_showtype',
        'label' => 'showtype',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => array (
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tmd_cinema_showtype.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, showtype, link",
    )
);

$TCA["tx_tmdcinema_spamlog"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog',        
        'label'     => 'uid',
		'label_alt'	=> 'spam',
		'label_alt_force' => 1,
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate DESC",    
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_tmdcinema_spamlog.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "ip, sender, recipient, msg",
    )
);



	/**
	 * pi1
	 * Programm anzeigen
	 */
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
	# Programmseiten markieren
$TCA['pages']['columns']['module']['config']['items'][] = Array('LLL:EXT:'.$_EXTKEY.'/locallang_tca.xml:pages.module.I.5', 'cinema_prg');
	# Wir blenden die Standard Felder layout,select_key,pages,recursive  von Plugins aus
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
	# Dafür blenden wir das tt_content Feld pi_flexform ein
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
	# Wir definieren die Datei, die unser Flexform Schema enthält
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/pi1/flexform_ds.xml');
t3lib_extMgm::addPlugin(array('LLL:EXT:tmd_cinema/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Cinema Program');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/rss_feed/','Cinema Program RSS');


	/**
	 * pi2
	 * Reservierungen
	 */
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:tmd_cinema/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi2/static/','Book Reservation');




	/**
	 * pi3
	 * Box Office
	 */
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi3', 'FILE:EXT:'.$_EXTKEY.'/pi3/flexform_ds.xml');

t3lib_extMgm::addPlugin(array('LLL:EXT:tmd_cinema/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi3/static/','BoxOffice');





	/* BE Modul */
if (TYPO3_MODE == 'BE')    {
    t3lib_extMgm::addModule('web','txtmdcinemaM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	t3lib_extMgm::addModule('web','txtmdcinemaM2','',t3lib_extMgm::extPath($_EXTKEY).'mod2/');
    
    		// add folder icon
	$ICON_TYPES['cinema_prg'] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon_prg_folder.gif');
}
?>