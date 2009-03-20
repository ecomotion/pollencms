CREATE TABLE `%TABLE_NAME%` (
  `id_search` int(255) NOT NULL auto_increment,
  `published` tinyint(3) unsigned NOT NULL default '1',
  `nom` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `texte` text NOT NULL,
  `texte_html` text NOT NULL,
  `date_crea` varchar(255) NOT NULL,
  `date_modif` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_search`),
  FULLTEXT KEY `engine` (`nom`,`texte`,`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
