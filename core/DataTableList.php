<?php
/**
 * Piwik - Open source web analytics
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @category Piwik
 * @package  Piwik
 */

/**
 * @package Piwik
 */

class Piwik_DataTableList
{
	/**
	 * @var Piwik_DataTableList
	 */
	static protected $_instance = null;

	/**
	 * Contains all available datatables
	 *
	 * @var array
	 */
	protected $dataTables = array();

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
	 * @return Piwik_DataTableList
	 */
	static public function getInstance()
	{
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function _loadDataTableList()
	{
		static $eventPosted = false;

		if (!$eventPosted) {
			Piwik_PostEvent('DataTableList.add');
			$eventPosted = true;
		}
	}

	/**
	 * Adds a new datatable to the list. If category and name is given, datatable will also be available as widget
	 *
	 * @param string $uniqueId              unique id for datatable
	 * @param array  $customParameters      parameters for datatable
	 * @param string $category              widget category
	 * @param string $name                  widget name
	 */
	public function add($uniqueId, $customParameters, $category = '', $name = '')
	{
		$name = Piwik_Translate($name);

		$this->dataTables[] = array(
			'uniqueId'            => $uniqueId,
			'defaultParameters'   => $customParameters,
			'name'                => $name,
			'category'            => $category,
			'isAvailableAsWidget' => !empty($category) && !empty($name)
		);
	}

	/**
	 * Return all available datatables grouped by category
	 *
	 * @return array
	 */
	public function get()
	{
		$this->_loadDataTableList();
		return $this->dataTables;
	}

	/**
	 * Returns the data of the data table with the given uniqueId
	 *
	 * @param string $uniqueId
	 *
	 * @return array
	 */
	public function getDataTableByUniqueId($uniqueId)
	{
		foreach ($this->get() AS $dataTable) {
			if ($dataTable['uniqueId'] == $uniqueId) {
				return $dataTable;
			}
		}
		return array();
	}

	/**
	 * Checks if a datatable with the given uniqueId is defined
	 *
	 * @param $uniqueId
	 *
	 * @return bool
	 */
	public function isDefined($uniqueId)
	{
		$dataTable = $this->getDataTableByUniqueId($uniqueId);
		return !empty($dataTable);
	}
}
