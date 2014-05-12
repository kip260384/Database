<?php
namespace PHPixieTests\Database\Driver\Mongo\Query\Type;

/**
 * @coversDefaultClass \PHPixie\Database\Driver\Mongo\Query\Type\Update
 */
class UpdateTest extends \PHPixieTests\Database\Driver\Mongo\Query\ItemsTest
{
    protected $queryClass = '\PHPixie\Database\Driver\Mongo\Query\Type\Update';
    protected $type = 'update';
    
    /**
     * @covers ::set
     * @covers ::clearSet
     * @covers ::getSet
     */
    public function testSet()
    {
        $this->setClearGetTest('set', array(
            array(array('test'), array(array('test'))),
        ), 'array');
    }
    
    /**
     * @covers ::increment
     * @covers ::clearIncrement
     * @covers ::getIncrement
     */
    public function testIncrement()
    {
        $this->setClearGetTest('increment', array(
            array(array('test'), array(array('test'))),
        ), 'array');
    }
    
    /**
     * @covers ::_unset
     * @covers ::remove
     * @covers ::clearUnset
     * @covers ::getUnset
     */
    public function testUnset()
    {
        $this->builderMethodTest('_unset', array('name'), $this->query, null, array(array('name')), 'addUnset');
        $this->builderMethodTest('remove', array('name'), $this->query, null, array(array('name')), 'addUnset');
        $this->clearGetTest('unset', 'array');
    }
}