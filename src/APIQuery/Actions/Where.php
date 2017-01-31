<?php

namespace SI\Laravel\APIQuery\Actions;

use SI\Laravel\APIQuery\AbstractAction;

/**
 * This action performs a where query. It uses a comma separated list of
 * parameters corresponding to the `where()` laravel query function. E.g.:
 *
 *     where=name,%3D,foo
 *
 * @package SI\Laravel\APIQuery
 */
class Where extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function handle()
    {
        $explodedParameters = explode(',', $this->getParameterValue());

        return $this->subject->where(...$explodedParameters);
    }
}