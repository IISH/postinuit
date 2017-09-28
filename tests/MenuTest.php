<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 31-8-2017
 * Time: 11:35
 */

require_once "./classes/menu.inc.php";
use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase
{
    protected $menu;

    public function setUp(){
        $this->menu = array();
        $this->menu[] = new MenuItem("Home", "home.php", "Menu");
        $this->menu[] = new MenuItem("About", "about.php", "Menu");
        $this->menu[] = new MenuItem("Contact", "contact.php", "Menu");
    }

    public function testMenuArrayIsNotEmpty(){
        $this->assertNotEmpty($this->menu);
    }

    public function testFirstMenuItemHasLabelHome(){
        $this->assertEquals("Home", $this->menu[0]->getLabel());
    }

    public function testFirstMenuItemHasUrLHomeDotPhp(){
        $this->assertEquals("home.php", $this->menu[0]->getUrl());
    }

    public function testThirdMenuItemHasLabelContact(){
        $this->assertEquals("Contact", $this->menu[2]->getLabel());
    }

    public function testThirdMenuItemHasClassMenu(){
        $this->assertEquals("Menu", $this->menu[2]->getClass());
    }

    public function testMenuArrayContainsThreeMenuItems(){
        $this->assertEquals(3, count($this->menu));
    }

}