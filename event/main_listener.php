<?php
/**
*
* Upcoming Birthday List extension for the phpBB Forum Software package.
*
* @copyright (c) Rich McGirr
* @author 2015 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rmcgirr83\upcomingbirthdays\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.index_modify_page_title'			=> 'main',
		);
	}

	public function main($event)
	{
		if (!$this->config['allow_birthdays'])
		{
			return;
		}

		if (!$this->config['allow_birthdays_ahead'] > 0)
		{
			return;
		}
var_dump($this->config['allow_birthdays_ahead']);
		$this->user->add_lang_ext('rmcgirr83/upcomingbirthdays', 'upcomingbirthdays');

		$this->upcoming_birthdays();
	}

	public function upcoming_birthdays()
	{
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday
			FROM ' . USERS_TABLE . ' u
			LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
			WHERE (b.ban_id IS NULL
				OR b.ban_exclude = 1)
				AND	u.user_birthday NOT LIKE '%- 0-%'
				AND u.user_birthday NOT LIKE '0-%'
				AND	u.user_birthday NOT LIKE '0- 0-%'
				AND	u.user_birthday NOT LIKE ''
				AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		$result = $this->db->sql_query($sql);
		//delete the above line and uncomment below line if you want to cache the query for an hour
		//$result = $this->db->sql_query($sql,3600);

		$time = $this->user->create_datetime();
		$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

		$today = (mktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']));
		$tomorrow = (mktime(0, 0, 0, $now['mon'], $now['mday']+1, $now['year']));

		$ucbirthdayrow = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$bdday = $bdmonth = 0;
			list($bdday, $bdmonth) = explode('-', $row['user_birthday']);

			$birthdaycheck = strtotime(gmdate('Y') . '-' . (int) trim($bdmonth) . '-' . (int) trim($bdday));
			$birthdayyear = ( $birthdaycheck < $today ) ? gmdate('Y') + 1 : gmdate('Y');
			$birthdaydate = ($birthdayyear . '-' . (int) trim($bdmonth) . '-' . (int) trim($bdday));

			$ucbirthdayrow[] = array(
				'user_birthday_tstamp' 	=> 	strtotime($birthdaydate . ' GMT'),
				'username'				=>	$row['username'],
				'user_birthdayyear' 	=> 	$birthdayyear,
				'user_birthday' 		=> 	$row['user_birthday'],
				'user_id'				=>	$row['user_id'],
				'user_colour'			=>	$row['user_colour'],
			);

		}
		$this->db->sql_freeresult($result);
		sort($ucbirthdayrow);

		$birthday_ahead_list = '';

		for ($i = 0, $end = sizeof($ucbirthdayrow); $i < $end; $i ++)
		{
			if ( $ucbirthdayrow[$i]['user_birthday_tstamp'] >= $tomorrow && $ucbirthdayrow[$i]['user_birthday_tstamp'] <= ($today + ((($this->config['allow_birthdays_ahead'] > 365) ? 365 : $this->config['allow_birthdays_ahead']) * 86400) ) )
			{
				$user_link = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $ucbirthdayrow[$i]['user_id'], $ucbirthdayrow[$i]['username'], $ucbirthdayrow[$i]['user_colour']) : get_username_string('no_profile', $ucbirthdayrow[$i]['user_id'], $ucbirthdayrow[$i]['username'], $ucbirthdayrow[$i]['user_colour']);

				//lets add to the birthday_ahead list.
				$birthday_ahead_list .= (($birthday_ahead_list != '') ? ', ' : '') . '<span title="' . $this->user->format_date($ucbirthdayrow[$i]['user_birthday_tstamp'], 'D, j. M') . '">' . $user_link . '</span>';
				if ($age = (int) substr($ucbirthdayrow[$i]['user_birthday'], -4))
				{
					$birthday_ahead_list .= ' (' . ($ucbirthdayrow[$i]['user_birthdayyear'] - $age) . ')';
				}
			}
		}

		// Assign index specific vars
		$this->template->assign_vars(array(
			'BIRTHDAYS_AHEAD_LIST'	=> $birthday_ahead_list,
			'L_BIRTHDAYS_AHEAD'	=> sprintf($this->user->lang['BIRTHDAYS_AHEAD'], ($this->config['allow_birthdays_ahead'] > 365) ? 365 : $this->config['allow_birthdays_ahead']),
		));
	}
}
