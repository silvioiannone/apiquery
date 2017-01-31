<?php

namespace SI\Laravel\APIQuery\Actions;

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
     * Columns that can be queried.
     *
     * Use this array to specify the columns/fields that is possible to search for each model.
     *
     * @var array
     */
    protected $searchableColumns = [
        'App\AccountingFirm' => ['name'],
        'App\Company' => ['name'],
        'App\Contact' => ['name'],
        'App\Document' => ['name'],
        'App\Event' => ['title'],
        'App\Link' => ['name'],
        'App\News' => ['title'],
        'App\Report' => ['title'],
        'App\Service' => ['title'],
        'App\Support' => ['title'],
        'App\User' => ['name']
    ];

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $this->validate();

        list($column, $value) = $this->getParameters();

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

    /**
     * Validate whether the query can be performed or not.
     */
    protected function validate()
    {
        $subjectType = (new \ReflectionClass($this->subject))->getName();

        if(!isset($this->searchableColumns[$subjectType]))
        {
            throw new InvalidQuery('The model ' . $subjectType . ' cannot be queried.');
        }

        $searchableColumns = $this->searchableColumns[$subjectType];

        if(!in_array($this->getColumn(), $searchableColumns))
        {
            throw new InvalidQuery('Searches in the '. $this->getColumn() . ' column of the ' .
                $subjectType . ' model are not allowed.');
        }
    }
}