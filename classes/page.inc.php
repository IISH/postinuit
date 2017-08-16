<?php 
require_once dirname(__FILE__) . "/misc.inc.php";

class Page {
	protected $page_template;
	protected $content;
	protected $tab;
	protected $title;
	protected $color;
	protected $favicon;

	function __construct() {
	}

	public function getPageAttributes( $extra = '' ) {
		global $oWebuser;

		$arr = array();

		$arr['content'] = $this->content;
		$arr['title'] = $this->title;
		$arr['favicon'] = $this->favicon;
		$arr['color'] = $this->color;
		$arr['menu'] = $this->createMenu();

		//
		$welcome = Translations::get('welcome');
		$logout = '';
		if ( $oWebuser->isLoggedIn() ) {
			$niceName = trim($oWebuser->getName());
			if ( $niceName == '' ) {
				$niceName = '...';
			}
			$niceName = '<a href="user.php">' . $niceName . '</a>';

			$welcome .= ', ' . $niceName;

			$logout = '<a href="logout.php" onclick="if (!confirm(\'' . Translations::get('confirm') . '\')) return false;">(' . Translations::get('logout') . ')</a>';
		} else {
			$logout = '<a href="login.php">(' . Translations::get('login') . ')</a>';;
		}
		$arr['welcome'] = $welcome;
		$arr['logout'] = $logout;
		$arr['website_name'] = Translations::get('website_name');
		$arr['contact'] = Translations::get('contact');

		// add extra settings
		if ( is_array($extra) ) {
			$arr = array_merge($arr, $extra);
		}

		return $arr;
	}

	private function createMenu() {
		global $menu;

		$sMenu = "<ul>";

		//
		foreach ( $menu as $a ) {
			$sMenu .= "				<li class=\"" . $a->getClass() . "\"><a href=\"" . $a->getUrl() . "\">" . $a->getLabel() . "</a></li>\n";
		}

		$sMenu .= "</ul>";

		return $sMenu;
	}

	public function setContent( $content ) {
		$this->content = $content;
	}

	public function setTitle( $title ) {
		$this->title = $title;
	}
}
