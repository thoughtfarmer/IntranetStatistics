<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
 *
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime_Controller extends Piwik_Controller
{
    public function index()
    {
        $view = Piwik_View::factory('index');
        echo $view->render();
    }
}
