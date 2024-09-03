CREATE TABLE IF NOT EXISTS `ciips_cdss_log`(
	`id` BIGINT UNSIGNED auto_increment NOT NULL,
	`datetime` DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`method` varchar(10) NOT NULL COMMENT 'Request method (POST, PUT, GET, etc.)',
	`url` varchar(150) NOT NULL COMMENT 'Endpoint URL. Example: http://hapifhir:8080/Patient/15',
	`data` TEXT NULL COMMENT 'Data sent on the request body',
	`response` TEXT NULL COMMENT 'Response received from the endpoint',
	CONSTRAINT ciips_cdss_log_pk PRIMARY KEY (id)
);