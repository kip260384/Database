<?php
namespace PHPixieTests\Database\SQL\Query;

/**
 * @coversDefaultClass \PHPixie\Database\Query\SQL\Implementation
 */
abstract class ImplementationTest extends \PHPixieTests\Database\Query\ImplementationTest
{
    /**
     * @covers ::table
     * @covers ::clearTable
     * @covers ::getTable
     */
    public function testGetSetTable()
    {
        $this->setClearGetTest('table', array(
            array(array('pixie', 'test')),
            array(array('pixie'), array('pixie', null)),
        ));
    }
    
    public function execute()
    {
        $expr = $this->parse();
        $result = $this->connection->execute($expr->sql, $expr->params);
        return $result;
    }
    
    /**
     * @covers ::parse
     */
    public function testParse()
    {
        $this->parserTest();
    }
    
    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $query = $this->query();
        $this->parser
                ->expects($this->any())
                ->method('parse')
                ->with ($query)
                ->will($this->returnValue(new \PHPixie\Database\SQL\Expression('pixie', array(5))));

        $this->connection
                ->expects($this->any())
                ->method('execute')
                ->with ('pixie', array(5))
                ->will($this->returnValue('a'));
        $this->assertEquals('a', $query->execute());
    }
}
