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

class Piwik_ThoughtFarmer_Action extends Piwik_Tracker_Action
{
	protected $idThoughtFarmerAction = null;
	protected $thoughtFarmerActionName = null;

	// ThoughtFarmer actions
	const THOUGHTFARMER_PAGE_CREATE = 1;
	const THOUGHTFARMER_PAGE_EDIT = 2;
	const THOUGHTFARMER_PAGE_COMMENT = 3;

	// Action type used to store structure action names in piwik_log_action
	const TYPE_ACTION_THOUGHTFARMER = 10;

	public function getThoughtFarmerActionName()
	{
		return $this->thoughtFarmerActionName;
	}

	public function getIdThoughtFarmerAction()
	{
		return $this->idThoughtFarmerAction;
	}

	// rewrite action name in order to use site's main_url
	protected function rewriteActionName()
	{
		$websiteData = Piwik_Common::getCacheWebsiteAttributes($this->getIdSite());

		if(isset($websiteData['hosts']))
		{
			$hosts = $websiteData['hosts'];
			$mainUrl = array_shift($hosts);

			$actionUrl = $this->getActionUrl();

			$actionUrlParsed = @parse_url(Piwik_Common::unsanitizeInputValue($actionUrl));

			if(!isset($actionUrlParsed['host']))
			{
				return false;
			}


			if( in_array($actionUrlParsed['host'], $hosts) )
			{
				$newActionUrl = str_replace("://".$actionUrlParsed['host'], "://".$mainUrl, $actionUrl);
				$this->setActionUrl($newActionUrl);

				printDebug("Rewriting ".$actionUrlParsed['host']." to ".$mainUrl);
				
				return true;
			}
		}
		return false;
	}
	
	public function loadIdActionNameAndUrl()
	{
		$this->rewriteActionName();
		
		parent::loadIdActionNameAndUrl();

		if( !is_null($this->idThoughtFarmerAction) || is_null($this->getThoughtFarmerActionName()) )
		{
			return;
		}
		
		$idAction = Piwik_Tracker::getDatabase()->fetchOne("SELECT idaction
						    FROM ".Piwik_Common::prefixTable('log_action')
						."  WHERE hash = CRC32(?) AND name = ? AND type = ? ",
						array($this->getThoughtFarmerActionName(), $this->getThoughtFarmerActionName(),
							self::TYPE_ACTION_THOUGHTFARMER));

		if( $idAction !== false )
		{
			$this->idThoughtFarmerAction = $idAction;
		}
		else
		{
			$sql = "INSERT INTO ". Piwik_Common::prefixTable('log_action').
							"( name, hash, type ) VALUES (?,CRC32(?),?)";

			Piwik_Tracker::getDatabase()->query($sql,
				array($this->getThoughtFarmerActionName(), $this->getThoughtFarmerActionName(),
					self::TYPE_ACTION_THOUGHTFARMER));
			
			$this->idThoughtFarmerAction = Piwik_Tracker::getDatabase()->lastInsertId();
		}
	}



	protected function recordAction( $idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction )
	{
		$this->loadIdActionNameAndUrl();

		$idActionName = $this->getIdActionName();
		if(is_null($idActionName))
		{
			$idActionName = 0;
		}
		
		$idThoughtFarmer = $this->getIdThoughtFarmerAction();
		if(is_null($idThoughtFarmer))
		{
			$idThoughtFarmer = 0;
		}

		Piwik_Tracker::getDatabase()->query("INSERT INTO ".Piwik_Common::prefixTable('log_link_visit_action')
                                                ." (idvisit, idsite, idvisitor, server_time, idaction_url, idaction_name, idaction_url_ref, idaction_name_ref, idaction_thoughtfarmer, time_spent_ref_action)
                                                        VALUES (?,?,?,?,?,?,?,?,?,?)",
						array(  $idVisit,
                                                        $this->getIdSite(),
                                                        $visitorIdCookie,
                                                        Piwik_Tracker::getDatetimeFromTimestamp($this->timestamp),
                                                        $this->getIdActionUrl(),
                                                        $idActionName ,
                                                        $idRefererActionUrl,
                                                        $idRefererActionName,
							$idThoughtFarmer,
                                                        $timeSpentRefererAction
                ));


		$idLinkVisitAction = Piwik_Tracker::getDatabase()->lastInsertId();

                $info = array(
                        'idSite' => $this->getIdSite(),
                        'idLinkVisitAction' => $this->idLinkVisitAction,
                        'idVisit' => $idVisit,
                        'idRefererActionUrl' => $idRefererActionUrl,
                        'idRefererActionName' => $idRefererActionName,
			'idThoughtFarmer' => $idThoughtFarmer,
                        'timeSpentRefererAction' => $timeSpentRefererAction,
                );
                printDebug($info);


		/*
		* send the Action object ($this)  and the list of ids ($info) as arguments to the event
		*/
		Piwik_PostEvent('Tracker.Action.record', $this, $info);
	 }

	protected function recordThoughtFarmerAction($idVisit, $type)
	{
		$this->loadIdActionNameAndUrl();
		
		$idActionName = $this->getIdActionName();
		$idActionUrl = $this->getIdActionUrl();
		
		if(is_null($idActionName))
		{
			$idActionName = 0;
		}

		$idThoughtFarmer = $this->getIdThoughtFarmerAction();
		if(is_null($idThoughtFarmer))
		{
			$idThoughtFarmer = 0;
		}

		printDebug("ThoughtFarmer::recording thoughtfarmer action type=$type");

		Piwik_Tracker::getDatabase()->query("INSERT INTO ".Piwik_Common::prefixTable('thoughtfarmer_action')
					." (idvisit, idaction_url, idaction_name, idaction_thoughtfarmer, type)
						VALUES (?,?,?,?,?)",
				array($idVisit, $idActionUrl, $idActionName, $idThoughtFarmer, $type)
		);
	}
	
	public function record($idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction)
	{
		// should we record "standard" piwik action
		$recordAction = true;

		// check if there are custom Piwik var passed
		if( isset($_GET['data']) && ($customVariables = json_decode($_GET['data'],true)) !== null )
		{
			if( isset($customVariables['ThoughtFarmer_action']) )
			{
				$this->thoughtFarmerActionName = $customVariables['ThoughtFarmer_action'];

				printDebug("ThoughtFarmer::detected thoughtfarmer action");
			}

			if( isset($customVariables['ThoughtFarmer_search']) )
			{
				$searchPhrase = strtolower(urldecode($customVariables['ThoughtFarmer_search']));

				printDebug("ThoughtFarmer::recording search");
				
				Piwik_ThoughtFarmer::recordSearch($idVisit, $searchPhrase);

				$recordAction = false;
			}

			if( isset($customVariables['ThoughtFarmer_page_create']) )
			{
				printDebug("ThoughtFarmer::recording page edit in action");

				$this->recordThoughtFarmerAction($idVisit, self::THOUGHTFARMER_PAGE_CREATE);

				$recordAction = false;
			}

			if( isset($customVariables['ThoughtFarmer_page_edit']) )
			{
				printDebug("ThoughtFarmer::recording page edit in action");

				$this->recordThoughtFarmerAction($idVisit, self::THOUGHTFARMER_PAGE_EDIT);
				
				$recordAction = false;
			}

			if( isset($customVariables['ThoughtFarmer_page_comment']) )
			{
				printDebug("ThoughtFarmer::recording page comment in action");

				$this->recordThoughtFarmerAction($idVisit, self::THOUGHTFARMER_PAGE_COMMENT);

				$recordAction = false;
			}

		}

		if ($recordAction === true)
		{
			$this->recordAction($idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction);
		}
	}
}
