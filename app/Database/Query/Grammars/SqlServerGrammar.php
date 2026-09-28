<?php

namespace App\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SqlServerGrammar as BaseSqlServerGrammar;

class SqlServerGrammar extends BaseSqlServerGrammar
{
    /**
     * Compile a select query into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if ($query->offset <= 0) {
            return parent::compileSelect($query);
        }

        // If an offset is present, use ROW_NUMBER() for SQL Server 2008 compatibility
        return $this->compileAnsiOffset($query, $this->compileComponents($query));
    }

    /**
     * Create a full ANSI-92 SQL statement that will be decorated with ROW_NUMBER().
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $components
     * @return string
     */
    protected function compileAnsiOffset(Builder $query, $components)
    {
        // An order by clause is required for ROW_NUMBER()
        $orders = $this->compileOrders($query, $query->orders);
        if (empty($orders)) {
            $orders = 'order by (select 0)';
        }

        unset($components['limit'], $components['offset'], $components['orders']);

        // Append ROW_NUMBER() to the select columns
        $components['columns'] = $this->compileColumns($query, $query->columns).", row_number() over ({$orders}) as row_num";

        $sql = $this->concatenate($components);

        $start = $query->offset + 1;
        $end = $query->limit ? $query->offset + $query->limit : null;

        $between = $end ? "between {$start} and {$end}" : ">= {$start}";

        return "select * from ({$sql}) as temp_table where row_num {$between} order by row_num";
    }

    /**
     * Compile the limit into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        if ($query->offset > 0) {
            return '';
        }

        return parent::compileLimit($query, $limit);
    }

    /**
     * Compile the offset into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $offset
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return '';
    }
}
