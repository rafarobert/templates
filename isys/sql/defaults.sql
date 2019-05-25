alter table table_name add `status` varchar(15) NOT NULL DEFAULT 'ENABLED';
alter table table_name add `change_count` int(11) NOT NULL DEFAULT '0';
alter table table_name add `id_user_modified` int(11) UNSIGNED NOT NULL;
alter table table_name add `id_user_created` int(11) UNSIGNED NOT NULL;
alter table table_name add `date_modified` datetime NOT NULL;
alter table table_name add `date_created` datetime NOT NULL;
