<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_ThoughtFarmer
 */

class Piwik_ThoughtFarmer_API
{
	static private $instance = null;

	static public function getInstance()
	{
		if (self::$instance == null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	protected function getDataTable($name, $idSite, $period, $date, $expanded, $idSubtable = false, $summarize = false )
	{
		Piwik::checkUserHasViewAccess( $idSite );

		$archive = Piwik_Archive::build($idSite, $period, $date );

		if($idSubtable === false)
		{
			$idSubtable = null;
		}

		if($expanded)
		{
			$dataTable = $archive->getDataTableExpanded($name, $idSubtable);
		}
		else
		{
			$dataTable = $archive->getDataTable($name, $idSubtable);
		}

		if( $summarize )
		{
		    $dataTable->queueFilter('AddSummaryRow',0);
		}

		$dataTable->queueFilter('ReplaceColumnNames', array($expanded));
		$dataTable->queueFilter('ReplaceSummaryRowLabel');

		return $dataTable;
	}

	public function getUserActivity( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		Piwik::checkUserHasViewAccess($idSite);

		$dataTable = $this->getDataTable('thoughtfarmer_user', $idSite, $period, $date, $expanded, $idSubtable );
		$dataTable->filter('Sort', array('nb_hits', 'desc', $naturalSort = false, $expanded));
		
		return $dataTable;
	}

	public function getSearches( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		Piwik::checkUserHasViewAccess($idSite);

		$dataTable = $this->getDataTable('thoughtfarmer_search', $idSite, $period, $date, $expanded, $idSubtable );
		$dataTable->filter('Sort', array('nb_searches', 'desc', $naturalSort = false, $expanded));

		return $dataTable;
	}

	public function getPageHierarchy( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		Piwik::checkUserHasViewAccess($idSite);

		$dataTable = $this->getDataTable('thoughtfarmer_page_hierarchy', $idSite, $period, $date, $expanded, $idSubtable );
		$dataTable->filter('Sort', array('nb_hits', 'desc', $naturalSort = false, $expanded));

		return $dataTable;
	}

	public function getPageTitle( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		Piwik::checkUserHasViewAccess($idSite);

		$dataTable = $this->getDataTable('thoughtfarmer_page_title', $idSite, $period, $date, $expanded, $idSubtable );
		$dataTable->filter('Sort', array('nb_hits', 'desc', $naturalSort = false, $expanded));

		return $dataTable;
	}

	public function getUsersOnline( $idSite )
	{
		Piwik::checkUserHasViewAccess($idSite);

		return $this->loadUsersOnline($idSite);
	}

	protected function loadUsersOnline( $idSite )
	{
		$query = "SELECT
				t1.thoughtfarmer_username as label,
				t1.visit_last_action_time as last_activity,
				t1.visit_total_time as total_time_spent
			    FROM `".Piwik_Common::prefixTable('log_visit')."` as t1
			    WHERE visit_last_action_time >= ?
				 	AND idsite = ?
					AND thoughtfarmer_username is not null
			    ORDER BY last_activity ASC";

		$date = Piwik_Date::factory( (int)(time() - Zend_Registry::get('config')->Tracker->visit_standard_length) );
		
		$results = Zend_Registry::get('db')->fetchAll($query,
			array( $date->getDatetime(), $idSite)
			);

		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromSimpleArray($results);
		
		$dataTable->filter('ColumnCallbackReplace', array('total_time_spent', 'Piwik::getPrettyTimeFromSeconds'));
		$dataTable->filter('ColumnCallbackReplace', array('last_activity', 'Piwik_calculateLastActivityTime'));
		
		return $dataTable;
	}
}

function Piwik_calculateLastActivityTime($datetime)
{
	return Piwik::getPrettyTimeFromSeconds(time() - strtotime($datetime));
}