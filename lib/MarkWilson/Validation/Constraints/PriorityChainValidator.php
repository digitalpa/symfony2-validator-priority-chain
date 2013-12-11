<?php

namespace MarkWilson\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Priority chain validation class
 *
 * @package MarkWilson\Validation\Constraints
 * @author  Mark Wilson <mark@89allport.co.uk>
 */
class PriorityChainValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        $walker = $this->context->getGraphWalker();
        $group  = $this->context->getGroup();

        $propertyPath = $this->context->getPropertyPath();

        $violationList          = $this->context->getViolations();
        $violationCountPrevious = $violationList->count();

        /** @var \SplPriorityQueue $constraintsQueue */
        $constraintsQueue = $constraint->getConstraints();

        // change extraction mode to just data, we don't care about priority
        $constraintsQueue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        foreach ($constraintsQueue as $queuedConstraint) {
            $walker->walkConstraint($queuedConstraint, $value, $group, $propertyPath);

            if ($constraint->stopOnError) {
                if (count($violationList) !== $violationCountPrevious) {
                    return;
                }
            }
        }
    }
}
