<?php

class Piwik_ThoughtFarmerUser_Controller extends Piwik_Controller
{
	public function getUserEngagement($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory('graphPie');
		$view->init(  	$this->pluginName,
						__FUNCTION__,
						'ThoughtFarmerUser.getUserEngagement',
						'getUserEngagement'
				);

		$view->setColumnsToDisplay( array('label', 'nb_member') );

		$view->setSortedColumn( 'label', 'asc' );

		$view->setColumnTranslation('label', 'Visits');
		$view->setColumnTranslation('nb_member', 'Members');

		$view->disableOffsetInformationAndPaginationControls();
		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();
		$view->setLimit( 10 );
		
		return $this->renderView($view, $fetch);
	}
}
