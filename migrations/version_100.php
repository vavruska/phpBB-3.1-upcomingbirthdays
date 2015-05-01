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

class version_100 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['ubl_version']) && version_compare($this->config['ubl_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v311');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('ubl_version', '1.0.0')),
			array('config.add', array('allow_birthdays_ahead', 30)),
		);
	}

}
