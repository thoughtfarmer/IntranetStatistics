<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 *
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_Controller extends Piwik_Controller
{
    function getDefaultAction()
    {
        return 'redirectToCoreHomeIndex';
    }

    function redirectToCoreHomeIndex()
    {
        $defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
        $module = 'CoreHome';
        $action = 'index';

        // User preference: default report to load is the All Websites dashboard
        if ($defaultReport == 'MultiSites'
            && Piwik_PluginsManager::getInstance()->isPluginActivated('MultiSites')
        ) {
            $module = 'MultiSites';
        }
        if ($defaultReport == Piwik::getLoginPluginName()) {
            $module = Piwik::getLoginPluginName();
        }
        $idSite = Piwik_Common::getRequestVar('idSite', false, 'int');

        parent::redirectToIndex($module, $action, !empty($idSite) ? $idSite : null);
    }

    public function showInContext()
    {
        $controllerName = Piwik_Common::getRequestVar('moduleToLoad');
        $actionName = Piwik_Common::getRequestVar('actionToLoad', 'index');
        if ($actionName == 'showInContext') {
            throw new Exception("Preventing infinite recursion...");
        }
        $view = $this->getDefaultIndexView();
        $view->content = Piwik_FrontController::getInstance()->fetchDispatch($controllerName, $actionName);
        echo $view->render();
    }

    protected function getDefaultIndexView()
    {
        $view = Piwik_View::factory('index');
        $this->setGeneralVariablesView($view);
        $view->menu = Piwik_GetMenu();
        $view->content = '';
        return $view;
    }

    protected function setDateTodayIfWebsiteCreatedToday()
    {
        $date = Piwik_Common::getRequestVar('date', false);
        if ($date == 'today'
            || Piwik_Common::getRequestVar('period', false) == 'range'
        ) {
            return;
        }
        $websiteId = Piwik_Common::getRequestVar('idSite', false, 'int');
        if ($websiteId) {
            $website = new Piwik_Site($websiteId);
            $datetimeCreationDate = $this->site->getCreationDate()->getDatetime();
            $creationDateLocalTimezone = Piwik_Date::factory($datetimeCreationDate, $website->getTimezone())->toString('Y-m-d');
            $todayLocalTimezone = Piwik_Date::factory('now', $website->getTimezone())->toString('Y-m-d');
            if ($creationDateLocalTimezone == $todayLocalTimezone) {
                Piwik::redirectToModule('CoreHome', 'index',
                    array('date'   => 'today',
                          'idSite' => $websiteId,
                          'period' => Piwik_Common::getRequestVar('period'))
                );
            }
        }
    }

    public function index()
    {
        $this->setDateTodayIfWebsiteCreatedToday();
        $view = $this->getDefaultIndexView();
        echo $view->render();
    }

    /*
     * This method is called when the asset manager is configured in merged mode.
     * It returns the content of the css merged file.
     *
     * @see core/AssetManager.php
     */
    public function getCss()
    {
        $cssMergedFile = Piwik_AssetManager::getMergedCssFileLocation();
        Piwik::serveStaticFile($cssMergedFile, "text/css");
    }

    /*
     * This method is called when the asset manager is configured in merged mode.
     * It returns the content of the js merged file.
     *
     * @see core/AssetManager.php
     */
    public function getJs()
    {
        $jsMergedFile = Piwik_AssetManager::getMergedJsFileLocation();
        Piwik::serveStaticFile($jsMergedFile, "application/javascript; charset=UTF-8");
    }


    //  --------------------------------------------------------
    //  ROW EVOLUTION
    //  The following methods render the popover that shows the
    //  evolution of a singe or multiple rows in a data table
    //  --------------------------------------------------------

    /**
     * This static cache is necessary because the signature cannot be modified
     * if the method renders a ViewDataTable. So we use it to pass information
     * to getRowEvolutionGraph()
     * @var Piwik_CoreHome_DataTableAction_Evolution
     */
    private static $rowEvolutionCache = null;

    /** Render the entire row evolution popover for a single row */
    public function getRowEvolutionPopover()
    {
        $rowEvolution = $this->makeRowEvolution($isMulti = false);
        self::$rowEvolutionCache = $rowEvolution;
        $view = Piwik_View::factory('popover_rowevolution');
        echo $rowEvolution->renderPopover($this, $view);
    }

    /** Render the entire row evolution popover for multiple rows */
    public function getMultiRowEvolutionPopover()
    {
        $rowEvolution = $this->makeRowEvolution($isMulti = true);
        self::$rowEvolutionCache = $rowEvolution;
        $view = Piwik_View::factory('popover_multirowevolution');
        echo $rowEvolution->renderPopover($this, $view);
    }

    /** Generic method to get an evolution graph or a sparkline for the row evolution popover */
    public function getRowEvolutionGraph($fetch = false)
    {
        $rowEvolution = self::$rowEvolutionCache;
        if ($rowEvolution === null) {
            $paramName = Piwik_CoreHome_DataTableRowAction_MultiRowEvolution::IS_MULTI_EVOLUTION_PARAM;
            $isMultiRowEvolution = Piwik_Common::getRequestVar($paramName, false, 'int');

            $rowEvolution = $this->makeRowEvolution($isMultiRowEvolution, $graphType = 'graphEvolution');
            $rowEvolution->useAvailableMetrics();
            self::$rowEvolutionCache = $rowEvolution;
        }

        $view = $rowEvolution->getRowEvolutionGraph();
        return $this->renderView($view, $fetch);
    }

    /** Utility function. Creates a RowEvolution instance. */
    private function makeRowEvolution($isMultiRowEvolution, $graphType = null)
    {
        if ($isMultiRowEvolution) {
            return new Piwik_CoreHome_DataTableRowAction_MultiRowEvolution($this->idSite, $this->date, $graphType);
        } else {
            return new Piwik_CoreHome_DataTableRowAction_RowEvolution($this->idSite, $this->date, $graphType);
        }
    }

    /**
     * Forces a check for updates and re-renders the header message.
     *
     * This will check piwik.org at most once per 10s.
     */
    public function checkForUpdates()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $this->checkTokenInUrl();

        // perform check (but only once every 10s)
        Piwik_UpdateCheck::check($force = false, Piwik_UpdateCheck::UI_CLICK_CHECK_INTERVAL);

        $view = Piwik_View::factory('header_message');
        $this->setGeneralVariablesView($view);
        echo $view->render();
    }

    public function renderDataTable($uniqueId = false)
    {
        if (empty($uniqueId)) {
            $uniqueId = Piwik_Common::getRequestVar('uniqueId', '', 'string');
        }
        $uniqueId = str_replace('DataTable', '', $uniqueId);
        $dataTable = Piwik_DataTableList::getInstance()->getDataTableByUniqueId($uniqueId);

        if (empty($dataTable)) {
            return '';
        }

        return $this->_renderDataTable($dataTable, __FUNCTION__);
    }

    public function renderDataTableWidget()
    {
        $uniqueId = Piwik_Common::getRequestVar('uniqueId', false, 'string');
        $widget = Piwik_WidgetsList::getInstance()->getWidgetByUniqueId($uniqueId);

        if (empty($widget)) {
            return '';
        }

        return $this->_renderDataTable($widget, __FUNCTION__);
    }

    protected function _renderDataTable($widget, $function)
    {
        $defaultParameters = $widget['defaultParameters'];
        $uniqueId          = $widget['uniqueId'].'DataTable';

        // check if a subtable should be rendered
        $renderSubTable = Piwik_Common::getRequestVar('renderSubTable', 0, 'int');
        $idSubTable = Piwik_Common::getRequestVar('idSubtable', 0, 'int');
        if ($renderSubTable == 1 && !empty($defaultParameters['subTable']) && $idSubTable) {

            $defaultParameters = $defaultParameters['subTable'];
            $uniqueId          = 'subDataTable_'.$idSubTable;
        }

        $apiMethod = Piwik_Common::getRequestVar('apiMethod', false, 'string', $defaultParameters);

        if (empty($apiMethod)) {
            return '';
        }

        $viewDataTable = Piwik_Common::getRequestVar('viewDataTable', 'table', 'string', $defaultParameters);

        $view = Piwik_ViewDataTable::factory($viewDataTable);
        $view->setUniqueIdViewDataTable($uniqueId);

        $subTable = Piwik_Common::getRequestVar('subTable', array(), 'array', $defaultParameters);

        $view->init( $this->pluginName,  $function, $apiMethod, !empty($subTable) );
        $this->setPeriodVariablesView($view);
        $this->setMetricsVariablesView($view);

        $columnsToDisplay = Piwik_Common::getRequestVar('columns', false, 'string');

        if (empty($columnsToDisplay)) {

            $columnsToDisplay = Piwik_Common::getRequestVar('columnsToDisplay', false, 'string', $defaultParameters);
        }

        if (!empty($columnsToDisplay) && method_exists($view, 'setColumnsToDisplay')) {

            if (is_string($columnsToDisplay)) {

                $columnsToDisplay = explode(',', $columnsToDisplay);
            }

            $view->setColumnsToDisplay( $columnsToDisplay );
        }

        $selectableColumns = Piwik_Common::getRequestVar('selectableColumns', array(), 'array', $defaultParameters);

        if (!empty($selectableColumns) && method_exists($view, 'setSelectableColumns')) {

            $view->setSelectableColumns( $selectableColumns );
        }

        $disableSubTableWhenShowGoals = Piwik_Common::getRequestVar('disableSubTableWhenShowGoals', array(), 'array', $defaultParameters);

        if (!empty($disableSubTableWhenShowGoals) && method_exists($view, 'disableSubTableWhenShowGoals')) {

            $view->disableSubTableWhenShowGoals();
        }

        $showAllTicks = Piwik_Common::getRequestVar('showAllTicks', false, null, $defaultParameters);

        if (!empty($showAllTicks) && method_exists($view, 'showAllTicks')) {

            $view->showAllTicks();
        }

        $defaultSort      = Piwik_Common::getRequestVar('defaultSort', '', 'string', $defaultParameters);
        $defaultSortOrder = Piwik_Common::getRequestVar('defaultSortOrder', 'desc', 'string', $defaultParameters);

        if (!empty($defaultSort)) {

            $view->setSortedColumn( $defaultSort, $defaultSortOrder );
        }

        $disableSort = Piwik_Common::getRequestVar('disableSort', false, null, $defaultParameters);

        if ($disableSort === true) {

            $view->disableSort();
        }

        $showGoals = Piwik_Common::getRequestVar('showGoals', false, null, $defaultParameters);

        if ($showGoals === true) {

            $view->enableShowGoals();
        }

        $disablePaginationControl = Piwik_Common::getRequestVar('disablePaginationControl', false, null, $defaultParameters);

        if ($disablePaginationControl === true) {

            $view->disableShowPaginationControl();
        }

        $disableExcludeLowPopulation = Piwik_Common::getRequestVar('disableExcludeLowPopulation', false, null, $defaultParameters);

        if ($disableExcludeLowPopulation === true) {

            $view->disableExcludeLowPopulation();
        }

        $disableOffsetInformation = Piwik_Common::getRequestVar('disableOffsetInformation', false, null, $defaultParameters);

        if ($disableOffsetInformation === true) {

            $view->disableOffsetInformation();
        }

        $disableShowBarChart = Piwik_Common::getRequestVar('disableShowBarChart', false, null, $defaultParameters);

        if ($disableShowBarChart === true) {

            $view->disableShowBarChart();
        }

        $disableRowEvolution = Piwik_Common::getRequestVar('disableRowEvolution', false, null, $defaultParameters);

        if ($disableRowEvolution === true && method_exists($view, 'disableRowEvolution')) {

            $view->disableRowEvolution();
        }

        $disableShowAllViewsIcons = Piwik_Common::getRequestVar('disableShowAllViewsIcons', false, null, $defaultParameters);

        if ($disableShowAllViewsIcons === true) {

            $view->disableShowAllViewsIcons();
        }

        $disableShowAllColumns = Piwik_Common::getRequestVar('disableShowAllColumns', false, null, $defaultParameters);

        if ($disableShowAllColumns === true) {

            $view->disableShowAllColumns();
        }

        $disableRowActions = Piwik_Common::getRequestVar('disableRowActions', false, null, $defaultParameters);

        if ($disableRowActions === true && method_exists($view, 'disableRowActions')) {

            $view->disableRowActions();
        }

        $disableGenericFilters = Piwik_Common::getRequestVar('disableGenericFilters', false, null, $defaultParameters);

        if ($disableGenericFilters === true) {

            $view->disableGenericFilters();
        }

        $disableShowExportAsRssFeed = Piwik_Common::getRequestVar('disableShowExportAsRssFeed', false, null, $defaultParameters);

        if ($disableShowExportAsRssFeed === true) {

            $view->disableShowExportAsRssFeed();
        }

        $limit = Piwik_Common::getRequestVar('limit', 0, null, $defaultParameters);

        if ($limit > 0) {

            $view->setLimit($limit);
        }

        $limitGraph = Piwik_Common::getRequestVar('limitGraph', 0, null, $defaultParameters);

        if ($limitGraph > 0 && method_exists($view, 'setGraphLimit')) {

            $view->setGraphLimit($limitGraph);
        }

        $disableSearch = Piwik_Common::getRequestVar('disableSearch', false, null, $defaultParameters);

        if ($disableSearch === true) {

            $view->disableSearchBox();
        }

        $addTotalRow = Piwik_Common::getRequestVar('addTotalRow', false, null, $defaultParameters);

        if ($addTotalRow === true) {

            $view->addTotalRow();
        }

        if (!empty($defaultParameters['footerMessage'])) {

            $view->setFooterMessage($defaultParameters['footerMessage']);
        }

        $view->setColumnTranslation('nb_conversions', Piwik_Translate('Goals_ColumnConversions'));
        $view->setColumnTranslation('revenue', Piwik_Translate('General_TotalRevenue'));

        $columnsToTranslate = Piwik_Common::getRequestVar('columnsToTranslate', array(), 'array', $defaultParameters);

        if (!empty($columnsToTranslate) && is_array($columnsToTranslate)) {

            foreach ($columnsToTranslate AS $label => $translationKey) {

                $view->setColumnTranslation($label, Piwik_Translate($translationKey));
            }
        }

        $reportDocumentation = Piwik_Common::getRequestVar('reportDocumentation', false, 'string', $defaultParameters);

        if (!empty($reportDocumentation)) {

            $view->setReportDocumentation(Piwik_Translate($reportDocumentation));
        }

        $template = Piwik_Common::getRequestVar('template', false, 'string', $defaultParameters);

        if (!empty($template)) {

            $view->setTemplate($template);
        }

        $customParameters = Piwik_Common::getRequestVar('customParameters', array(), 'array', $defaultParameters);

        if (!empty($customParameters) && is_array($customParameters)) {

            foreach ($customParameters AS $param => $value) {

                $view->setCustomParameter($param, $value);
            }
        }

        $relatedReports = Piwik_Common::getRequestVar('relatedReports', array(), 'array', $defaultParameters);

        if (!empty($relatedReports) && is_array($relatedReports) && count($relatedReports) == 2 && is_array($relatedReports[1])) {

            $view->setReportTitle($relatedReports[0]);

            foreach ($relatedReports[1] AS $relatedReport => $title) {

                $view->addRelatedReport('CoreHome', $function, $title, array('uniqueId' => $relatedReport));
            }
        }

        $customFilter = Piwik_Common::getRequestVar('customFilter', array(), 'array', $defaultParameters);

        if (!empty($customFilter) && is_array($customFilter)) {
            
            call_user_func($customFilter, $view);
        }

        return $this->renderView($view);
    }
    
    /**
     * Renders and echo's the in-app donate form w/ slider.
     */
    public function getDonateForm()
    {
        $view = Piwik_View::factory('donate');
        if (Piwik_Common::getRequestVar('widget', false)
            && Piwik::isUserIsSuperUser()
        ) {
            $view->footerMessage = Piwik_Translate('CoreHome_OnlyForAdmin');
        }
        echo $view->render();
    }

    /**
     * Renders and echo's HTML that displays the Piwik promo video.
     */
    public function getPromoVideo()
    {
        $view = Piwik_View::factory('promo_video');
        $view->shareText = Piwik_Translate('CoreHome_SharePiwikShort');
        $view->shareTextLong = Piwik_Translate('CoreHome_SharePiwikLong');
        $view->promoVideoUrl = 'http://www.youtube.com/watch?v=OslfF_EH81g';
        echo $view->render();
    }
}
