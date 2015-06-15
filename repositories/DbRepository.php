<?php
/**
 * Created by PhpStorm.
 * User: Ihor Borysyuk
 * Date: 05.06.15
 * Time: 15:53
 */

namespace yii2ddd\repositories;

use yii2ddd\utils\Convert;
use yii\db\Query;

/**
 * There is base class of repository for work with database.
 *
 * Class DbRepository
 * @package yii2ddd\repositories
 */
class DbRepository extends AbstractRepository
{
    /**
     * @var yii\db\Connection Yii database connection
     */
    protected $db = null;

    /**
     * @var yii\db\Transaction Current transaction
     */
    protected $transaction = null;

    /**
     * Set current DB connection.
     * @param yii\db\Connection $db - DB connection
     */
    public function setDB($db)
    {
        $this->db = $db;
    }

    /**
     * Get current DB connection.
     * @return  yii\db\Connection
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * Start a transaction
     */
    public function startTransaction()
    {
        $this->transaction = $this->db->beginTransaction();
    }

    /**
     * Commit a transaction.
     */
    public function commit()
    {
        $this->transaction->commit();
        $this->transaction = null;
    }

    /**
     * Rolls back a transaction.
     */
    public function rollBack()
    {
        $this->transaction->commit();
        $this->transaction = null;
    }

    /**
     * Insert items into table
     * @param string $table Table name
     * @param array $items
     * You can insert one record or multiply records at one time.
     * $items = [
     *      'field1' => 'value1',
     *      'field2' => 'value2',
     *      'field3' => 'value3',
     * ];
     * OR
     * $items = [
     *      [
     *          'field1' => 'value1',
     *          'field2' => 'value2',
     *          'field3' => 'value3',
     *      ],
     *      [
     *          'field1' => 'value4',
     *          'field2' => 'value5',
     *          'field3' => 'value6',
     *      ],
     *      [
     *          'field1' => 'value7',
     *          'field2' => 'value8',
     *          'field3' => 'value9',
     *      ],
     * ]
     *
     */
    public function insert($table, $items)
    {
        if (!is_array(reset($items))) {
            $items = [$items];
        }
        $keys = array_keys(reset($items));
        $keys = array_combine($keys, $keys);
        $this->db()->createCommand()->batchInsert($table, $keys, $items)->execute();
    }

    /**
     * Update record into table
     * @param string $table - Table name
     * @param array $record - Record which you want to update
     * @param string $key - Name of field for condition
     */
    public function update($table, $record, $key = 'id')
    {
        $expr = "$key = $record[$key]";
        unset($record[$key]);

        $this->db()->createCommand()->update($table, $record, $expr)->execute();
    }

    /**
     * Query sql command
     * @param string $sql
     * @param null|array|string $collectKey - Keys for grouping results
     * @param null|string|array $collectValue - field which will be returned as value
     * @return array
     * @throws \yii\web\UserException
     */
    public function query($sql, $collectKey = null, $collectValue = null)
    {
        $items = $this->db()->createCommand($sql)->queryAll();
        $items = Convert::collectItems($items, $collectKey, $collectValue);

        return $items;
    }

    /**
     * Find items which fit some condition
     * @param string $table - Table name
     * @param array $condition - Condition
     * @param null|array|string $collectKey - Keys for grouping results
     * @param null|string|array $collectValue - field which will be returned as value
     * @return array
     */
    public function findItems($table, $condition = [], $collectKey = null, $collectValue = null)
    {
        $query = new Query();
        $query->select('*')->from($table);
        $query->where($condition);
        $command = $query->createCommand();

        return $this->query($command->getRawSql(), $collectKey, $collectValue);
    }

    /**
     * Get records from table by ids.
     * If $ids == null - get all records.
     * @param string $table - Table name
     * @param null|array $ids
     * @param null|array|string $collectKey - Keys for grouping results
     * @param null|string|array $collectValue - field which will be returned as value
     * @return array
     */
    public function getItemsById($table, $ids = null, $collectKey = null, $collectValue = null)
    {
        $condition = [];
        if (!empty($ids)) {
            $condition = ['id' => $ids];
        }
        return $this->findItems($table, $condition, $collectKey, $collectValue);
    }
}
