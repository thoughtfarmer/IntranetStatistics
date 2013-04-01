<?php

class Piwik_ThoughtFarmerUser_API
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
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS, 'desc', $naturalSort = false, $expanded));

		if( $summarize )
		{
		    $dataTable->queueFilter('AddSummaryRow',0);
		}

		$dataTable->queueFilter('ReplaceColumnNames', array($expanded));
		$dataTable->queueFilter('ReplaceSummaryRowLabel');

		return $dataTable;
	}

	public function getUserEngagement( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		Piwik::checkUserHasViewAccess($idSite);

		$dataTable = $this->getDataTable('thoughtfarmer_engagement', $idSite, $period, $date, $expanded, $idSubtable );

		return $dataTable;
	}
}