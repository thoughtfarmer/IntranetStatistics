<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: modifier.translate.php 6300 2012-05-23 21:19:25Z SteveG $
 * 
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * Renders the dataTable with the given $uniqueId
 *
 * Usage:
 *  {'widgetUserCountrygetContinent'|renderDataTable}
 *
 * @param string $uniqueId
 * @return string The HTML for the requested dataTable
 */
function smarty_modifier_renderDataTable($uniqueId)
{
    return Piwik_FrontController::getInstance()->fetchDispatch('CoreHome','renderDataTable', array($uniqueId));
}
