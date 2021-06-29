<?php
/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

namespace Application\Model\Repository;

use Application\Model\Interfaces\BaseInterface;
use ArrayObject;
use InvalidArgumentException;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Hydrator\Reflection as ReflectionHydrator;
use RuntimeException;
use stdClass;

/**
 * Class BaseRepository
 *
 * @package Application\Model\Repository
 */
class BaseRepository implements BaseInterface
{
    /** @var AdapterInterface $db */
    protected $db;
    /** @var string $_name */
    protected $_name;    // table name
    /** @var string $_key */
    protected $_key; // table key
    /** @var string $_prototype */
    protected $_prototype; // prototype model of table

    public function __construct(
        AdapterInterface $db
    ) {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->db;
    }

    /**
     * @param array $params
     *
     * @return object
     */
    public function findOneBy(array $params)
    {
        $sql = new Sql($this->db);
        $select = $sql->select($this->_name);
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $select->where($value);
                continue;
            }
            $identifier = $sql->getAdapter()->platform->quoteIdentifier($key);
            $select->where([$identifier . '= ?' => $value]);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $resultSet = new HydratingResultSet(new ReflectionHydrator(), new $this->_prototype());
        $resultSet->initialize($result);
        $object = $resultSet->current();

        if (!$object) {
            throw new InvalidArgumentException(sprintf('`' . $this->_name . '` empty resultSet for: "%s"', json_encode($params)));
        }

        return $object;
    }

    /**
     * @param int $id
     *
     * @return ArrayObject|object|null
     */
    public function findById($id)
    {
        if (!$this->_prototype) {
            return $this->fetchById($id);
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->_name);
        $select->where([$this->_key . '= ?' => $id]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . $this->_name . ' with identifier "%s"; unknown database error.', $id
                )
            );
        }
        $resultSet = new HydratingResultSet(new ReflectionHydrator(), new $this->_prototype());
        $resultSet->initialize($result);
        /** @var  $object */
        $object = $resultSet->current();

        if (!$object) {
            throw new InvalidArgumentException(
                sprintf(
                    $this->_name . ' identifier "%s" not found.', $id
                )
            );
        }

        return $object;
    }

    /**
     * @param $id
     *
     * @return ArrayObject|null
     */
    public function fetchById($id)
    {
        if (null == $id || 0 == (int)$id) {
            return null;
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->_name);
        $select->where([$this->_key . '= ?' => $id]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . $this->_name . ' with identifier "%s"; unknown database error.', $id
                )
            );
        }
        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $object = $resultSet->current();
        if (!$object) {
            throw new InvalidArgumentException(
                sprintf(
                    $this->_name . ' identifier "%s" not found.', $id
                )
            );
        }

        return $object;
    }

    /**
     * @fetchRow from table with sql.  could be any table with join or so on.
     *
     * @param string $sql
     * @param null   $params
     * @param bool   $returnArray
     *
     * @return array|ArrayObject|null
     */
    public function fetchRow($sql, $params = null, $returnArray = true)
    {
        if ($returnArray) {
            $result = $this->fetchAll($sql, $params, true);

            return array_pop($result);
        }

        return $this->fetchAll($sql, $params, false)->current();
    }

    /**
     *  fetchAll from table with sql.  could be any table with join or so on.
     *
     * @param null $sql
     * @param null $params
     * @param bool $returnArray if true return array otherwise return resultset object
     *
     * @return array|ResultSet
     */
    public function fetchAll($sql = null, $params = null, $returnArray = true)
    {
        if (null == $sql) {
            $sql = "select * from " . $this->getName();
        }
        $statement = $this->db->driver->createStatement($sql);
        $statement->prepare();

        if ($params && !is_array($params)) {
            $params = array($params);
        }

        $result = $statement->execute($params);


        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
        } else {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . $this->_name . ' with identifier "%s"; unknown database error.', $sql
                )
            );
        }

        if ($returnArray) {
            return $resultSet->toArray();
        } else {
            return $resultSet;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @insertOrUpdate $data if key existing update otherwise insert
     * @param $data
     *
     * @return int returns amount of affected rows
     */
    public function insertOrUpdate($data)
    {
        $id = isset($data[$this->_key]) ? (int)$data[$this->_key] : 0;
        $tableGateway = new TableGateway($this->_name, $this->db);
        if ($id === 0) {
            $tableGateway->insert($data);

            return $tableGateway->getLastInsertValue();
        }

        try {
            $this->fetchById($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                sprintf(
                    'Cannot update ' . $this->_key . ' with identifier %d; does not exist', $id
                )
            );
        }
        unset($data[$this->_key]);

        return $tableGateway->update($data, [$this->_key => $id]);
    }

    /**
     * insert $data to Entity
     *
     * @param $data
     *
     * @return int
     */
    public function insert($data)
    {
        try {
            $tableGateway = new TableGateway($this->_name, $this->db);
            $tableGateway->insert($data);

            return $tableGateway->getLastInsertValue();
        } catch (RuntimeException $e) {

            throw new RuntimeException(
                'Database error occurred during ' . $this->_name . ' insert data operation>>>>>>' . $e->__toString()
            );
        }
    }

    /**
     * insert $data to table
     *
     * @param $table
     * @param $data
     *
     * @return int
     */
    public function insertTable($table, $data)
    {
        try {
            $tableGateway = new TableGateway($table, $this->db);
            $tableGateway->insert($data);

            return $tableGateway->getLastInsertValue();
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Database error occurred during ' . $this->_name . ' insert data operation'
            );
        }
    }

    /**
     * update $data to table
     *
     * @param $table
     * @param $data
     * @param $where
     *
     * @return int
     */
    public function updateTable($table, $data, $where)
    {
        try {
            $tableGateway = new TableGateway($table, $this->db);

            return $tableGateway->update($data, $where);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Database error occurred during ' . $this->_name . ' insert data operation'
            );
        }
    }

    /**
     * @param      $id
     * @param bool $setDeletedAt
     *
     * @return ResultInterface
     */
    public function setIsDeleted($id, $setDeletedAt = true)
    {
        if ($setDeletedAt) {
            $data = ['is_deleted' => 1, $this->_key => $id, 'deleted_at' => new Expression('now()')];
        } else {
            $data = ['is_deleted' => 1, $this->_key => $id];
        }

        return $this->update($data);
    }

    /**
     * @param      $data
     * @param null $where
     *
     * @return ResultInterface
     *$stmt->getAffectedRows()
     *$stmt->getAffectedRows()
     *update entity table with data.
     *if where is null use key from data. otherwise use where
     */
    public function update($data, $where = null)
    {
        if (null == $where) {
            $key = $data[$this->_key];
            if (!$key) {
                throw new RuntimeException('Cannot update ' . $this->_name . '; missing identifier');
            }
        }

        $update = new Update($this->_name);

        // remove key if existing
        if (is_array($this->_key)) {
            foreach ($this->_key as $keyName) {
                unset($data[$keyName]);
            }
        } else {
            unset($data[$this->_key]);
        }
        $update->set($data);

        if (null == $where) {
            $update->where([$this->_key . ' = ?' => $key]);
        } else {
            $update->where($where);
        }

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);

        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during ' . $this->_name . ' update operation'
            );
        }

        return $result;
    }

    /**
     * @param $id
     *
     * @return bool
     * @dangerous!!!
     */
    public function deleteReal($id)
    {
        $delete = new Delete($this->_name);
        $delete->where([$this->_key . ' = ?' => $id]);
        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            return false;
        }

        return true;
        /* or user tablegateway
        $tableGateway = new TableGateway($this->_name, $this->db);
        $tableGateway->delete([$this->_key => (int) $id]);
        */
    }

    public function select()
    {
        //$sql = new Sql($this->db);
        //return $sql->select();
        return new Select();
    }

    public function fetchAllSelect(Select $select)
    {
        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . $this->_name . '; unknown database error.'
                )
            );
        }
        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function fetchAllRows($where = null, $order = null, $limit = null, $offset = null)
    {
        $sql = new Sql($this->db);
        $select = $sql->select($this->_name);
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }


        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . $this->_name . '; unknown database error.'
                )
            );
        }
        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $where
     *
     * @return int fetchAllRows count
     */
    public function fetchAllRowsCount($where = null)
    {
        $sql = new Sql($this->db);
        $select = $sql->select($this->_name)->columns(array('count' => new Expression('COUNT(*)')));
        if ($where) {
            $select->where($where);
        }
        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving ' . __FUNCTION__ . $this->_name . '; unknown database error.'
                )
            );
        }
        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->toArray();

        return (int)array_pop($result)['count'];
    }

    /**
     * for run update/delete/insert table
     *
     * @param      $sql
     * @param null $params
     *
     * @return StatementInterface|ResultSet
     */
    public function query($sql, $params = null)
    {
        try {
            if ($params == null) {
                $result = $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
            } else {
                $result = $this->db->query($sql, $params);
            }

            return $result;
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                sprintf(
                    'Cannot run query' . $sql, $sql
                )
            );
        }
    }

    /**
     * array['id'] to object->id
     *
     * @param $array
     *
     * @return stdClass|null
     */
    public function arrayToObject($array)
    {
        return json_decode(json_encode($array), false);
    }

    /**
     * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
     *
     * @param string|Select $sql  An SQL SELECT statement.
     * @param mixed         $bind Data to bind into SELECT placeholders.
     *
     * @return array
     */
    public function fetchPairs($sql, $bind = array())
    {
        $result = $this->fetchAll($sql, $bind, true);
        $data = array();
        $i = 0;
        foreach ($result as $row) {
            $i = 0;
            $rowKey = null;
            $rowVal = null;
            foreach ($row as $key => $value) {
                if ($i == 0) {
                    $rowKey = $value;
                }
                if ($i == 1) {
                    $rowVal = $value;
                }
                $i = $i + 1;
            }
            $data[$rowKey] = $rowVal;
        }

        return $data;
    }

    /**
     * $this->db->getDriver()->getConnection()->beginTransaction();
     * $this->db->getDriver()->getConnection()->commit();
     *  $this->db->getDriver()->getConnection()->rollBack();
     */

    /**
     * shorthand for formatParameterName
     *
     * @param string $name
     *
     * @return string
     */
    public function fpn($name)
    {
        $adapter = $this->db;

        return $adapter->driver->formatParameterName($name);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function readCache($name)
    {
        $cache = new SimpleCacheDecorator($GLOBALS['ocs_cache']);
        $cache_name = hash('haval128,4', $name);

        try {
            return $cache->get($cache_name);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ' . $e->getMessage());

            return null;
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return bool
     */
    public function writeCache($name, $value, $ttl = 1800)
    {
        $cache = new SimpleCacheDecorator($GLOBALS['ocs_cache']);
        $cache_name = hash('haval128,4', $name);

        try {
            return $cache->set($cache_name, $value, $ttl);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            $GLOBALS['ocs_log']->err(__METHOD__ . ' - ' . $e->getMessage());

            return false;
        }
    }

}
