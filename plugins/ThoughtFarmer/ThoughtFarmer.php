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

require_once 'ThoughtFarmerAction.php';

class Piwik_ThoughtFarmer extends Piwik_Plugin
{
	protected $actionsTable = null;
	protected static $limitLevelSubCategory = 10;
	protected static $categoryDelimiter = '›';
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
			'Tracker.saveVisitorInformation' => 'saveVisitorInformation',
			'Tracker.knownVisitorUpdate' => 'saveVisitorInformation',
			'Tracker.newAction' => 'getNewActionObject',
			'Menu.add' => 'addMenus',
			'WidgetsList.add' => 'addWidgets',
		);
		return $hooks;
	}

	function addWidgets()
	{
		Piwik_AddWidget( 'Actions_Actions', 'Top keywords', 'ThoughtFarmer', 'getSearches');
		Piwik_AddWidget( 'General_Visitors', 'Top users', 'ThoughtFarmer', 'getUserActivity');
		Piwik_AddWidget( 'Actions_Actions', 'Page Hierarchy', 'ThoughtFarmer','getPageHierarchy');
		Piwik_AddWidget( 'Actions_Actions', 'Actions_SubmenuPageTitles', 'ThoughtFarmer','getPageTitle');
		//Piwik_AddWidget( 'General_Visitors', 'Online users', 'ThoughtFarmer', 'getUsersOnline');
	}

	function addMenus()
	{
		Piwik_AddMenu( 'Actions_Actions', 'Top keywords', array('module' => 'ThoughtFarmer', 'action' => 'getSearches'));
		Piwik_AddMenu( 'General_Visitors', 'Top users', array('module' => 'ThoughtFarmer', 'action' => 'getUserActivity'));
		Piwik_AddMenu( 'Actions_Actions', 'Page Hierarchy', array('module' => 'ThoughtFarmer', 'action' => 'getPageHierarchy'),  true, 1);
		Piwik_AddMenu( 'Actions_Actions', 'Actions_SubmenuPageTitles', array('module' => 'ThoughtFarmer', 'action' => 'getPageTitle'),  true, 4);
		//Piwik_AddMenu( 'General_Visitors', 'Online users', array('module' => 'ThoughtFarmer', 'action' => 'getUsersOnline'));
	}

	/*
	 * saves additional visitor information passed as custom data via piwik js
	 */
	public function saveVisitorInformation($notification)
	{
	    $visitorInfo =& $notification->getNotificationObject();

		if( isset($_GET['data']) && ($customVariables = json_decode($_GET['data'],true)) !== null )
		{
			if( isset($customVariables['ThoughtFarmer_username']) )
			{
				printDebug("ThoughtFarmer::recording username");
				
				$visitorInfo['ThoughtFarmer_username'] = $customVariables['ThoughtFarmer_username'];
			}
		}
	}

	/*
	 * change default Piwik action object to the one that detect & save searches
	 */
	public function getNewActionObject( $notification )
	{
	    $action =& $notification->getNotificationObject();

	    $action = new Piwik_ThoughtFarmer_Action();
	}

	protected function archiveTopUsers( $archiveProcessing )
	{
		// archive top users
		//
		// (main) table columns: label (username), nb_visits, nb_searches, nb_page_edit, nb_page_comment, nb_page_create
		// subtable columns: label (page name), nb_page_edit, nb_page_comment, nb_page_create (0 or 1 only, 1 means that user created it)


		// first we generate data for main table

		// get basic information about the user - number of visits, hits, searches

		$query = "SELECT t1.label,
			  t1.nb_visits,
			  t1.nb_searches,
			  t2.nb_hits
			FROM
			(
			SELECT
			    t1.thoughtfarmer_username as label,
			    COUNT(DISTINCT t1.idvisit) as nb_visits,
			    COUNT(DISTINCT t2.search_phrase) as nb_searches
			FROM `".Piwik_Common::prefixTable('log_visit')."` as t1
			LEFT JOIN `".Piwik_Common::prefixTable('thoughtfarmer_search')."` as t2
			    ON t2.idvisit = t1.idvisit
			WHERE t1.visit_last_action_time >= ?
			            AND t1.visit_last_action_time <= ?
			            AND t1.idsite = ?
			            AND t1.thoughtfarmer_username is not null
			GROUP BY t1.`thoughtfarmer_username`
			) t1

			INNER JOIN

			(
			SELECT
			    t1.thoughtfarmer_username as label,
			    COUNT(DISTINCT t3.idlink_va) as nb_hits
			FROM `".Piwik_Common::prefixTable('log_visit')."` as t1
			LEFT JOIN `".Piwik_Common::prefixTable('log_link_visit_action')."` as t3
			    ON t3.idvisit = t1.idvisit
			WHERE t1.visit_last_action_time >= ?
			            AND t1.visit_last_action_time <= ?
			            AND t1.idsite = ?
			            AND t1.thoughtfarmer_username is not null
			GROUP BY t1.`thoughtfarmer_username`
			) t2

			ON t1.label = t2.label
			ORDER BY t1.nb_visits DESC;";

		$results = Zend_Registry::get('db')->fetchAll($query,
			array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite,
				   $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite )
			);

		// get page creates, edits and comments
		$query = "SELECT t1.thoughtfarmer_username as username,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_CREATE.",1,0)) as nb_page_create,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_EDIT.",1,0)) as nb_page_edit,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_COMMENT.",1,0)) as nb_page_comment
					FROM ".$archiveProcessing->logTable." as t1
						JOIN ".Piwik_Common::prefixTable("thoughtfarmer_action")." as t2 USING(idvisit)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND t1.idsite = ?
					GROUP BY t1.thoughtfarmer_username";

		$pageResults = Zend_Registry::get('db')->fetchAll($query,
			array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite)
			);

		$pageResultsByUsername = array();

		foreach($pageResults as $row)
		{
			$pageResultsByUsername[$row['username']] = $row;
		}

		foreach($results as &$row)
		{
			$username = $row['label'];

			if( isset($pageResultsByUsername[$username]) )
			{
				$row['nb_page_create'] = $pageResultsByUsername[$username]['nb_page_create'];
				$row['nb_page_edit'] = $pageResultsByUsername[$username]['nb_page_edit'];
				$row['nb_page_comment'] = $pageResultsByUsername[$username]['nb_page_comment'];
			}
			$row['nb_hits_per_visit'] = round($row['nb_hits'] / $row['nb_visits'],2);
		}

		// load data into DataTable
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromSimpleArray($results);


		// now generate data for subtables
		
		$query = "SELECT 	t3.name as url,
							t4.name as name,
							t1.thoughtfarmer_username as username,
							COUNT(DISTINCT t1.idvisit) as nb_visits,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_CREATE.",1,0)) as nb_page_create,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_EDIT.",1,0)) as nb_page_edit,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_COMMENT.",1,0)) as nb_page_comment
					FROM ".$archiveProcessing->logTable." as t1
						JOIN ".Piwik_Common::prefixTable("thoughtfarmer_action")." as t2 USING(idvisit)
						JOIN ".Piwik_Common::prefixTable("log_action")." as t4 ON (t2.idaction_thoughtfarmer = t4.idaction)
						JOIN ".Piwik_Common::prefixTable("log_action")." as t3 ON (t2.idaction_url = t3.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND t1.idsite = ?
					GROUP BY t4.idaction, t1.thoughtfarmer_username";


		$results = Zend_Registry::get('db')->fetchAll($query,
			array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite)
		);

		$pagesByUsername = array();

		foreach($results as $row)
		{
			$username = $row['username'];

			if( !isset($pagesByUsername[$username]) )
			{
				$pagesByUsername[$username] = new Piwik_DataTable();
			}

			$r = new Piwik_DataTable_Row();
			$r->addColumn('label', $row['name']);
			$r->addMetadata('url', $row['url']);
			$r->addColumn('nb_visits', $row['nb_visits']);
			$r->addColumn('nb_page_edit', $row['nb_page_edit']);
			$r->addColumn('nb_page_comment', $row['nb_page_comment']);
			$r->addColumn('nb_page_create', $row['nb_page_create']);

			$pagesByUsername[$username]->addRow($r);
		}

		foreach($dataTable->getRows() as $row)
		{
			$username = $row->getColumn('label');

			if( isset($pagesByUsername[$username]) )
			{
				$row->addSubtable($pagesByUsername[$username]);
			}
		}

		// save to the database & free the memory used
		$s = $dataTable->getSerialized();
		$archiveProcessing->insertBlobRecord('thoughtfarmer_user', $s);
		destroy($dataTable);
	}

	protected function archiveTopKeywords($archiveProcessing)
	{

		// archive top keywords
		//
		// (main) table columns: label (search keyword), nb_visits, nb_users, nb_searches
		// subtable columns: label (username), nb_visits, nb_searches

		// first we generate data for main table

		$query = "SELECT
				t2.search_phrase as label,
				COUNT(DISTINCT idvisit) as nb_visits,
				COUNT(DISTINCT t1.thoughtfarmer_username) as nb_users,
				COUNT(t2.search_phrase) as nb_searches
			    FROM `".Piwik_Common::prefixTable('log_visit')."` as t1
					JOIN `".Piwik_Common::prefixTable('thoughtfarmer_search')."` as t2 USING(idvisit)
			    WHERE visit_last_action_time >= ?
					AND visit_last_action_time <= ?
				 	AND t1.idsite = ?
			    GROUP BY t2.`search_phrase` ORDER BY nb_searches DESC";


		$results = Zend_Registry::get('db')->fetchAll($query,
			array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite)
			);
		
		// load data into DataTable
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromSimpleArray($results);


		// now generate data for subtables

		$query = "SELECT
				t2.search_phrase as search_phrase,
				t1.thoughtfarmer_username as username,
				COUNT(t2.search_phrase) as nb_searches
			    FROM `".Piwik_Common::prefixTable('log_visit')."` as t1
					JOIN `".Piwik_Common::prefixTable('thoughtfarmer_search')."` as t2 USING(idvisit)
			    WHERE visit_last_action_time >= ?
					AND visit_last_action_time <= ?
				 	AND t1.idsite = ?
			    GROUP BY t2.`search_phrase`, t1.thoughtfarmer_username";

		$results = Zend_Registry::get('db')->fetchAll($query,
			array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite)
		);

		$usersBySearchPhrase = array();

		foreach($results as $row)
		{
			$searchPhrase = $row['search_phrase'];

			if( !isset($usersBySearchPhrase[$searchPhrase]) )
			{
				$usersBySearchPhrase[$searchPhrase] = new Piwik_DataTable();
			}

			$r = new Piwik_DataTable_Row();
			$r->addColumn('label', $row['username']);
			$r->addColumn('nb_searches', $row['nb_searches']);

			$usersBySearchPhrase[$searchPhrase]->addRow($r);
		}

		foreach($dataTable->getRows() as $row)
		{
			$searchPhrase = $row->getColumn('label');

			if( isset($usersBySearchPhrase[$searchPhrase]) )
			{
				$row->addSubtable($usersBySearchPhrase[$searchPhrase]);
			}
		}
		
		// save to the database & free the memory used
		$s = $dataTable->getSerialized();
		$archiveProcessing->insertBlobRecord('thoughtfarmer_search', $s);
		destroy($dataTable);
	}

	protected function archiveActions($archiveProcessing)
	{
		// archive actions for Page Hierarchy view
		
		$this->actionsTable = array();

		// basic metrics first: name, url, visits, hits
		$query = "SELECT 	t3.name as url,
							t4.name as name,
							count(distinct t1.idvisit) as nb_visits,
							count(distinct t1.idvisitor) as nb_uniq_visitors,
							count(*) as nb_hits
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".Piwik_Common::prefixTable("log_link_visit_action")." as t2 USING (idvisit))
							LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t3 ON (t2.idaction_url = t3.idaction)
								LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t4 ON (t2.idaction_thoughtfarmer = t4.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND t1.idsite = ?
					GROUP BY t4.idaction
					ORDER BY nb_hits DESC";

		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$this->updateActionsTableWithRowQuery($query);

		// get page creates, edits and comments
		$query = "SELECT 	t3.name as url,
							t4.name as name,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_EDIT.",1,0)) as nb_page_edit,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_COMMENT.",1,0)) as nb_page_comment
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".Piwik_Common::prefixTable("thoughtfarmer_action")." as t2 USING (idvisit))
							LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t3 ON (t2.idaction_url = t3.idaction)
								LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t4 ON (t2.idaction_thoughtfarmer = t4.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND t1.idsite = ?
					GROUP BY t4.idaction";

		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$this->updateActionsTableWithRowQuery($query);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTable);
		//$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized();
		$archiveProcessing->insertBlobRecord('thoughtfarmer_page_hierarchy', $s);
		destroy($dataTable);

		
		// archive actions for Page Title view
		
		$this->actionsTable = array();

		// basic metrics first: name, url, visits, hits
		$query = "SELECT 	t3.name as url,
							t4.name as name,
							count(distinct t1.idvisit) as nb_visits,
							count(distinct t1.idvisitor) as nb_uniq_visitors,
							count(*) as nb_hits
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".Piwik_Common::prefixTable("log_link_visit_action")." as t2 USING (idvisit))
							LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t3 ON (t2.idaction_url = t3.idaction)
								LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t4 ON (t2.idaction_name = t4.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND t1.idsite = ?
					GROUP BY t4.idaction
					ORDER BY nb_hits DESC";

		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$this->updateActionsTableWithRowQuery($query);

		// get page creates, edits and comments
		$query = "SELECT 	t3.name as url,
							t4.name as name,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_EDIT.",1,0)) as nb_page_edit,
							sum(IF(t2.type=".Piwik_ThoughtFarmer_Action::THOUGHTFARMER_PAGE_COMMENT.",1,0)) as nb_page_comment
					FROM (".$archiveProcessing->logTable." as t1
						LEFT JOIN ".Piwik_Common::prefixTable("thoughtfarmer_action")." as t2 USING (idvisit))
							LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t3 ON (t2.idaction_url = t3.idaction)
								LEFT JOIN ".Piwik_Common::prefixTable("log_action")." as t4 ON (t2.idaction_name = t4.idaction)
					WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
						AND t1.idsite = ?
					GROUP BY t4.idaction";

		$query = $archiveProcessing->db->query($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ));
		$this->updateActionsTableWithRowQuery($query);

		$dataTable = Piwik_ArchiveProcessing_Day::generateDataTable($this->actionsTable);
		//$this->deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized();
		$archiveProcessing->insertBlobRecord('thoughtfarmer_page_title', $s);
		destroy($dataTable);
	}

	/*
	 * archive for day period
	 */
	function archiveDay( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();

		$this->archiveTopUsers($archiveProcessing);
		$this->archiveTopKeywords($archiveProcessing);
		$this->archiveActions($archiveProcessing);
	}

	/*
	 * archive for week / month / year periods
	 */
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		$archiveProcessing->archiveDataTable(array('thoughtfarmer_page_hierarchy'));		
		$archiveProcessing->archiveDataTable(array('thoughtfarmer_page_title'), null, 500, null, 'nb_hits');
		$archiveProcessing->archiveDataTable(array('thoughtfarmer_user'));		
		$archiveProcessing->archiveDataTable(array('thoughtfarmer_search'), null, 200, null, 'nb_searches');
	}

	static public function recordSearch($idvisit, $searchPhrase)
	{
	    $insertQuery = "INSERT INTO `".Piwik_Common::prefixTable('thoughtfarmer_search')."` ".
	    " (`idvisit`,`search_phrase`) VALUES(?,?)";

	    $params = array( $idvisit, $searchPhrase );
		
	    Piwik_Tracker::getDatabase()->query($insertQuery, $params);
	}

	/*
	 * Explodes action name into an array of elements.
	 */
	protected function getActionExplodedNames($name)
	{
		$name = str_replace("\n", "", $name);
		$split = explode(self::$categoryDelimiter, $name, self::$limitLevelSubCategory);

		// trim every category and remove empty categories
		$split = array_map('trim', $split);
		$split = array_filter($split, 'strlen');

		if( empty($split) )
		{
			return false; //array( trim(self::$defaultName) );
		}

		return array_values( $split );
	}

	protected function updateActionsTableWithRowQuery($query)
	{		
		while( $row = $query->fetch() )
		{
			$actionExplodedNames = $this->getActionExplodedNames($row['name']);

			// we skip action that do not have name set
			if( $actionExplodedNames === false )
			{
				continue;
			}
			
			// we work on the root table of the given TYPE (either ACTION_URL or DOWNLOAD or OUTLINK etc.)
			$currentTable =& $this->actionsTable;

			// go to the level of the subcategory
			$end = count($actionExplodedNames)-1;
			for($level = 0 ; $level < $end; $level++)
			{
				$actionCategory = $actionExplodedNames[$level];
				$currentTable =& $currentTable[$actionCategory];
			}
			$actionName = $actionExplodedNames[$end];

			// we need to prefix the page
			// so that if a page has the same name as a category
			$actionName = ' ' . $actionName;

			$currentTable =& $currentTable[$actionName];

			// add the row to the matching sub category subtable
			if(!($currentTable instanceof Piwik_DataTable_Row))
			{

				$currentTable = new Piwik_DataTable_Row(array(
							Piwik_DataTable_Row::COLUMNS => array('label' => (string)$actionName),
							Piwik_DataTable_Row::METADATA => array('url' => (string)$row['url']),
						));
			}

			foreach($row as $name => $value)
			{

				if($name != 'name' && $name != 'url')
				{
					if(($alreadyValue = $currentTable->getColumn($name)) !== false)
					{
						$currentTable->setColumn($name, $alreadyValue+$value);
					}
					else
					{
						$currentTable->addColumn($name, $value);
					}
				}
			}
		}
		// just to make sure php copies the last $currentTable in the $parentTable array
		$currentTable =& $this->actionsTable;
	}

	public function install()
	{
		// add column to save username
		$queryAddUsername = "ALTER TABLE `".Piwik_Common::prefixTable('log_visit')."`
		    ADD COLUMN `thoughtfarmer_username` VARCHAR(255) DEFAULT NULL";

		$queryAddIdactionThoughtFarmer = "ALTER TABLE `".Piwik_Common::prefixTable('log_link_visit_action')."`
		    ADD COLUMN `idaction_thoughtfarmer`  int(10) unsigned DEFAULT NULL AFTER `idaction_name`";

		$queryCreateThoughtFarmerAction = "CREATE TABLE `".Piwik_Common::prefixTable('thoughtfarmer_action')."` (
				`idvisit` int(10) unsigned NOT NULL,
				`idaction_url` int(10) unsigned NOT NULL,
				`idaction_name` int(10) unsigned DEFAULT NULL,
				`idaction_thoughtfarmer` int(10) unsigned DEFAULT NULL,
				`type` int(10) unsigned NOT NULL,
				KEY index_idvisit_type (`idvisit`,`type`)
			);";

		$queryCreateThoughtFarmerSearch = "CREATE TABLE `".Piwik_Common::prefixTable('thoughtfarmer_search')."` (
				`idvisit` int(10) unsigned NOT NULL,
				`search_phrase` VARCHAR(64),
				KEY index_idvisit_search (`idvisit`, `search_phrase`)
			);";

		try {
			Piwik_Exec($queryAddUsername);
		}
		catch(Exception $e){}

		try {
			Piwik_Exec($queryAddIdactionThoughtFarmer);
		}
		catch(Exception $e){}

		try {
			Piwik_Exec($queryCreateThoughtFarmerSearch);
		}
		catch(Exception $e){}
		
		try {
			Piwik_Exec($queryCreateThoughtFarmerAction);
		}
		catch(Exception $e){}		

	}
}
