# Symfony2 Validation priority chain constraint

Note: currently only works for validator component 2.2.x

Prioritised chain of validator constraints in Symfony2 validator component.

## Install

Add `markwilson/symfony2-validator-priority-chain` to composer.json requires.

## Usage

`PriorityChain` requires a `constraint` option which takes either an array (ordered highest priority first), or a SplPriorityQueue. Each item in the queue must be a `Constraint` instance. By default, the validator will loop through each constraint until an error occurs (this can be changed with the `stopOnError` option).

e.g.

```` php
use MarkWilson\Validator\Constraints\PriorityChain;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

$queue = new \SplPriorityQueue();
$queue->insert(new Assert\Type('array'), 2);
$queue->insert(new Assert\NotBlank(), 1);

$constraint = new PriorityChain($queue);

$validator = Validation::createValidator();
$validator->validateValue($value, $constraint);
````
