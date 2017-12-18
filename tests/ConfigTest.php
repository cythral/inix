<?php

use \PHPUnit\Framework\TestCase;
use inix\Config as inix;

class ConfigTest extends TestCase {

    public function testValidConfig() {
        // load config file
        $this->assertTrue(inix::load(dirname(__DIR__)."/example.ini"));

        // test database section
        $this->assertEquals([
            "host" => "localhost",
            "user" => "root",
            "pass" => "password",
            "name" => "inix"
        ], inix::get("database"));
        $this->assertEquals("localhost", inix::get("database.host"));
        $this->assertEquals("root", inix::get("database.user"));
        $this->assertEquals("password", inix::get("database.pass"));
        $this->assertEquals("inix", inix::get("database.name"));
        
        // try setting a value
        $this->assertTrue(inix::set("database.host", "localhost2"));
        $this->assertEquals("localhost2", inix::get("database.host"));

        // try setting an unsectioned value
        $this->assertTrue(inix::set("key", "value2"));
        $this->assertEquals("value2", inix::get("key"));

        // cleanup - restore defaults
        $this->assertTrue(inix::set("key", "value"));
        $this->assertTrue(inix::set("database.host", "localhost"));
    }
}