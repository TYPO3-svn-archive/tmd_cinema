#
# Table structure for table 'tmd_cinema_program'
#
CREATE TABLE tx_tmdcinema_program (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
    t3ver_id int(11) DEFAULT '0' NOT NULL,
    t3ver_wsid int(11) DEFAULT '0' NOT NULL,
    t3ver_label varchar(30) DEFAULT '' NOT NULL,
    t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
    t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
    t3ver_count int(11) DEFAULT '0' NOT NULL,
    t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
    t3_origuid int(11) DEFAULT '0' NOT NULL,

	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,

	week int(11) DEFAULT '0' NOT NULL,
	date int(11) DEFAULT '0' NOT NULL,
	info text NOT NULL,
	info2 text NOT NULL,
	nores tinyint(3) DEFAULT '0' NOT NULL,
	cinema blob NOT NULL,
	showtype blob NOT NULL,
	movie blob NOT NULL,
	program text NOT NULL,
    features int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tmd_cinema_booking'
#
CREATE TABLE tx_tmdcinema_booking (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	name tinytext NOT NULL,
	email tinytext NOT NULL,
	seats int(11) DEFAULT '0' NOT NULL,
	note text NOT NULL,
	timedate int(11) DEFAULT '0' NOT NULL,
	movie tinytext NOT NULL,
	cinema int(11) DEFAULT '0' NOT NULL,
	sentmail text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tmd_cinema_showtype'
#
CREATE TABLE tx_tmdcinema_showtype (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    showtype tinytext NOT NULL,
    link tinytext NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);


#
# Table structure for table 'tx_tmdcinema_spamlog'
#
CREATE TABLE tx_tmdcinema_spamlog (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    ip tinytext,
    sender tinytext,
    recipient tinytext,
    msg tinytext,
    spam tinyint(3) DEFAULT '0' NOT NULL,
    showdata tinytext,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);