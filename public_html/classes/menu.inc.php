<?php 
class MenuItem {
	protected $label = '';
	protected $url = '';
	protected $class = '';

	function __construct($label, $url, $class = '' ) {
		$this->label = $label;
		$this->url = $url;
		$this->class = $class;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getClass() {
		return $this->class;
	}
}
