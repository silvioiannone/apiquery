<?php

namespace SI\Laravel\APIQuery\Actions;

use SI\Laravel\APIQuery\AbstractAction;

/**
 * This action performs a where in query. It uses a comma separated list of parameters corresponding
 * to the `whereIn()` laravel query function. E.g.:
 *
 *     whereIn=column,value1,...,valueN
 *
 * @package SI\Laravel\APIQuery
 */
class WhereIn extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function handle()
    {
        $explodedParameters = explode(',', $this->getParameterValue());

        $column = array_shift($explodedParameters);

        return $this->subject->whereIn($column, $explodedParameters);
    }
}