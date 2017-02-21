<?php

namespace SI\Laravel;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Collection;
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
     * @var Model|Relation|EloquentBuilder|DatabaseBuilder|Collection $subject
     */
    protected $subject;

    /**
     * Execute the query performing all the needed actions.
     *
     * @param Model|Relation|EloquentBuilder|DatabaseBuilder|Collection $subject
     * @return mixed
     */
    public function execute($subject)
    {
        $this->setSubject($subject);

        foreach($this->getParameterActions() as $parameterAction)
        {
            $this->setSubject($parameterAction->run());
        }

        if($this->subject instanceof Model || $this->subject instanceof Collection)
        {
            return $this->subject;
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

        $actionsToPerform = $this->prepare();

        // Loop the parameter requests and try to instantiate the actions
        foreach($actionsToPerform as $parameterKey => $parameterValue)
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
     * Prepare the actions that need to be executed.
     *
     * @return array
     */
    protected function prepare(): array
    {
        $requestParameters = request()->all();

        // The 'with' action needs to be run before the others. This is better explained with an
        // example. Let's for example take Auth::user(): it returns the currently logged in user.
        // If a 'with' action is present and it's not the first to be executed then
        // Auth::user()->with('role') will be executed and that will return all the users and their
        // role relation. To avoid this the 'with' action needs to run first so that something like
        // User::with('role')->where('id', Auth::user()->id) can be executed returning the intended
        // result. See the 'with' action class for more informations.

        // If a 'with' action is present...
        if(request()->has('with'))
        {
            // ...put it at the first place.
            $requestParameters = ['with' => request()->get('with')] + $requestParameters;
        }

        return $requestParameters;
    }

    /**
     * Set the entity that will be processed when the query will be processed.
     * It must be an eloquent model, an eloquent relation or a builder.
     *
     * @param Model|Relation|EloquentBuilder|DatabaseBuilder|Collection $subject
     * @throws InvalidSubjectType
     * @return APIQuery
     */
    protected function setSubject($subject): APIQuery
    {
        if(!($subject instanceof Model ||
            $subject instanceof EloquentBuilder ||
            $subject instanceof DatabaseBuilder ||
            $subject instanceof Relation ||
            $subject instanceof Collection
        )) {
            throw new InvalidSubjectType(
                'Subject must be either a model, relation or builder. Given: ' .
                (new \ReflectionClass($subject))->getName()
            );
        }

        $this->subject = $subject;

        return $this;
    }
}