<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitFrequency
 */

/**
 *
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency_Controller extends Piwik_Controller
{
    function index()
    {
        $view = Piwik_View::factory('index');
        $this->setSparklinesAndNumbers($view);
        echo $view->render();
    }
    
    public function getSparklines()
    {
        $view = Piwik_View::factory('sparklines');
        $this->setSparklinesAndNumbers($view);
        echo $view->render();
    }

    protected function setSparklinesAndNumbers($view)
    {
        $view->urlSparklineNbVisitsReturning         = $this->getUrlSparklineForDataTable('widgetVisitFrequencygetEvolutionGraph', array('columns' => array('nb_visits_returning')));
        $view->urlSparklineNbActionsReturning        = $this->getUrlSparklineForDataTable('widgetVisitFrequencygetEvolutionGraph', array('columns' => array('nb_actions_returning')));
        $view->urlSparklineActionsPerVisitReturning  = $this->getUrlSparklineForDataTable('widgetVisitFrequencygetEvolutionGraph', array('columns' => array('nb_actions_per_visit_returning')));
        $view->urlSparklineAvgVisitDurationReturning = $this->getUrlSparklineForDataTable('widgetVisitFrequencygetEvolutionGraph', array('columns' => array('avg_time_on_site_returning')));
        $view->urlSparklineBounceRateReturning       = $this->getUrlSparklineForDataTable('widgetVisitFrequencygetEvolutionGraph', array('columns' => array('bounce_rate_returning')));

        $dataTableFrequency               = $this->getSummary();
        $dataRow                          = $dataTableFrequency->getFirstRow();
        $nbVisitsReturning                = $dataRow->getColumn('nb_visits_returning');
        $view->nbVisitsReturning          = $nbVisitsReturning;
        $view->nbActionsReturning         = $dataRow->getColumn('nb_actions_returning');
        $view->nbActionsPerVisitReturning = $dataRow->getColumn('nb_actions_per_visit_returning');
        $view->avgVisitDurationReturning  = $dataRow->getColumn('avg_time_on_site_returning');
        $nbBouncedReturningVisits         = $dataRow->getColumn('bounce_count_returning');
        $view->bounceRateReturning        = Piwik::getPercentageSafe($nbBouncedReturningVisits, $nbVisitsReturning);
    }

    protected function getSummary()
    {
        $requestString = "method=VisitFrequency.get&format=original";
        $request = new Piwik_API_Request($requestString);
        return $request->process();
    }
}
