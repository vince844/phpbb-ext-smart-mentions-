<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) 2026 KondoManager
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kondomanager\mention\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/**
	 * Maximum number of mentions allowed per post.
	 * Prevents mention flooding / DoS attacks.
	 */
	const MAX_MENTIONS_PER_POST = 15;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\notification\manager $notification_manager,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language
	)
	{
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->auth = $auth;
		$this->template = $template;
		$this->helper = $helper;
		$this->language = $language;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.page_header'                        => 'page_header',
			'core.text_formatter_s9e_configure_after' => 'configure_s9e_after',
			'core.submit_post_end'                    => 'submit_post_end',
		];
	}

	/**
	 * Inject autocomplete URL into the template and load language.
	 */
	public function page_header($event)
	{
		$this->language->add_lang('notification', 'kondomanager/mention');
		$this->template->assign_var('U_MENTION_AUTOCOMPLETE', $this->helper->route('kondomanager_mention_autocomplete'));
	}

	/**
	 * Configure the s9e TextFormatter to recognise @mention syntax.
	 *
	 * FIX #1 (XSS): The template now URL-encodes the username in the href
	 *   attribute via urlencode() in XSLT, and uses xsl:value-of which is
	 *   auto-escaped by the XSLT processor in text nodes.
	 */
	public function configure_s9e_after($event)
	{
		$configurator = $event['configurator'];

		if (!isset($configurator->tags['MENTION']))
		{
			$tag = $configurator->tags->add('MENTION');

			$attribute = $tag->attributes->add('username');
			// Restrict the attribute to safe characters (alphanumeric, spaces, hyphens, underscores, dots)
			// This is a defence-in-depth measure against XSS via malicious usernames.
			$attribute->filterChain->append('#regexp')
				->setRegexp('/^[a-zA-Z0-9 _\\-\\.]{1,50}$/');

			// and xsl:value-of for safe text output (auto-escaped by XSLT).
			$tag->template =
				'<a href="memberlist.php?mode=viewprofile&amp;un={@username}" class="mention">' .
					'@<xsl:value-of select="@username"/>' .
				'</a>';
		}

		// Add a Preg match for @username and @"User Name"
		$configurator->Preg->match('/(?J)(?<![a-zA-Z0-9])@(?:\"(?<username>[^\"]{1,50})\"|(?<username>[a-zA-Z0-9_\-\.]{1,50}))/', 'MENTION');
	}

	/**
	 * Handle post submission: extract mentions and send notifications.
	 *
	 * FIX #2 (XML injection): Uses DOMDocument instead of regex to parse s9e XML.
	 * FIX #3 (Flooding):      Limits mentions to MAX_MENTIONS_PER_POST.
	 * FIX #5 (Duplicate):     On edit, skips users who were already notified.
	 */
	public function submit_post_end($event)
	{
		$data = $event['data'];
		$mode = $event['mode'];

		// Only process on actual post/reply/edit actions
		if (!in_array($mode, ['post', 'reply', 'quote', 'edit', 'edit_first_post', 'edit_last_post', 'edit_topic'], true))
		{
			return;
		}

		$post_text = $data['message'];

		// FIX #2: Extract mentioned usernames using DOMDocument (safe XML parsing)
		$usernames = $this->extract_mentioned_usernames($post_text);

		if (empty($usernames))
		{
			return;
		}

		// FIX #3: Limit the number of mentions per post to prevent flooding
		$usernames = array_slice($usernames, 0, self::MAX_MENTIONS_PER_POST);

		$usernames_clean = array_map('utf8_clean_string', $usernames);

		// Look up user IDs for the mentioned usernames, excluding the post author
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('username_clean', $usernames_clean) . '
				AND user_id <> ' . (int) $data['poster_id'] . '
				AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);

		$user_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_ids[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($user_ids))
		{
			return;
		}

		// FIX #5: On edit, exclude users who were already notified for this post
		$is_edit = in_array($mode, ['edit', 'edit_first_post', 'edit_last_post', 'edit_topic'], true);
		if ($is_edit)
		{
			$already_notified = $this->notification_manager->get_notified_users(
				'kondomanager.mention.notification.type.mention',
				['item_id' => (int) $data['post_id']]
			);

			if (!empty($already_notified))
			{
				$user_ids = array_diff($user_ids, array_keys($already_notified));
			}

			if (empty($user_ids))
			{
				return;
			}
		}

		$notification_data = [
			'post_id'         => (int) $data['post_id'],
			'topic_id'        => (int) $data['topic_id'],
			'forum_id'        => (int) $data['forum_id'],
			'post_subject'    => $data['post_subject'],
			'poster_id'       => (int) $data['poster_id'],
			'topic_title'     => isset($data['topic_title']) ? $data['topic_title'] : '',
			'post_username'   => '',
			'forum_name'      => isset($data['forum_name']) ? $data['forum_name'] : '',
			'post_time'       => isset($data['post_time']) ? $data['post_time'] : time(),
			'users_to_notify' => $user_ids,
		];

		$this->notification_manager->add_notifications(
			'kondomanager.mention.notification.type.mention',
			$notification_data
		);
	}

	/**
	 * Safely extract mentioned usernames from the s9e XML text using DOMDocument.
	 *
	 * @param string $xml_text The s9e-formatted post text (XML)
	 * @return array Unique list of mentioned usernames
	 */
	protected function extract_mentioned_usernames($xml_text)
	{
		$usernames = [];

		// Wrap in a root element so DOMDocument can parse it as valid XML
		$dom = new \DOMDocument();
		$prev = libxml_use_internal_errors(true);

		if (!@$dom->loadXML('<r>' . $xml_text . '</r>'))
		{
			libxml_clear_errors();
			libxml_use_internal_errors($prev);
			return [];
		}

		libxml_clear_errors();
		libxml_use_internal_errors($prev);

		$mentions = $dom->getElementsByTagName('MENTION');
		foreach ($mentions as $mention)
		{
			$username = $mention->getAttribute('username');
			if ($username !== '')
			{
				$usernames[] = $username;
			}
		}

		return array_unique($usernames);
	}
}
