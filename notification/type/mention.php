<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) 2026 KondoManager
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kondomanager\mention\notification\type;

/**
 * Mention notification type.
 * Notifies a user when they are mentioned via @username in a post.
 */
class mention extends \phpbb\notification\type\post
{
	/**
	 * Get notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'kondomanager.mention.notification.type.mention';
	}

	/**
	 * Language key used to output the text
	 *
	 * @var string
	 */
	protected $language_key = 'NOTIFICATION_MENTION';

	/**
	 * Notification option data (for outputting to the user)
	 */
	static public $notification_option = [
		'lang'  => 'NOTIFICATION_TYPE_MENTION',
		'group' => 'NOTIFICATION_GROUP_POSTING',
	];

	/**
	 * This notification type is always available (no config dependency).
	 */
	public function is_available()
	{
		return true;
	}

	/**
	 * Find the users who want to receive notifications.
	 *
	 * This uses the pre-filtered list of user IDs passed in $post['users_to_notify']
	 * by the event listener, and then checks forum read permissions and user
	 * notification preferences via get_authorised_recipients().
	 *
	 * @param array $post Data from submit_post
	 * @param array $options Options for finding users for notification
	 *
	 * @return array
	 */
	public function find_users_for_notification($post, $options = [])
	{
		$options = array_merge([
			'ignore_users' => [],
		], $options);

		$users = isset($post['users_to_notify']) ? $post['users_to_notify'] : [];

		if (empty($users))
		{
			return [];
		}

		// get_authorised_recipients checks f_read permission and user notification
		// preferences, preventing information disclosure (FIX #4).
		return $this->get_authorised_recipients($users, $post['forum_id'], $options, true);
	}

	/**
	 * Users needed to query before this notification can be displayed
	 *
	 * @return array Array of user_ids
	 */
	public function users_to_query()
	{
		return [$this->get_data('poster_id')];
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_insert_array($post, $pre_create_data = [])
	{
		$this->set_data('poster_id', $post['poster_id']);
		$this->set_data('topic_title', isset($post['topic_title']) ? $post['topic_title'] : '');
		$this->set_data('post_subject', $post['post_subject']);
		$this->set_data('post_username', isset($post['post_username']) ? $post['post_username'] : '');
		$this->set_data('forum_id', $post['forum_id']);
		$this->set_data('forum_name', isset($post['forum_name']) ? $post['forum_name'] : '');

		if (isset($post['post_time']))
		{
			$this->notification_time = $post['post_time'];
		}

		// Call grandparent (base) directly, since parent::create_insert_array
		// expects fields like forum_name that we've already set here.
		\phpbb\notification\type\base::create_insert_array($post, $pre_create_data);
	}
}
