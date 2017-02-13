<?php

namespace SI\Laravel\APIQuery;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Abstract API query action.
 *
 * An action is simply the operation (or set of operations) that needs to be run
 * on the subject.
 *
 * @package SI\Laravel\APIQuery
 */
abstract class AbstractAction
{
    /**
     * @var mixed
     */
    protected $subject;

    /**
     * Set the subject.
     *
     * @param $subject
     * @return AbstractAction
     */
    public function setSubject($subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value bound to the current action.
     *
     * @return string
     */
    protected function getParameterValue(): string
    {
        $parameterName = lcfirst((new \ReflectionClass($this))->getShortName());

        return request()->get($parameterName);
    }

    /**
     * Execute the action.
     *
     * @return Model|Relation|Builder
     * @throws \Exception
     */
    public function run()
    {
        $result = $this->handle();

        if(!($result instanceof Model ||
            $result instanceof Builder ||
            $result instanceof Relation ||
            $result instanceof Collection)
        ) {
            throw new \Exception(
                'The action ' . (new \ReflectionClass($this))->getShortName() .
                ' is returning invalid results. Got: ' . (new \ReflectionClass($result))->getName()
            );
        }

        return $result;
    }

    /**
     * Use this function to specify how the action should handle the subject.
     */
    protected abstract function handle();
}