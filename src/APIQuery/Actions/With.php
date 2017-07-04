<?php

namespace SI\Laravel\APIQuery\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SI\Laravel\APIQuery\AbstractAction;
use SI\Laravel\APIQuery\Exceptions\IncompatibleType;
use SI\Laravel\APIQuery\Exceptions\InvalidSubjectType;

/**
 * This action performs a with operation on the subject allowing to retrieve related models.
 *
 * The request parameter format will be the following
 *
 *     with=relation1,relation2,...,relationN
 *
 * @package SI\Laravel\APIQuery\Actions
 */
class With extends AbstractAction
{
    /**
     * Use this function to specify how the action should handle the subject.
     */
    protected function handle()
    {
        // This action is run before all the others: running $userModel->with('role') is different
        // than running UserModel::with('role').
        if($this->subject instanceof Model || $this->subject instanceof Collection)
        {
            $model = $this->executeWith();

            // If the subject has an id set...
            if($this->subject instanceof Model && $this->subject->getAttribute('id'))
            {
                // ...then we return the model with the relation
                return $model->find($this->subject->id);
            }

            return $model;
        }

        return $this->subject->with($this->getParameters());
    }

    /**
     * Execute the with method on the subject.
     *
     * @return mixed
     */
    protected function executeWith()
    {
        $modelClass = $this->getSubjectSourceClassName();

        // Statically call the `with` method on the subject:
        //     User::with()
        return call_user_func([$modelClass, 'with'], $this->getParameters());
    }

    /**
     * Get the full class path of the subject.
     *
     * @return string
     * @throws InvalidSubjectType
     */
    protected function getSubjectSourceClassName(): string
    {
        if ($this->subject instanceof Collection)
        {
            return (new \ReflectionClass($this->subject[0]))->getName();
        }

        if ($this->subject instanceof Model)
        {
            return (new \ReflectionClass($this->subject))->getName();
        }

        throw new InvalidSubjectType();
    }

    /**
     * Get the parameters for this action.
     *
     * @return array
     */
    protected function getParameters(): array
    {
        return explode(',', $this->getParameterValue());
    }
}