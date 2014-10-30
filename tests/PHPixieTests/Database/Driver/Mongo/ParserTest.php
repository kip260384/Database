<?php
namespace PHPixieTests\Database\Driver\Mongo;

/**
 * @coversDefaultClass \PHPixie\Database\Driver\Mongo\Parser
 */
class ParserTest extends \PHPixieTests\Database\ParserTest
{
    protected $database;

    protected function setUp()
    {
        $this->database = new \PHPixie\Database(null);
        $driver = $this->database->driver('Mongo');
        $operatorParser = new \PHPixie\Database\Driver\Mongo\Parser\Operator();
        $groupParser = new \PHPixie\Database\Driver\Mongo\Parser\Group($driver, $operatorParser);
        $this->parser = new \PHPixie\Database\Driver\Mongo\Parser($this->database, $driver, 'default', $groupParser);
    }

    /**
     * @covers ::parse
     * @covers ::<protected>
     */
    public function testParseSelect()
    {
        $query = $this->getQuery()->collection('fairies')
                                    ->where('name', 1)
                                    ->where(function ($builder) {
                                        $builder->_and('id', 2)->_or('id', 3);
                                    });
        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'find',
                'args' =>array (
                    array (
                        '$or' => array (
                            array (
                                'name' => 1,
                                'id' => 2,
                            ),
                            array (
                                'name' => 1,
                                'id' => 3,
                            ),
                        ),
                    ),
                    array()
                )
            )
        ));

        $query = $this->getQuery()->collection('fairies')
                                    ->fields(array('id', 'name'))
                                    ->where('name', 1)
                                    ->limit(4)
                                    ->offset(10);

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'find',
                'args' =>array (
                    array (
                        'name' => 1
                    ),
                    array(
                        'id' => true,
                        'name' => true
                    )
                )
            ),
            array(
                'type' => 'method',
                'name' => 'limit',
                'args' => array(4)
            ),
            array(
                'type' => 'method',
                'name' => 'skip',
                'args' => array(10)
            )
        ));

        $query = $this->getQuery('selectSingle')->collection('fairies');

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'findOne',
                'args' => array (array(), array())
            )
        ));

        $query = $this->getQuery()->collection('fairies')
                                    ->orderAscendingBy('name')
                                    ->orderDescendingBy('id')
                                    ->limit(1);

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'find',
                'args' => array (array(), array())
            ),
            array(
                'type' => 'method',
                'name' => 'sort',
                'args' => array(array(
                    'name' => 1,
                    'id' => -1
                )
            )),
            array(
                'type' => 'method',
                'name' => 'limit',
                'args' => array(1)
            ),
        ));
    }

    /**
     * @covers ::parse
     * @covers ::<protected>
     */
    public function testParseInsert()
    {
        $query = $this->getQuery('insert')->collection('fairies')
                                    ->data(array('id'=>1, 'name'=>"Trixie"));

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'insert',
                'args' => array (array('id'=>1, 'name'=>"Trixie"))
            )
        ));

        $query = $this->getQuery('insert')->collection('fairies')
                                    ->batchData(array(
                                        array('id' => 1, 'name' => "Trixie"),
                                        array('id' => 2, 'name' => "Tinkerbell")
                                    ));

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'batchInsert',
                'args' => array(array(
                            array('id' => 1, 'name' => "Trixie"),
                            array('id' => 2, 'name' => "Tinkerbell")
                        ))
            )
        ));

    }

    /**
     * @covers ::parse
     * @covers ::<protected>
     */
    public function testParseUpdate()
    {
        $query = $this->getQuery('update')->collection('fairies')
                                    ->set(array('id'=>1, 'name'=>"Trixie"))
                                    ->increment('trees', 1)
                                    ->unset('test');

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'update',
                'args' => array (array(), array(
                    '$set' => array('id'=>1, 'name'=>"Trixie"),
                    '$unset' => array('test' => true),
                    '$inc' => array('trees' => 1)
                ), array('multiple' => true))
            )
        ));

        $query = $this->getQuery('update')->collection('fairies')
                                    ->where('name', 5)
                                    ->set(array('id'=>1, 'name'=>"Trixie"));

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'update',
                'args' => array (array('name' => 5), array('$set' => array('id'=>1, 'name'=>"Trixie")), array('multiple' => true))
            )
        ));
    }

    /**
     * @covers ::parse
     * @covers ::<protected>
     */
    public function testParseDelete()
    {
        $query = $this->getQuery('delete')->collection('fairies')
                                    ->where('id',7);

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'remove',
                'args' => array (array('id'=>7))
            )
        ));

        $query = $this->getQuery('delete')->collection('fairies');

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'remove',
                'args' => array (array())
            )
        ));

    }

    /**
     * @covers ::parse
     * @covers ::<protected>
     */
    public function testParseCount()
    {
        $query = $this->getQuery('count')->collection('fairies')
                                    ->where('id',7);

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'find',
                'args' => array (array('id'=>7))
            ),
            array (
                'type' => 'method',
                'name' => 'count',
                'args' => array()
            )
        ));

        $query = $this->getQuery('count')->collection('fairies');

        $this->assertQuery($query, array (
            array (
                'type' => 'property',
                'name' => 'fairies',
            ),
            array (
                'type' => 'method',
                'name' => 'count',
                'args' => array()
            )
        ));

    }

    /**
     * @covers ::parse
     * @covers ::<protected>
     */
    public function testException()
    {
        $pixieQuery = $this->quickMock('\PHPixie\Database\Driver\Mongo\Query', array('type'));
        $pixieQuery
            ->expects($this->any())
            ->method('type')
            ->will($this->returnValue('pixie'));
        $this->assertException($pixieQuery);
        $this->assertException($this->getQuery('insert'));
        $this->assertException($this->getQuery('select'));
        $this->assertException($this->getQuery('insert')->data(array('id'=>1)));
        $this->assertException($this->getQuery('insert')->collection('fairies'));
        $this->assertException($this->getQuery('update')->collection('fairies'));

    }

    protected function assertException($query)
    {
        $except = false;
        try {
            $this->parser->parse($query);
        } catch (\PHPixie\Database\Exception\Parser $e) {
            $except = true;
        }
        $this->assertEquals(true, $except);
    }

    protected function assertQuery($query, $expect)
    {
        $chain = $this->parser->parse($query)->getChain();
        $this->assertEquals($chain, $expect);
    }

    protected function getQuery($type = 'select')
    {
        $class = '\PHPixie\Database\Driver\Mongo\Query\Type\\'.ucfirst($type);
        $builder = new \PHPixie\Database\Driver\Mongo\Query\Builder($this->database->conditions());
        $query = new $class(null, null, $builder);

        return $query;
    }

}
