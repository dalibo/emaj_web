<?php

/**
 * Really simple RecordSet to allow printTable of arrays.
 *
 */
class ArrayRecordSet {

	var $_array;
	var $_count;
	var $EOF = false;
	var $fields;

	function ArrayRecordSet($data) {
		$this->_array = $data;
		$this->_count = count($this->_array);
		$this->fields = reset($this->_array);
		if ($this->fields === false) $this->EOF = true;
	}

	function recordCount() {
		return $this->_count;
	}

	function moveNext() {
		$this->fields = next($this->_array);
		if ($this->fields === false) $this->EOF = true;
	}

	function moveFirst() {
		$this->fields = reset($this->_array);
		if ($this->fields === false) $this->EOF = true;
	}
}

?>
