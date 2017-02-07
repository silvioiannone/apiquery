<?php

namespace SI\Laravel\APIQuery\Actions;

use Illuminate\Database\Eloquent\Model;
use SI\Laravel\APIQuery\AbstractAction;

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

        // If the subject is a Model we can't just use $this->subject->with(parameter) otherwise it
        // will return all the users with the specified relations. Instead something like this must
        // be done: ModelType::with(parameter)->find($this->subject->id). This way only one user
        // will be fetched.
        if($this->subject instanceof Model)
        {
            $modelClass = (new \ReflectionClass($this->subject))->getName();

            return call_user_func([$modelClass, 'with'], $explodedParameters)
                ->find($this->subject->id);
        }

        return $this->subject->with($explodedParameters);
    }
}