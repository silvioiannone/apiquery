<?php

namespace SI\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use SI\Laravel\APIQuery\AbstractAction;
use SI\Laravel\APIQuery\Exceptions\InvalidSubjectType;

/**
 * The purpose of this class it to read the parameters in the current request
 * and elaborate queries on models/relations/collections based on those.
 *
 * A simple example: if the current request contains:
 * *\/users?search=name&order_by=email* this library will perform a search on
 * all the users and order them by the email.
 *
 * @package SI\Laravel\APIQuery
 */
class APIQuery
{
    /**
     * @var Model|Relation|Builder $subject
     */
    protected $subject;

    /**
     * Execute the query performing all the needed actions.
     *
     * @param Model|Relation|Builder $subject
     * @return mixed
     */
    public function execute($subject)
    {
        $this->setSubject($subject);

        foreach($this->getParameterActions() as $parameterAction)
        {
            $this->setSubject($parameterAction->run());
        }

        return $this->subject->get();
    }

    /**
     * Get a the action instances that will be performed.
     *
     * @return AbstractAction[]
     */
    protected function getParameterActions(): array
    {
        $parameterActions = [];

        // Loop the parameter requests and try to instantiate the actions
        foreach(request()->all() as $parameterKey => $parameterValue)
        {
            $className = __NAMESPACE__ . '\APIQuery\Actions\\' . studly_case($parameterKey);

            if(!class_exists($className)) continue;

            /** @var AbstractAction $actionInstance */
            $actionInstance = new $className();
            $actionInstance->setSubject($this->subject);
            $parameterActions[] = $actionInstance;
        };

        return $parameterActions;
    }

    /**
     * Set the entity that will be processed when the query will be processed.
     * It must be an eloquent model, an eloquent relation or a builder.
     *
     * @param Model|Relation|Builder $subject
     * @throws InvalidSubjectType
     * @return APIQuery
     */
    protected function setSubject($subject): APIQuery
    {
        if(!($subject instanceof Model ||
            $subject instanceof Builder ||
            $subject instanceof Relation)
        ) {
            throw new InvalidSubjectType(
                'Subject must be either a model, relation or builder. Given: ' .
                (new \ReflectionClass($subject))->getName()
            );
        }

        $this->subject = $subject;

        return $this;
    }
}