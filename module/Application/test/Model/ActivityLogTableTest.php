<?php

namespace ApplicationTest\Model;

use Application\Model\Table\ActivityLogTable;
use PHPUnit\Framework\TestCase;
use Laminas\Db\TableGateway\TableGatewayInterface;

class ActivityLogTableTest extends TestCase
{
    public function testCanRetrieveAnActivityLogByItsId()
    {
        /*
        $log = new ActivityLog();
        $log->exchangeArray(array('activity_log_id' => 598784,
            'member_id' => 24,
            'project_id' => 1314240,
            'object_id' => 1314240,
            'object_ref' => 'project',
            'object_title' => 'Greyscale / Sephia images',
            'object_text' => 'Adds a new entry to the context menu which allows you to convert image files to greyscale or sephia very easily.

Note:
imagemagick/mogrify ne ... ',
            'object_img' => '5/9/e/c/fd5a4ced2f64e4a77380d996e6a06ae6cf7f.png',
            'activity_type_id' => 50,
            'time' => '2020-02-05 08:08:12'));

        $resultSet = new ResultSet();
        $resultSet->setArrayObjectPrototype(new ActivityLog());
        $resultSet->initialize(array($log));

        $this->assertSame($log, $this->ActivityLogTable->findById(598784));
         *
         */
    }

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGatewayInterface::class);
        $this->ActivityLogTable = new ActivityLogTable($this->tableGateway->reveal());
    }

    /*
    public function testFetchAllReturnsAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select()->willReturn($resultSet);
        $this->assertSame($resultSet, $this->ActivityLogTable->fetchAll());
    }
     * 
     */
}