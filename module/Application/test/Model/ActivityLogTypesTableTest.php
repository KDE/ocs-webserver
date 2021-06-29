<?php

namespace ApplicationTest\Model;

use Application\Model\Table\ActivityLogTypesTable;
use PHPUnit\Framework\TestCase;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class ActivityLogTypesTableTest extends TestCase
{
    public function testFetchAllReturnsAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select()->willReturn($resultSet);
        $this->assertSame($resultSet, $this->ActivityLogTypesTable->fetchAll());
    }

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGatewayInterface::class);
        $this->ActivityLogTypesTable = new ActivityLogTypesTable($this->tableGateway->reveal());
    }
}