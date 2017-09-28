<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 31-8-2017
 * Time: 11:10
 */

require_once "./classes/page.inc.php";
require_once "./classes/menu.inc.php";
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    protected $page;
    protected $menu;

    public function setUp(){
//        $this->menu = array();
//        $this->menu[] = new MenuItem("Home", "home.php", "Menu");
//        $this->menu[] = new MenuItem("About", "about.php", "Menu");
//        $this->menu[] = new MenuItem("Contact", "contact.php", "Menu");
        $this->page = new Page();
        $this->page->setTitle("Test");
        $this->page->setContent("Foobar");
    }

    public function testCreateMenuCreatesCorrectMenu(){
        print_r($this->page);
        $temp = $this->page->getPageAttributes();
        print_r($temp);
    }
}