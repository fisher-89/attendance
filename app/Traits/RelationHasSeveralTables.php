<?php
/**
 * Created by PhpStorm.
 * User: Fisher
 * Date: 2017/10/23 0023
 * Time: 18:05
 */

namespace App\Traits;

trait RelationHasSeveralTables
{
    /**
     * Create a new model instance for a related model.
     *
     * @param  string $class
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        $class = is_object($class) ? $class : new $class;
        return tap(new $class, function ($instance) {
            if (!$instance->getConnectionName()) {
                $instance->setConnection($this->connection);
            }
        });
    }
}