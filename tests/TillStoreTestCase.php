<?php
require_once dirname(dirname(__FILE__)) . '/server.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @category Testing
 * @package  TillStore
 * @author   Till Klampaeckel <till@php.net>
 * @version  Release: @package_version@
 * @link     http://github.com/till/TillStore
 */
class TillStoreTestCase extends PHPUnit_Framework_TestCase
{
    protected $tillStore;

    public function setUp()
    {
        $this->tillStore = new TillStore;
    }

    public static function setGetProvider()
    {

        $roidrageResp = new stdClass;
        $roidrageResp->ruby  = 'fan';
        $roidrageResp->redis = true;
        $roidrageResp->cloud = array('ec2' => 1, 'rightscale' => 0);

        return array(
            array('foo', 'bar'),
            array('till', array('awsum' => true)),
            array('key-value', false),
            array('roidrage', $roidrageResp),
        );
    }

    /**
     * @dataProvider setGetProvider
     */
    public function testSetGetWorks($var, $value)
    {
        $this->tillStore->set($var, $value);
        $this->assertEquals($value, $this->tillStore->get($var));
    }

    public function testGc()
    {
        $this->tillStore->set("foo123", "ohai", 1);
        sleep(2);
        $this->tillStore->gc();
        $value = $this->tillStore->get("foo123", "no dice");
        $this->assertEquals($value, "no dice");
    }

    public function testExpireTime()
    {
        $test = mktime()+5;

        $this->tillStore->set("foobar", "bar", 5);
        $ttl = $this->tillStore->getTtl("foobar");

        $this->assertEquals($test, $ttl);
    }
}