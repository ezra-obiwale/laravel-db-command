<?php

namespace DSCribe;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class Database extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:table
                                { table : The name of the table to act on }
                                { --where= : A comma-separated list of <column>:<sign>:<value> e.g. id:=:1,name:like:%Ezra% }
                                { --where-null= : A comma-separated list of columns that must be null }
                                { --where-not-null= : A comma-separated list of columns that must not be null }
                                { --data= : A comma-separated list of <column>:<value> }
                                { --fields= : A comma-separated list of fields to retrieve }
                                { --c|create : Indicates to perform a create operation }
                                { --r|read : Indicates to perform a read operation. This is default }
                                { --u|update : Indicates to perform an update operation }
                                { --d|delete : Indicates to perform a delete operation }
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a CRUD operation on a table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tableName = $this->argument('table');
        $where = $this->option('where');
        $whereNull = $this->option('where-null');
        $whereNotNull = $this->option('where-not-null');

        $table = DB::table($tableName);

        $where = $this->prepWhere($where);

        if (count($where)) {
            $table->where($where);
        }

        if ($whereNull) {
            foreach (explode(',', $whereNull) as $col) {
                $table->whereNull($col);
            }
        }

        if ($whereNotNull) {
            foreach (explode(',', $whereNotNull) as $col) {
                $table->whereNotNull($col);
            }
        }

        if ($this->option('create')) {
            return $this->create($table);
        }

        if ($this->option('update')) {
            return $this->update($table);
        }

        if ($this->option('delete')) {
            return $this->delete($table);
        }

        return $this->read($table);
    }

    private function create(Builder $table)
    {
        try {
            $data = $this->prepData();

            $result = $table->insert($data);

            return $this->parseResult($result);
        } catch (Throwable $th) {
            $this->error($th->getMessage());

            return 255;
        }
    }

    private function read(Builder $table)
    {
        $fields = $this->option('fields');

        $result = $table->get($fields ? explode(',', trim($fields)) : null);

        return $this->parseResult($result);
    }

    private function update(Builder $table)
    {
        try {
            $data = $this->prepData();

            $result = $table->update($data);

            return $this->parseResult($result);
        } catch (Throwable $th) {
            $this->error($th->getMessage());

            return 255;
        }
    }

    private function delete(Builder $table)
    {
        if (!$this->confirm('Are you sure you want to delete all rows in the table?')) {
            $this->info('Canceled');

            return 0;
        }

        $result = $table->delete();

        return $this->parseResult($result);
    }

    private function prepWhere($where)
    {
        $where = $where ? explode(',', trim($where)) : [];

        foreach ($where as &$d) {
            $d = explode(':', $d);

            if (count($d) === 1) {
                $d[] = 'is null';
            } else if (count($d) === 2) {
                if ($d[1] === 'null') {
                    $d[1] = 'is null';
                }
            }
        }

        return $where;
    }

    private function prepData()
    {
        $data = $this->option('data');

        if (empty($data)) {
            throw new Exception('Empty data found. Please use option --data');
        }

        $newData = [];
        $data = explode(',', trim($data));

        foreach ($data as $d) {
            $parts = explode(':', trim($d));

            $key = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : null;

            if ($value === 'null') {
                $value = null;
            }

            $newData[$key] = $value;
        }

        return $newData;
    }

    private function parseResult($result)
    {
        if (is_object($result)) {
            $result = $result->map(function ($item) {
                return is_object($item) ? (array) $item : $item;
            })->toArray();
        }

        if (is_array($result)) {
            if (!count($result)) {
                $this->info('Result: empty');

                return 0;
            }

            $this->info('Result:');

            $item = $this->option('read') ? $result[0] : $result;
            $headers = array_keys((array) $item);

            $this->table($headers, $result);
        } else {
            $this->info('Result: ' . $result);
        }

        return 0;
    }
}
