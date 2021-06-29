<?php

namespace ApplicationTest\Model;

use Application\Model\Table\ConfigStoreTable;
use PHPUnit\Framework\TestCase;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class ConfigStoreTableTest extends TestCase
{
    public function testFetchAllReturnsAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select()->willReturn($resultSet);
        $this->assertSame($resultSet, $this->ConfigStoreTable->fetchAll());
    }

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGatewayInterface::class);
        $this->ConfigStoreTable = new ConfigStoreTable($this->tableGateway->reveal());
    }
}