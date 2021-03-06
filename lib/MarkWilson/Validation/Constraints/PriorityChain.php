<?php

namespace MarkWilson\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Priority Chain constraint
 *
 * Adapted from https://gist.github.com/rybakit/4705749
 *
 * @package MarkWilson\Validation\Constraints
 * @author  Mark Wilson <mark@89allport.co.uk>
 */
class PriorityChain extends Constraint
{
    /**
     * Constraints queue
     *
     * @var \SplPriorityQueue
     */
    public $constraints;

    /**
     * Fail validation on first error
     *
     * @var boolean
     */
    public $stopOnError = true;

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        if (is_array($options) && !array_intersect(array_keys($options), array('constraints', 'stopOnError'))) {
            $options = array('constraints' => $options);
        }

        parent::__construct($options);

        // convert array of constraints into SplPriorityQueue
        if (is_array($this->constraints)) {
            $queue       = new \SplPriorityQueue();
            $constraints = $this->constraints;
            $constraints = array_reverse($constraints);

            foreach ($constraints as $index => $constraint) {
                $queue->insert($constraint, $index);
            }

            $this->constraints = $queue;
        }

        if (!$this->constraints instanceof \SplPriorityQueue) {
            throw new ConstraintDefinitionException('The option "constraints" is expected to be a SplPriorityQueue in constraint ' . __CLASS__ . '.');
        }

        $constraintsCopy = $this->getConstraints();

        // set extraction mode to both priority and data
        $constraintsCopy->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);

        // loop through the priority chain for options validation
        while ($constraintsCopy->valid()) {
            $constraint = $constraintsCopy->current();

            // fail if the constraint is not actually a constraint
            if (!$constraint['data'] instanceof Constraint) {
                throw new ConstraintDefinitionException('The option "constraints" (priority: ' . $constraint['priority'] . ') is not a Constraint, in ' . __CLASS__ . '.');
            }

            // move to next constraint
            $constraintsCopy->next();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions()
    {
        return array('constraints');
    }

    /**
     * Get a clone of the constraints queue
     *
     * @return \SplPriorityQueue
     */
    public function getConstraints()
    {
        return clone $this->constraints;
    }
}
