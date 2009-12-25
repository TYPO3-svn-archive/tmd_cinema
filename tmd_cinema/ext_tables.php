<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_tmdcinema_program"] = array (
    "ctrl" => array (
        'title' => 'LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program',
        'label' => 'temp_title',
	'label_alt' => 'week',
	'label_alt_force' => 1,


/*		'label' => 'movie',
		'label_alt' => 'tx_tmdmovie_movie',
		'label_alt_force' => 1,
*/
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, date, info,info2,nores, movie, cinema, program, 3d",
    )
);


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

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

# Programmseiten markieren
$TCA['pages']['columns']['module']['config']['items'][] = Array('LLL:EXT:'.$_EXTKEY.'/locallang_tca.xml:pages.module.I.5', 'cinema_prg');

# Wir blenden die Standard Felder layout,select_key,pages,recursive  von Plugins aus
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
# ,pages,recursive

# Dafür blenden wir das tt_content Feld pi_flexform ein
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

# Wir definieren die Datei, die unser Flexform Schema enth?lt
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds.xml');

t3lib_extMgm::addPlugin(array('LLL:EXT:tmd_cinema/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Cinema Program');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/rss_feed/','Cinema Program RSS');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:tmd_cinema/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi2/static/','Book Reservation');


	/* BE Modul */
if (TYPO3_MODE == 'BE')    {
    t3lib_extMgm::addModule('web','tmdcinemaM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
    		// add folder icon
	$ICON_TYPES['cinema_prg'] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon_prg_folder.gif');
}
?>