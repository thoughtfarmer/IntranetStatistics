<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package PluginsFunctions
 */

/**
 * @package PluginsFunctions
 */
class Piwik_WidgetsList
{
    /**
     * @var Piwik_WidgetsList
     */
    static protected $_instance = null;

    /**
     * Contains all available widgets
     * @var array
     */
    protected $widgets = array();

    protected $eventPosted = false;

    /**
     * non public constructor
     */
    protected function __construct()
    {
    }

    /**
     * Returns the singleton instance
     *
     * @static
     * @return Piwik_WidgetsList
     */
    static public function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function _loadWidgetsList ()
    {
        if (!$this->eventPosted) {
            Piwik_PostEvent('WidgetsList.add');
            $this->eventPosted = true;

            // append all available datatables as widgets
            $dataTables = Piwik_DataTableList::getInstance()->get();

            foreach ($dataTables AS $table) {
                if ($table['isAvailableAsWidget']) {
                    self::add($table['category'], $table['name'], $table['uniqueId'], $table['defaultParameters']);
                }
            }
        }

        uksort($this->widgets, array($this, '_sortWidgetCategories'));

        $widgets = array();
        foreach ($this->widgets as $key => $v) {
            if (isset($widgets[Piwik_Translate($key)])) {
                $v = array_merge($widgets[Piwik_Translate($key)], $v);
            }
            $widgets[Piwik_Translate($key)] = $v;
        }
        $this->widgets = $widgets;
    }

    public function add($widgetCategory, $widgetName, $widgetUniqueId, $customParameters)
    {
        $widgetName = Piwik_Translate($widgetName);

        if (empty($customParameters['module']) && empty($customParameters['action'])) {
            $customParameters['module'] = 'CoreHome';
            $customParameters['action'] = 'renderDataTableWidget';
        }

        $this->widgets[$widgetCategory][] = array(
            'name'              => $widgetName,
            'uniqueId'          => $widgetUniqueId,
            'parameters'        => array(
                                         'module' => $customParameters['module'],
                                         'action' => $customParameters['action']
                                   ),
            'defaultParameters' => $customParameters
        );
    }

    /**
     * Return all available widgets grouped by category
     *
     * @return array
     */
    public function get()
    {
        $this->_loadWidgetsList();
        return $this->widgets;
    }

    /**
     * Returns the data of the widget with the given uniqueId
     *
     * @param string $uniqueId
     *
     * @return array
     */
    public function getWidgetByUniqueId($uniqueId)
    {
        foreach ($this->get() AS $widgets) {
            foreach ($widgets AS $widget) {
                if ($widget['uniqueId'] == $uniqueId) {
                    return $widget;
                }
            }
        }
        return array();
    }

    /**
     * Checks if a widget with the given uniqueId is defined
     *
     * @param $uniqueId
     *
     * @return bool
     */
    public function isDefined ($uniqueId)
    {
        $widget = $this->getWidgetByUniqueId($uniqueId);
        return !empty($widget);
    }

    /**
     * Sorting method for widget categories
     *
     * @param string  $a
     * @param string  $b
     *
     * @return bool
     */
    protected function _sortWidgetCategories($a, $b)
    {
        $order = array(
            'VisitsSummary_VisitsSummary',
            'Live!',
            'General_Visitors',
            'UserSettings_VisitorSettings',
            'Actions_Actions',
            'Actions_SubmenuSitesearch',
            'Referers_Referers',
            'Goals_Goals',
            'Goals_Ecommerce',
            '_others_',
            'Example Widgets',
            'ExamplePlugin_exampleWidgets',
        );

        if (($oa = array_search($a, $order)) === false) {
            $oa = array_search('_others_', $order);
        }
        if (($ob = array_search($b, $order)) === false) {
            $ob = array_search('_others_', $order);
        }
        return $oa > $ob;
    }


    /**
     * Method to reset the widget list
     * For testing only
     */
    public static function _reset() {
        self::$_instance = new self();
    }
}

/**
 * Returns all available widgets
 *
 * @see Piwik_WidgetsList::get
 *
 * @return array
 *
 * @deprecated since 1.10
 */
function Piwik_GetWidgetsList()
{
	return Piwik_WidgetsList::getInstance()->get();
}

/**
 * Adds an widget to the list
 *
 * @see Piwik_WidgetsList::add
 *
 * @param string  $widgetCategory
 * @param string  $widgetName
 * @param string  $controllerName
 * @param string  $controllerAction
 * @param array   $customParameters
 *
 * @deprecated since 1.10
 */
function Piwik_AddWidget( $widgetCategory, $widgetName, $controllerName, $controllerAction, $customParameters = array())
{
    $widgetUniqueId = 'widget' . $controllerName . $controllerAction;
    foreach ($customParameters as $name => $value) {
        if (is_array($value)) {
            // use 'Array' for backward compatibility;
            // could we switch to using $value[0]?
            $value = 'Array';
        }
        $widgetUniqueId .= $name . $value;
    }

    $customParameters['module'] = $controllerName;
    $customParameters['action'] = $controllerAction;

    Piwik_WidgetsList::getInstance()->add($widgetCategory, $widgetName, $widgetUniqueId, $customParameters);
}

/**
 * Checks if the widget with the given parameters exists in der widget list
 *
 * @see Piwik_WidgetsList::isDefined
 *
 * @param string  $controllerName
 * @param string  $controllerAction
 * @return bool
 *
 * @deprecated since 1.10
 */
function Piwik_IsWidgetDefined($controllerName, $controllerAction)
{
    $widgetUniqueId = 'widget' . $controllerName . $controllerAction;
    return Piwik_WidgetsList::getInstance()->isDefined($widgetUniqueId);
}
