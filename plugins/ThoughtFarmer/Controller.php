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

class Piwik_ThoughtFarmer_Controller extends Piwik_Controller
{
	const ACTIONS_REPORT_ROWS_DISPLAY = 100;

	/*
	 * These should be included as constants in lang/*.php files
	 */
	protected function initColumnTranslations($view)
	{
		$view->setColumnTranslation('nb_searches', 'Searches');
		$view->setColumnTranslation('nb_users', 'Users');
		$view->setColumnTranslation('nb_page_create', 'Creates');
		$view->setColumnTranslation('nb_page_edit', 'Edits');
		$view->setColumnTranslation('nb_page_comment', 'Comments');
		$view->setColumnTranslation('nb_hits', 'Views');
		$view->setColumnTranslation('nb_hits_per_visit', 'Views/Visit');
	}

	protected function configureUserActivityView($view)
	{
		$this->initColumnTranslations($view);
		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();
		$view->setLimit( 10 );
	}

	/*
	 * shows Top Users widget
	 */
	public function getUserActivity($fetch = false)
	{
		if(Piwik_Common::getRequestVar('viewDataTable', '') == 'tableAllColumns')
		{
			$view = Piwik_ViewDataTable::factory('table', true);
		}
		else 
		{
			$view = Piwik_ViewDataTable::factory();
		}
		
		$view->init(  	$this->pluginName,
						__FUNCTION__,
						'ThoughtFarmer.getUserActivity',
						'getUserActivitySubtable'
				);
		
		$view->setColumnsToDisplay( array('label','nb_visits','nb_hits','nb_searches','nb_page_edit','nb_page_comment'));
		$view->setTableAllColumnsToDisplay( array('label','nb_visits','nb_hits','nb_searches','nb_page_edit','nb_page_comment'));
		
		$view->setSortedColumn( 'nb_hits' );
		
		$view->setColumnTranslation('label', 'Username');

		$this->configureUserActivityView($view);

		return $this->renderView($view, $fetch);
	}

	public function getUserActivitySubtable($fetch = false)
	{
		if(Piwik_Common::getRequestVar('viewDataTable', '') == 'tableAllColumns')
		{
			$view = Piwik_ViewDataTable::factory('table', true);
		}
		else 
		{
			$view = Piwik_ViewDataTable::factory();
		}
		
		$view->init(  	$this->pluginName,
						__FUNCTION__,
						'ThoughtFarmer.getUserActivity',
						'getUserActivitySubtable'
				);

		$view->setColumnsToDisplay( array('label', 'nb_page_edit', 'nb_page_comment' ) );
		$view->setTableAllColumnsToDisplay( array('label','nb_page_edit','nb_page_comment'));
		
		$this->configureUserActivityView($view);

		$view->setColumnTranslation('label', 'Page');

		$view->disableSearchBox();

		return $this->renderView($view, $fetch);
	}

	public function getSearches($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName,
						__FUNCTION__,
						'ThoughtFarmer.getSearches',
						'getSearchesSubtable'
				);

		$view->setColumnsToDisplay( array('label', 'nb_searches','nb_users', 'nb_visits' ) );
		$view->setSortedColumn( 'nb_searches' );

		$this->initColumnTranslations($view);
		$view->disableExcludeLowPopulation();
		$view->setLimit( 10 );

		$view->setColumnTranslation('label', 'Keyword');

		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();

		return $this->renderView($view, $fetch);
	}

	public function getSearchesSubtable($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName,
						__FUNCTION__,
						'ThoughtFarmer.getSearches',
						'getSearchesSubtable'
				);

		$view->setColumnsToDisplay( array('label', 'nb_searches' ) );

		$this->initColumnTranslations($view);
		$view->disableExcludeLowPopulation();
		$view->setLimit( 10 );
		
		$view->setColumnTranslation('label', 'Username');

		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();
		$view->disableSearchBox();

		return $this->renderView($view, $fetch);
	}

	protected function configureViewActions($view)
	{
		$view->setColumnTranslation('nb_hits', Piwik_Translate('General_ColumnPageviews'));
		$view->setColumnTranslation('nb_visits', Piwik_Translate('General_ColumnUniquePageviews'));
		$view->setColumnTranslation('nb_page_edit', 'Edits');
		$view->setColumnTranslation('nb_page_comment', 'Comments');

        $view->setColumnsToDisplay( array('label','nb_hits','nb_visits','nb_page_edit','nb_page_comment') );
		
		$view->setTemplate('CoreHome/templates/datatable_actions.tpl');
		
		if(Piwik_Common::getRequestVar('idSubtable', -1) != -1)
		{
			$view->setTemplate('CoreHome/templates/datatable_actions_subdatable.tpl');
		}
		$view->disableExcludeLowPopulation();
		$view->disableShowAllViewsIcons();
		$view->disableShowAllColumns();
		$view->disableSearchBox();

		$view->setLimit( self::ACTIONS_REPORT_ROWS_DISPLAY );
		$view->main();

		return $view;
	}

	protected function getActionView($currentAction, $controllerActionSubtable, $apiCall)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName,
						$currentAction,
						$apiCall,
						$controllerActionSubtable );

		$view->setColumnTranslation('label', 'Location');

		return $view;
	}

	public function getPageHierarchy($fetch = false)
	{
		$view = $this->getActionView(__FUNCTION__, 'getPageHierarchySubDataTable', 'ThoughtFarmer.getPageHierarchy');
		$this->configureViewActions($view);
		return $this->renderView($view, $fetch);
	}

	public function getPageHierarchySubDataTable($fetch = false)
	{
		$view = $this->getActionView(__FUNCTION__, 'getPageHierarchySubDataTable', 'ThoughtFarmer.getPageHierarchy');
		$this->configureViewActions($view);
		return $this->renderView($view, $fetch);
	}

	public function getPageTitle($fetch = false)
	{
		$view = $this->getActionView(__FUNCTION__, 'getPageTitleSubDataTable', 'ThoughtFarmer.getPageTitle');
		$this->configureViewActions($view);
		return $this->renderView($view, $fetch);
	}

	public function getPageTitleSubDataTable($fetch = false)
	{
		$view = $this->getActionView(__FUNCTION__, 'getPageTitleSubDataTable', 'ThoughtFarmer.getPageTitle');
		$this->configureViewActions($view);
		return $this->renderView($view, $fetch);
	}
	
	public function getUsersOnline($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$this->pluginName,
						__FUNCTION__,
						'ThoughtFarmer.getUsersOnline');

		$view->setColumnsToDisplay( array('label', 'total_time_spent', 'last_activity' ) );

		$this->initColumnTranslations($view);
		$view->disableExcludeLowPopulation();
		$view->setLimit( 10 );
		
		$view->setColumnTranslation('label', 'Username');
		$view->setColumnTranslation('total_time_spent', 'Total time spent');
		$view->setColumnTranslation('last_activity', 'Last Activity');

		$view->disableExcludeLowPopulation();
		$view->disableShowAllColumns();

		return $this->renderView($view, $fetch);
	}
}

