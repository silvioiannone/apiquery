<?php

namespace SI\Laravel\APIQuery\Actions;

use Illuminate\Database\Eloquent\Collection;
use SI\Laravel\APIQuery\AbstractAction;
use SI\Laravel\APIQuery\Exceptions\InvalidQuery;

/**
 * This action executes a simple search on the given subject.
 *
 * E.g.:
 *
 *     search=column,value
 *
 * @package SI\Laravel\APIQuery\Actions
 */
class Search extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function handle()
    {
        list($column, $value) = $this->getParameters();

        // The 'search' action can't work directly on collections because that would be like doing
        // for example: User::all()->where(...) and that doesn't work. Instead, if it's a
        // collection...
        if($this->subject instanceof Collection)
        {
            $subjectModelType = (new \ReflectionClass($this->subject->first()))->getName();

            return (new $subjectModelType)->where($column, 'LIKE', '%' . $value . '%');
        }

        return $this->subject->where($column, 'LIKE', '%' . $value . '%');
    }

    /**
     * Get the column.
     *
     * @return string|null
     */
    protected function getColumn(): string
    {
        return $this->getParameters()[0] ?? '';
    }

    /**
     * Get the request parameters
     *
     * @return array
     */
    protected function getParameters(): array
    {
        return explode(',', trim($this->getParameterValue()));
    }

    /**
     * Get the search value.
     *
     * @return string
     */
    protected function getValue(): string
    {
        return $this->getParameters()[1] ?? '';
    }
}