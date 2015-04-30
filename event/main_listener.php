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
	/* @var \rmcgirr83\upcomingbirthdays\core\functions_upcomingbirthdays */
	protected $ubl_functions;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\rmcgirr83\upcomingbirthdays\core\functions_upcomingbirthdays $functions, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->ubl_functions = $functions;
		$this->config = $config;
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
		if (!$this->config['allow_birthdays_ahead'] > 0)
		{
			return;
		}
		$this->user->add_lang_ext('rmcgirr83/upcomingbirthdays', 'upcomingbirthdays');

		$this->ubl_functions->upcoming_birthdays();
	}
}
