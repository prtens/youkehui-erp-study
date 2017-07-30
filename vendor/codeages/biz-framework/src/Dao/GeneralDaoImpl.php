<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Context\Biz;

abstract class GeneralDaoImpl implements GeneralDaoInterface
{
    protected $biz;

    protected $table = null;

    protected $timestamps = array();

    protected $serializes = array();

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function create($fields)
    {
        $timestampField = $this->getTimestampField('created');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }

        $timestampField = $this->getTimestampField('updated');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }
        $affected = $this->db()->insert($this->table(), $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert error.');
        }

        return $this->get($this->db()->lastInsertId());
    }

    public function update($identifier, array $fields)
    {
        if (empty($identifier)) {
            return null;
        }

        if (is_numeric($identifier)) {
            return $this->updateById($identifier, $fields);
        } elseif (is_array($identifier)) {
            return $this->updateByConditions($identifier, $fields);
        }

        throw new DaoException('update arguments type error');
    }

    public function delete($id)
    {
        return $this->db()->delete($this->table(), array('id' => $id));
    }

    public function wave(array $ids, array $diffs)
    {
        $sets = array_map(function ($name) {
            return "{$name} = {$name} + ?";
        }, array_keys($diffs));

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $sql = "UPDATE {$this->table()} SET ".implode(', ', $sets)." WHERE id IN ($marks)";

        return $this->db()->executeUpdate($sql, array_merge(array_values($diffs), $ids));
    }

    public function get($id, $lock = false)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE id = ?".($lock ? ' FOR UPDATE' : '');
        return $this->db()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $declares = $this->declares();
        foreach ($orderBys ?: array() as $order => $sort) {
            $this->checkOrderBy($order, $sort, $declares['orderbys']);
            $builder->addOrderBy($order, $sort);
        }

        return $builder->execute()->fetchAll();
    }

    public function count($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(*)');

        return (int) ($builder->execute()->fetchColumn(0));
    }

    protected function updateById($id, $fields)
    {
        $timestampField = $this->getTimestampField('updated');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }

        $this->db()->update($this->table, $fields, array('id' => $id));

        return $this->get($id);
    }

    protected function updateByConditions(array $conditions, array $fields)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->update($this->table, $this->table);

        $timestampField = $this->getTimestampField('updated');
        if ($timestampField) {
            $fields[$timestampField] = time();
        }

        foreach ($fields as $key => $value) {
            $builder
                ->set($key, ':'.$key)
                ->setParameter($key, $value);
        }

        $builder->execute();

        $resultBuilder = $this->createQueryBuilder($conditions)->select('*')->from($this->table(), $this->table());

        return $resultBuilder->execute()->fetchAll();
    }

    /**
     * @param string $sql
     * @param array  $orderBys
     * @param int    $start
     * @param int    $limit
     *
     * @throws DaoException
     *
     * @return string
     */
    protected function sql($sql, array $orderBys = array(), $start = null, $limit = null)
    {
        if (!empty($orderBys)) {
            $sql .= ' ORDER BY ';
            $orderByStr = $separate = '';
            $declares = $this->declares();
            foreach ($orderBys as $order => $sort) {
                $this->checkOrderBy($order, $sort, $declares['orderbys']);
                $orderByStr .= sprintf('%s %s %s', $separate, $order, $sort);
                $separate = ',';
            }

            $sql .= $orderByStr;
        }

        if (null !== $start && !is_numeric($start)) {
            throw $this->createDaoException('SQL Limit must can be cast to integer');
        }

        if (null !== $limit && !is_numeric($limit)) {
            throw $this->createDaoException('SQL Limit must can be cast to integer');
        }

        $onlySetStart = $start !== null && $limit === null;
        $onlySetLimit = $limit !== null && $start === null;

        if ($onlySetStart || $onlySetLimit) {
            throw $this->createDaoException('start and limit need to be assigned');
        }

        if (is_numeric($start) && is_numeric($limit)) {
            $sql .= sprintf(' LIMIT %d, %d', $start, $limit);
        }

        return $sql;
    }

    public function table()
    {
        return $this->table;
    }

    /**
     * @return Connection
     */
    public function db()
    {
        return $this->biz['db'];
    }

    protected function getByFields($fields)
    {
        $placeholders = array_map(function ($name) {
            return "{$name} = ?";
        }, array_keys($fields));

        $sql = "SELECT * FROM {$this->table()} WHERE ".implode(' AND ', $placeholders). ' LIMIT 1 ';

        return $this->db()->fetchAssoc($sql, array_values($fields)) ?: null;
    }

    protected function findInField($field, $values)
    {
        if (empty($values)) {
            return array();
        }

        $marks = str_repeat('?,', count($values) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE {$field} IN ({$marks});";

        return $this->db()->fetchAll($sql, $values);
    }

    protected function findByFields($fields)
    {
        $placeholders = array_map(function ($name) {
            return "{$name} = ?";
        }, array_keys($fields));

        $sql = "SELECT * FROM {$this->table()} WHERE ".implode(' AND ', $placeholders);

        return $this->db()->fetchAll($sql, array_values($fields));
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || $value === null) {
                return false;
            }

            if (is_array($value) && empty($value)) {
                return false;
            }

            return true;
        });

        $builder = $this->getQueryBuilder($conditions);
        $builder->from($this->table(), $this->table());

        $declares = $this->declares();
        $declares['conditions'] = isset($declares['conditions']) ? $declares['conditions'] : array();

        foreach ($declares['conditions'] as $condition) {
            $builder->andWhere($condition);
        }

        return $builder;
    }

    protected function getQueryBuilder($conditions)
    {
        return new DynamicQueryBuilder($this->db(), $conditions);
    }

    private function getTimestampField($mode = null)
    {
        if (empty($this->timestamps)) {
            return;
        }

        if ($mode == 'created') {
            return isset($this->timestamps[0]) ? $this->timestamps[0] : null;
        } elseif ($mode == 'updated') {
            return isset($this->timestamps[1]) ? $this->timestamps[1] : null;
        } else {
            throw $this->createDaoException('mode error.');
        }
    }

    private function createDaoException($message = '', $code = 0)
    {
        return new DaoException($message, $code);
    }

    private function checkOrderBy($order, $sort, $allowOrderBys)
    {
        if (!in_array($order, $allowOrderBys, true)) {
            throw $this->createDaoException(sprintf("SQL order by field is only allowed '%s', but you give `{$order}`.", implode(',', $allowOrderBys)));
        }
        if (!in_array(strtoupper($sort), array('ASC', 'DESC'), true)) {
            throw $this->createDaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$sort}`.");
        }
    }
}