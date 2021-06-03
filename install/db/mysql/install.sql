create table if not exists b_iplogicberu_profile
(
	ID int(11) NOT NULL auto_increment,
	NAME varchar(255) NOT NULL,
	ACTIVE char(1) NULL default 'Y',
	SORT int(5) NULL default 100,
	SITE varchar(2) NOT NULL,
	IBLOCK_TYPE varchar(50) NOT NULL,
	IBLOCK_ID int(11) NOT NULL,
	USE_API char(1) NULL default 'Y',
	USE_FEED char(1) NULL default 'Y',
	COMPANY VARCHAR(255) NULL,
	TAX_SYSTEM varchar(14) NULL,
	VAT VARCHAR(6) NULL,
	BASE_URL varchar(100) NULL,
	CLIENT_ID varchar(255) NULL,
	COMPAIN_ID varchar(100) NULL,
	SEND_TOKEN varchar(255) NULL,
	GET_TOKEN varchar(255) NULL,
	STICKER_DELIVERY varchar(50) NULL,
	USER_ID INT(11) NULL,
	DELIVERY INT(11) NULL,
	PAYMENTS INT(11) NULL,
	PERSON_TYPE INT(2) NULL,
	STATUSES TEXT NULL,
	STICKER_LOGO INT(11) NULL,
	YML_FROM_MARKET CHAR(1) NULL DEFAULT 'N',
	YML_FILENAME VARCHAR(255) NULL,
	YML_NAME VARCHAR(255) NULL,
	YML_URL VARCHAR(255) NULL,
	ENABLE_AUTO_DISCOUNTS CHAR(1) NULL DEFAULT 'N',
	PRIMARY KEY (ID)
);

create table if not exists b_iplogicberu_prop
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	NAME varchar(255) NOT NULL,
	TYPE varchar(255) NULL,
	VALUE varchar(255) NULL,
	PRIMARY KEY (ID),
	INDEX (PROFILE_ID)
);

create table if not exists b_iplogicberu_attr
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	PROP_ID varchar(11) NOT NULL,
	NAME varchar(255) NOT NULL,
	TYPE varchar(255) NULL,
	VALUE varchar(255) NULL,
	PRIMARY KEY (ID),
	INDEX (PROFILE_ID)
);

create table if not exists b_iplogicberu_order
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	EXT_ID int(64) NOT NULL,
	ORDER_ID int(11) NOT NULL,
	STATE varchar(255) NOT NULL,
	STATE_CODE varchar(255) NULL,
	UNIX_TIMESTAMP int(15) NOT NULL,
	HUMAN_TIME varchar(19) NOT NULL,
	FAKE char(1) NOT NULL default 'N',
	SHIPMENT_ID int(64) NULL,
	SHIPMENT_DATE varchar(10) NULL,
	SHIPMENT_TIMESTAMP int(15) NULL,
	DELIVERY_NAME varchar(255) NULL,
	DELIVERY_ID varchar(255) NULL,
	BOXES_SENT char(1) NOT NULL default 'N',
	READY_TIME int(15) NULL default 0,
	PRIMARY KEY (ID),
	INDEX (EXT_ID)
);

create table if not exists b_iplogicberu_api_log
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	UNIX_TIMESTAMP int(15) NOT NULL,
	HUMAN_TIME varchar(19) NOT NULL,
	TYPE char(2) NOT NULL,
	STATE char(2) NOT NULL,
	URL varchar(255) NOT NULL,
	REQUEST_TYPE varchar(6) NOT NULL,
	REQUEST text NULL,
	REQUEST_H text NOT NULL,
	RESPOND text NULL,
	RESPOND_H text NULL,
	STATUS int(3) NULL,
	ERROR varchar(255) NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_iplogicberu_task
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	UNIX_TIMESTAMP int(15) NOT NULL,
	TYPE varchar(20) NULL,
	STATE char(2) NOT NULL,
	ENTITY_ID varchar(255) NULL,
	TRYING int(3) NULL,
	PRIMARY KEY (ID),
	INDEX (UNIX_TIMESTAMP)
);

create table if not exists b_iplogicberu_error
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	UNIX_TIMESTAMP int(15) NOT NULL,
	HUMAN_TIME varchar(19) NOT NULL,
	ERROR varchar(255) NULL,
	DETAILS mediumtext NOT NULL,
	STATE char(2) NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_iplogicberu_product
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	PRODUCT_ID int(11) NULL,
	SKU_ID varchar(150) NULL,
	MARKET_SKU bigint(64) NULL,
	NAME text NULL,
	VENDOR varchar(255) NULL,
	AVAILABILITY char(1) NULL DEFAULT "N",
	STATE varchar(12) NULL,
	REJECT_REASON varchar(255) NULL,
	REJECT_NOTES text NULL,
	DETAILS mediumtext NULL,
	PRICE varchar(12) NULL,
	HIDDEN char(1) NULL DEFAULT "N",
	API char(1) NULL DEFAULT "N",
	FEED char(1) NULL DEFAULT "N",
	PRIMARY KEY (ID),
	INDEX (SKU_ID),
	INDEX (PROFILE_ID)
);

create table if not exists b_iplogicberu_box
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	ORDER_ID int(11) NOT NULL,
	NUM int(3) NOT NULL,
	WEIGHT int(64) NOT NULL,
	WIDTH int(64) NOT NULL,
	HEIGHT int(64) NOT NULL,
	DEPTH int(64) NOT NULL,
	PRIMARY KEY (ID),
	INDEX (ORDER_ID)
);

create table if not exists b_iplogicberu_box_link
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	ORDER_ID int(11) NOT NULL,
	BOX_ID int(11) NOT NULL,
	SKU_ID varchar(150) NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_iplogicberu_condition
(
	ID int(11) NOT NULL auto_increment,
	PROFILE_ID int(5) NOT NULL,
	ACTIVE char(1) NULL default 'Y',
	SORT int(5) NULL default 100,
	TYPE char(2) NOT NULL,
	PROP_TYPE varchar(255) NOT NULL,
	PROP varchar(255) NULL,
	COND varchar(15) NOT NULL,
	VALUE text NULL,
	ACTION varchar(15) NOT NULL,
	SET_VALUE1 varchar(255) NULL,
	SET_VALUE2 varchar(255) NULL,
	SET_VALUE3 varchar(255) NULL,
	DESCRIPTION text NULL,
	PRIMARY KEY (ID)
);