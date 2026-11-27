<?php namespace Winter\Storm\Database\Schema;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Winter\Storm\Support\Facades\Schema;

/**
 * Proxy class
 */
class Blueprint extends BaseBlueprint
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection|null
     */
    public $connection = null;

    /**
     * Create a new schema blueprint.
     */
    public function __construct(...$args)
    {
        if (isset($args[0]) && $args[0] instanceof \Illuminate\Database\Connection) {
            $this->connection = array_shift($args);
        }

        parent::__construct(...$args);
    }

    /**
     * Get the raw SQL statements for the blueprint.
     *
     * @return array
     */
    public function toSql(...$args)
    {
        $connection = $args[0] ?? $this->connection;
        $grammar = $args[1] ?? ($connection ? $connection->getSchemaGrammar() : null);

        if ($connection && $grammar) {
            return parent::toSql($connection, $grammar);
        }

        return parent::toSql(...$args);
    }
    /**
     * Indicate that the given columns should be dropped if it exists.
     *
     * @param  array|mixed  $columns
     * @return \Illuminate\Support\Fluent
     */
    public function dropColumnIfExists($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $columns = collect($columns)->filter(function ($column) {
            return Schema::hasColumn($this->getTable(), $column);
        })->values()->all();

        return !empty($columns) ? $this->dropColumn($columns) : $this;
    }

    /**
     * Add the commands that are implied by the blueprint's state.
     *
     * Swaps in Winter's BlueprintState so that a `->change()` preserves the existing column's
     * attributes (see {@see \Winter\Storm\Database\Schema\BlueprintState}). Laravel hard-codes its
     * own state class when an alter command is present, so we re-seed with ours once the base
     * implied commands - and therefore the base state - have been created.
     *
     * @return void
     */
    protected function addImpliedCommands(...$args)
    {
        parent::addImpliedCommands(...$args);

        if (!is_null($this->state)) {
            $connection = $args[0] ?? ($this->connection ?? null);
            $grammar = $args[1] ?? ($connection ? $connection->getSchemaGrammar() : null);

            if ($connection) {
                $this->state = new BlueprintState($this, $connection, $grammar);
            }
        }
    }
}
