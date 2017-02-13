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

        // This action is run before all the others: running $userModel->with('role') is different
        // than running UserModel::with('role').
        if($this->subject instanceof Model)
        {
            $modelClass = (new \ReflectionClass($this->subject))->getName();

            return call_user_func([$modelClass, 'with'], $explodedParameters)
                ->find($this->subject->id);
        }

        return $this->subject->with($explodedParameters);
    }
}