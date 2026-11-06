<?php

/**
 * This file is part of Laravel Recurring.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Recurring;

use DateTime;
use Recurr\Rule;
use DateTimeZone;
use Carbon\Carbon;
use Recurr\Frequency;
use Recurr\RecurrenceCollection;
use Illuminate\Database\Eloquent\Model;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use Illuminate\Support\Str;

class Builder
{
    /** @var \DateTime */
    private $model;

    /** @var \DateTime */
    private $config;

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->config = $this->buildConfig();
    }

    /**
     * @return bool|\DateTime
     */
    public function first()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return Carbon::instance($schedule->first()->getStart());
    }

    /**
     * @return bool|\DateTime
     */
    public function last()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return Carbon::instance($schedule->last()->getStart());
    }

    /**
     * @return bool|\DateTime
     */
    public function next()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return Carbon::instance($schedule->next()->getStart());
    }

    /**
     * @return bool|\DateTime
     */
    public function current()
    {
        if (!$schedule = $this->schedule()) {
            return false;
        }

        return Carbon::instance($schedule->current()->getStart());
    }

    /**
     * @return \Recurr\RecurrenceCollection
     */
    public function schedule(): RecurrenceCollection
    {
        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();

        $transformer = new ArrayTransformer();
        $transformer->setConfig($transformerConfig);

        return $transformer->transform($this->rule());
    }

    /**
     * @return \Recurr\RecurrenceCollection
     */
    public function scheduleBetween($startDate, $endDate): RecurrenceCollection
    {
        $startDate = $this->config->convertDate($startDate);
        $endDate = $this->config->convertDate($endDate);

        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();

        $transformer = new ArrayTransformer();
        $transformer->setConfig($transformerConfig);

        $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($startDate, $endDate);
        // The $countConstraintFailures in the ArrayTransformer::transform() method
        // decides whether the transformer will stop looping or just count failures
        // toward the limit of recurrences.
        // true = count toward limit
        // false = stop looping
        // We want it to stop looping since we're searching between two dates
        // so that once the dates go beyond the range it will return.
        return $transformer->transform($this->rule(), $constraint, $countConstraintFailures = false);

    }

    /**
     * @return \Recurr\Rule
     */
    public function rule(): Rule
    {
        $config = $this->getConfig();

        $rule = (new Rule())
            ->setStartDate($config['start_date'])
            ->setFreq($this->getFrequencyType());

        if (!empty($config['timezone'])) {
            $rule = $rule->setTimezone($config['timezone']);
        } else {
            $rule->setTimezone(date_default_timezone_get());
        }

        if (!empty($config['interval'])) {
            $rule = $rule->setInterval($config['interval']);
        }

        if (!empty($config['by_day'])) {
            $rule = $rule->setByDay($config['by_day']);
        }

        if (!empty($config['until'])) {
            $rule = $rule->setUntil($config['until']);
        }

        if (!empty($config['count'])) {
            $rule = $rule->setCount($config['count']);
        }

        if (!empty($config['end_date'])) {
            $rule = $rule->setEndDate($config['end_date']);
        }

        if (!empty($config['exceptions'])) {
            $rule->setExDates($config['exceptions']);
        }

        if (!empty($config['inclusions'])) {
            $rule->setRDates($config['inclusions']);
        }

        return $rule;
    }

    /**
     * @return string
     */
    public function getFrequencyType(): string
    {
        $frequency = $this->getFromConfig('frequency');

        if (!in_array($frequency, $this->config->getFrequencies())) {
            throw new \InvalidArgumentException("$frequency is not a valid frequency");
        }

        return $frequency;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    private function getFromConfig($key)
    {
        return $this->config->{'get' . Str::studly($key)}();
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config->toArray();
    }

    /**
     * @return \BrianFaust\Recurring\Config
     */
    private function buildConfig(): Config
    {
        return new Config($this->model->getRecurringConfig());
    }
}
