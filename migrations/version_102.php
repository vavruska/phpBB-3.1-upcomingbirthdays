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

namespace rmcgirr83\upcomingbirthdays\migrations;

/**
* Primary migration
*/

class version_102 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\rmcgirr83\upcomingbirthdays\migrations\version_101');
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('ubl_version')),
		);
	}

}
