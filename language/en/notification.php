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
	'NOTIFICATION_TYPE_MENTION' => 'Someone mentioned you in a post',
	'NOTIFICATION_MENTION'      => 'You were mentioned by <strong>%1$s</strong> in: <strong>%2$s</strong>',
]);
