





--§%!&(&?%§CB_TABLE§%!&)&?%§ Do not modify this part of the code unless you want to uninstall the entire extension.

CREATE TABLE cb_table (`uid` INT UNSIGNED AUTO_INCREMENT NOT NULL, `pid` INT UNSIGNED DEFAULT 0 NOT NULL, `tstamp` INT UNSIGNED DEFAULT 0 NOT NULL, `crdate` INT UNSIGNED DEFAULT 0 NOT NULL, `deleted` SMALLINT UNSIGNED DEFAULT 0 NOT NULL, `hidden` SMALLINT UNSIGNED DEFAULT 0 NOT NULL, `starttime` INT UNSIGNED DEFAULT 0 NOT NULL, `endtime` INT UNSIGNED DEFAULT 0 NOT NULL, `fe_group` VARCHAR(255) DEFAULT '0' NOT NULL, `sorting` INT DEFAULT 0 NOT NULL, `editlock` SMALLINT UNSIGNED DEFAULT 0 NOT NULL, `sys_language_uid` INT DEFAULT 0 NOT NULL, `l18n_parent` INT UNSIGNED DEFAULT 0 NOT NULL, `l10n_source` INT UNSIGNED DEFAULT 0 NOT NULL, `l10n_state` TEXT DEFAULT NULL, `l18n_diffsource` MEDIUMBLOB DEFAULT NULL, `t3ver_oid` INT UNSIGNED DEFAULT 0 NOT NULL, `t3ver_wsid` INT UNSIGNED DEFAULT 0 NOT NULL, `t3ver_state` SMALLINT DEFAULT 0 NOT NULL, `t3ver_stage` INT DEFAULT 0 NOT NULL, `preset` VARCHAR(255) DEFAULT '' NOT NULL, `classes` LONGTEXT DEFAULT NULL, INDEX `parent` (pid, deleted, hidden), `CType` VARCHAR(255) DEFAULT '' NOT NULL,`tt_content_uid` INT UNSIGNED DEFAULT 0 NOT NULL, INDEX `translation_source` (l10n_source), INDEX `t3ver_oid` (t3ver_oid, t3ver_wsid), PRIMARY KEY(uid));
CREATE TABLE tt_content (`cb_index` INT UNSIGNED DEFAULT 0 NOT NULL);

--§%!&(&?%§CB_TABLE§%!&)&?%§

--&%!§(§?%&textpicture&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!

--&%!§(§?%&textpicture&%?§)§!%&

--&%!§(§?%&menucards&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
CREATE TABLE menucard_rows (`dishHeader` VARCHAR(255) DEFAULT '' NOT NULL, `dishDesc` VARCHAR(255) DEFAULT '' NOT NULL, `price` NUMERIC(10, 2) DEFAULT 0 NOT NULL, `image` INT UNSIGNED DEFAULT 0 NOT NULL, `menucard_rows` INT UNSIGNED DEFAULT 0 NOT NULL);
CREATE TABLE menucard_columns (`headerType` VARCHAR(255) DEFAULT '' NOT NULL, `columnIcon` VARCHAR(255) DEFAULT '' NOT NULL, `menucard_rows` INT UNSIGNED DEFAULT 0 NOT NULL, `menucard_columns` INT UNSIGNED DEFAULT 0 NOT NULL);
CREATE TABLE tt_content (`menucard_columns` INT UNSIGNED DEFAULT 0 NOT NULL);

--&%!§(§?%&menucards&%?§)§!%&
--&%!§(§?%&blog_creator&%?§)§!%& NEVER DELETE THIS STARTING AND THE ENDING COMMENT - NEVER USE THE TOKEN CHARACTER SETS IN THAT ORDER!
CREATE TABLE tt_content (`date` BIGINT DEFAULT 0 NOT NULL);

--&%!§(§?%&blog_creator&%?§)§!%&
