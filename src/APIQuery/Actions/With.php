<?php

namespace SI\Laravel\APIQuery\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SI\Laravel\APIQuery\AbstractAction;
use SI\Laravel\APIQuery\Exceptions\IncompatibleType;

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
        $explodedParameters = explode(',', $this->getParameterValue());

        // It's not possible to run the 'with' action if the subject is a collection.
        if($this->subject instanceof Collection)
        {
            throw new IncompatibleType('The subject is not compatible with this action.');
        }

        // This action is run before all the others: running $userModel->with('role') is different
        // than running UserModel::with('role').
        if($this->subject instanceof Model)
        {
            $modelClass = (new \ReflectionClass($this->subject))->getName();

            $model = call_user_func([$modelClass, 'with'], $explodedParameters);

            if($this->subject->id)
            {
                return $model->find($this->subject->id);
            }

            return $model;
        }

        return $this->subject->with($explodedParameters);
    }
}