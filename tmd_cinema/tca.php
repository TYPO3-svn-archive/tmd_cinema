<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_tmdcinema_program"] = array (
	"ctrl" => $TCA["tx_tmdcinema_program"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,date,info,info2,nores,movie,program,3d,boxoffice"
	),
	"feInterface" => $TCA["tx_tmdcinema_program"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		
		/*-------------------
		"temp_title" => Array (	# solange ich über TCA->ctrl nicht eine fremde tabelle ansprechen kann	
			"exclude" => 0,		
			"label" => "Titel", 		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
#				"eval" => "required",
			)
		),
		-------------------*/
		
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.date",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"week" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.week",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"min" => "0",
				"max" => "20",
				"eval" => "int",
				"default" => "1",
			)
		),
        "showtype" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.showtype",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_tmdcinema_showtype",    
                "foreign_table_where" => " AND (tx_tmdcinema_showtype.pid=###CURRENT_PID### OR tx_tmdcinema_showtype.pid = ###STORAGE_PID### ) ORDER BY tx_tmdcinema_showtype.sorting",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "info" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.info",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "2",
			)
		),
		"info2" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.info2",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",
                "rows" => "5",
                "wizards" => Array(
                    "_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
            )
        ),
		"3d" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_program.3d",        
            "config" => Array (
                "type" => "check",
            )
        ),
    	"nores" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.nores",		
			"config" => Array (
				"type" => "check",
			)
		),

		"movie" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.movie",		
			"config" => Array (
			    'type' => 'group',    
				"internal_type" => "db",
			    'foreign_table' => "tx_tmdmovie_movie",
			    'allowed' => "tx_tmdmovie_movie",    
				"eval" => "required",
			    'size' => 1,    
			    'minitems' => 1,
			    'maxitems' => 1,
			    'wizards' => array(
			        'suggest' => array(    
			            'type' => 'suggest',
			        ),
			    ),
			),
		),

		
		"cinema" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.cinema",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tt_address",    
				"foreign_table_where" => " AND tt_address.PID = ###STORAGE_PID### ORDER BY tt_address.company",
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
		"program" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_program.program",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "4",
				"default" => "",	
				"wizards" => Array(
					"_PADDING" => 2,
					"example" => Array(
						"title" => "Program Wizard:",
						"type" => "script",
						"notNewRecords" => 1,
						"icon"   => t3lib_extMgm::extRelPath("tmd_cinema")."tx_tmdcinema_program_program/wizard_icon.png",
						"script" => t3lib_extMgm::extRelPath("tmd_cinema")."tx_tmdcinema_program_program/index.php",
					),
				),
			)
		),
	"boxoffice" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_program.boxoffice",        
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "4",
				"default" => "",	
			)
		),
		
	),
	"types" => array (
		"0" => array("showitem" => "--div--;Programm,hidden;;1;;1-1-1, temp_title, movie, date, week, cinema, showtype, info,3d,nores, program,
									--div--;Besondere Veranstaltungshinweise,info2;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_tmdcinema/rte/],
									--div--;Umsatzzahlen,boxoffice
		")
	),
# "types" => array (
# "0" => array("showitem" => "--div--;Produkt,sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, text;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], icon, mood, related, detail, multicolorimage,--div--;ProduktFinder,artikelnr, temphigh, templow, prodcategory, sports, sex,sortgroup,sizes,sizecorrect,lengthcorrect"),
# ),
	
	
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);






$TCA["tx_tmdcinema_booking"] = array (
	"ctrl" => $TCA["tx_tmdcinema_booking"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,name,email,seats,note,movie,timedate,sentmail,editedby"
	),
	"feInterface" => $TCA["tx_tmdcinema_booking"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"email" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"seats" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.seats",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"note" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.note",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
				"readOnly" => 1,
			)
		),
		"movie" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.movie",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"timedate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.timedate",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),



		"cinema" => Array (		
			"exclude" => 1,		
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.cinema",		
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tt_address",    
//				"foreign_table_where" => " AND (tt_address.PID = ###PAGE_TSCONFIG_ID### OR tt_address.PID = ###CURRENT_PID###) ORDER BY tt_address.name",
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
		),

		"sentmail" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_booking.sentmail",		
			"config" => Array (
				"type" => "text",
				"readOnly" => 1,  
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, email, seats, note, movie, cinema, timedate, sentmail")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_tmdcinema_showtype"] = array (
    "ctrl" => $TCA["tx_tmdcinema_showtype"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,showtype,link"
    ),
    "feInterface" => $TCA["tx_tmdcinema_showtype"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => array (
                'type' => 'check',
                'default' => '0'
            )
        ),
        "showtype" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_showtype.showtype",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required",
            )
        ),
        "link" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tmd_cinema_showtype.link",        
            "config" => Array (
                "type" => "input",
                "size" => "15",
                "max" => "255",
                "checkbox" => "",
                "eval" => "trim",
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    )
                )
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, showtype, link")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);


$TCA["tx_tmdcinema_spamlog"] = array (
    "ctrl" => $TCA["tx_tmdcinema_spamlog"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "ip,sender,recipient,msg,spam,showdata"
    ),
    "feInterface" => $TCA["tx_tmdcinema_spamlog"]["feInterface"],
    "columns" => array (
        "ip" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog.ip",        
            "config" => Array (
                "type" => "none",
            )
        ),
        "sender" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog.sender",        
            "config" => Array (
                "type" => "none",
            )
        ),
        "recipient" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog.recipient",        
            "config" => Array (
                "type" => "none",
            )
        ),
        "msg" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog.msg",        
            "config" => Array (
                "type" => "none",
            )
        ),
		'spam' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog.spam',        
            'config' => array (
                'type' => 'check',
            )
        ),
        'showdata' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:tmd_cinema/locallang_db.xml:tx_tmdcinema_spamlog.showData',        
            'config' => array (
                'type' => 'none',
            )
        ),
        
    ),
    "types" => array (
        "0" => array("showitem" => "ip;;;;1-1-1, sender, recipient, msg, spam, showdata")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

?>