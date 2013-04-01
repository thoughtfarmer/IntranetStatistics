<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorInterest
 */

/**
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest_Controller extends Piwik_Controller
{
	function index()
	{
		$view = Piwik_View::factory('index');
		echo $view->render();
	}
}
