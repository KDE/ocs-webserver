<?php

namespace ApplicationTest\Model;

use Application\Model\Table\BrowseListTypesTable;
use PHPUnit\Framework\TestCase;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class BrowseListTypesTableTest extends TestCase
{
    public function testFetchAllReturnsAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select()->willReturn($resultSet);
        $this->assertSame($resultSet, $this->BrowseListTypesTable->fetchAll());
    }

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGatewayInterface::class);
        $this->BrowseListTypesTable = new BrowseListTypesTable($this->tableGateway->reveal());
    }

}