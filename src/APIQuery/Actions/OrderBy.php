<?php

namespace SI\Laravel\APIQuery\Actions;

use SI\Laravel\APIQuery\AbstractAction;

/**
 * This action orders the query result.
 *
 * The format is the following: `orderBy=column,direction`.
 * E.g.:
 *
 *     'orderBy=updated_date,desc'.
 *
 * @package SI\Laravel\APIQuery
 */
class OrderBy extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function handle()
    {
        $parameters = explode(',', $this->getParameterValue());

        if(count($parameters) === 1)
        {
            $column = $parameters[0];
        }
        else
        {
            list($column, $direction) = $parameters;
        }

        if(!isset($direction)) $direction = 'desc';

        return $this->subject->orderBy($column, $direction);
    }
}