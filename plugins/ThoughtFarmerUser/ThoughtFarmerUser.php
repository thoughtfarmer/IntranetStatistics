<?php


class Piwik_ThoughtFarmerUser extends Piwik_Plugin
{
	protected $actionsTable = null;
	protected static $limitLevelSubCategory = 10;
	protected static $categoryDelimiter = '/';
	protected static $defaultName = 'index';

	public function getInformation()
	{
		$info = array(
			'name' => 'ThoughtFarmer',
			'description' => 'Intranet features for Piwik',
			'author' => 'Maciej Zawadzinski, Clearcode for ThoughtFarmer',
			'author_homepage' => 'http://clearcode.cc/',
			'version' => '0.1',
			'TrackerPlugin' => true, // this plugin must be loaded during the stats logging
		);

		return $info;
	}

	public function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'Menu.add' => 'addMenus',
			'WidgetsList.add' => 'addWidgets',
		);
		return $hooks;
	}

	function addWidgets()
	{
		Piwik_AddWidget( 'General_Visitors', 'User Engagement', 'ThoughtFarmerUser', 'getUserEngagement');
	}

	function addMenus()
	{
		Piwik_AddMenu( 'General_Visitors', 'User Engagement', array('module' => 'ThoughtFarmerUser', 'action' => 'getUserEngagement'));
	}

	function archiveDay($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();

		// archive top users
		$query = "SELECT CONCAT(nb_visits, ' visit(s)') as label, COUNT(DISTINCT thoughtfarmer_username) as nb_member FROM
				(SELECT thoughtfarmer_username, COUNT(*) as nb_visits
					FROM `".Piwik_Common::prefixTable('log_visit')."` as t1
					WHERE visit_last_action_time >= ?
								AND visit_last_action_time <= ?
								AND idsite = ?
								AND t1.thoughtfarmer_username is not NULL
					GROUP BY t1.thoughtfarmer_username) as t2 GROUP BY label";
		
		$results = Zend_Registry::get('db')->fetchAll($query,
				array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite)
				);

		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromSimpleArray($results);
		$s = $dataTable->getSerialized();
		$archiveProcessing->insertBlobRecord('thoughtfarmer_engagement', $s);
		
		destroy($dataTable);

	}

	function archivePeriod($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();

		$archiveProcessing->archiveDataTable(array('thoughtfarmer_engagement'));
	}
}