<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) 2026 KondoManager
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kondomanager\mention\controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use phpbb\request\request_interface;

class autocomplete
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\db\driver\driver_interface $db, request_interface $request, \phpbb\user $user)
	{
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * Endpoint to fetch username suggestions for autocomplete.
	 *
	 * @return JsonResponse
	 */
	public function handle()
	{
		$query = $this->request->variable('q', '', true);
		$query = trim($query);

		if (strlen($query) < 2)
		{
			return new JsonResponse([]);
		}

		// Escape for LIKE statement. Clean string for robust matching.
		$search_clean = utf8_clean_string($query);
		$search_clean = $this->db->sql_escape($search_clean);

		$sql = 'SELECT username
			FROM ' . USERS_TABLE . "
			WHERE username_clean LIKE '" . $search_clean . "%'
				AND user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ")
				AND user_id <> " . (int) max(1, $this->user->data['user_id']) . '
			ORDER BY username_clean ASC';

		// Limita a 5 risultati per non sovraccaricare l'interfaccia e il DB
		$result = $this->db->sql_query_limit($sql, 5);

		$users = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = [
				'username' => $row['username']
			];
		}
		$this->db->sql_freeresult($result);

		return new JsonResponse($users);
	}
}
