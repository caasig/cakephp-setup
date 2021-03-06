<?php
namespace Setup\Shell\Traits;

use Cake\Collection\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Text;

/**
 * @mixin \Cake\Console\Shell
 */
trait DbToolsTrait {

	/**
	 * @return \Cake\Database\Connection|\Cake\Datasource\ConnectionInterface
	 */
	protected function _getConnection() {
		$name = 'default';
		if ($this->params['connection']) {
			$name = $this->params['connection'];
		}

		return ConnectionManager::get($name);
	}

	/**
	 * @param string $prefix
	 * @return array
	 */
	protected function _getTables($prefix) {
		$db = $this->_getConnection();
		$config = $db->config();
		$database = $config['database'];

		$script = "
SELECT table_name
FROM information_schema.tables AS tb
WHERE   table_schema = '$database'
AND table_name LIKE '$prefix%' OR table_name LIKE '\_%';";

		$res = $db->query($script);
		if (!$res) {
			$this->abort('Nothing to do...');
		}
		$tables = new Collection($res);

		$whitelist = Text::tokenize($this->param('table'));

		$tables = $tables->toArray();
		foreach ($tables as $key => $table) {
			if (substr($table['table_name'], 0, 1) === '_') {
				unset($tables[$key]);
			}

			if ($whitelist && !in_array($table['table_name'], $whitelist)) {
				unset($tables[$key]);
			}
		}

		return $tables;
	}

}
