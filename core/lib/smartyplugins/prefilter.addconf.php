<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Fichier :  prefilter.addconf.php
 * Type :     filtre de pré-compilation
 * Nom :      addconf
 * Rôle :     Ajoute les fichiers de configuration aux templates.
 * ----------
 */

function smarty_prefilter_addconf($source, &$smarty)
{
	return '

{if isset($DEFAULT_CONFIG_FILE_SITE)}
	{config_load file=$DEFAULT_CONFIG_FILE_SITE}
	{config_load file=$DEFAULT_CONFIG_FILE_SITE section=\'HOTKEYS\'}
{/if}

	{config_load file=$CONFIG_FILE_SITE}
	{config_load file=$CONFIG_FILE_SITE section=\'HOTKEYS\'}

{if isset($CONFIG_FILE_CATEGORY)}
	{if isset($DEFAULT_CONFIG_FILE_SITE)}
		{config_load file=$DEFAULT_CONFIG_FILE_SITE section=\'CATEGORY\'}
	{/if}
	{config_load file=$CONFIG_FILE_SITE section=\'CATEGORY\'}
	{config_load file=$CONFIG_FILE_CATEGORY}
{/if}
{if isset($CONFIG_FILE_PAGE)}
	{if isset($DEFAULT_CONFIG_FILE_SITE)}
		{config_load file=$DEFAULT_CONFIG_FILE_SITE section=\'PAGE\'}
	{/if}
	{config_load file=$CONFIG_FILE_SITE section=\'PAGE\'}
	{config_load file=$CONFIG_FILE_PAGE}
{/if}

	'.$source;
}
?>