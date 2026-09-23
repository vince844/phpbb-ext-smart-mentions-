<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) 2026 KondoManager
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'NOTIFICATION_TYPE_MENTION' => 'Qualcuno ti ha menzionato in un post',
	'NOTIFICATION_MENTION'      => 'Sei stato menzionato da <strong>%1$s</strong> in: <strong>%2$s</strong>',
]);
