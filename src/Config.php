<?php

/*
 * This file is part of Laravel Recurring.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Recurring;

use Recurr\Frequency;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use DateTime;

class Config implements Arrayable
{
    /** @var \DateTime */
    private $startDate;

    /** @var \DateTime */
    private $endDate;

    /** @var string */
    private $timezone;

    /** @var int */
    private $frequency;

    /** @var string */
    private $byDay;

    /** @var string */
    private $until;

    /** @var int */
    private $interval;

    /** @var int */
    private $count;

    /** @var array */
    private $exceptions;

    /** @var array */
    private $inclusions;

    /** @var array */
    private $frequencies = [
        Frequency::YEARLY => 'YEARLY',
        Frequency::MONTHLY => 'MONTHLY',
        Frequency::WEEKLY => 'WEEKLY',
        Frequency::DAILY => 'DAILY',
        Frequency::HOURLY => 'HOURLY',
        Frequency::MINUTELY => 'MINUTELY',
        Frequency::SECONDLY => 'SECONDLY',
    ];

    /**
     * @param array      $config
     */
    public function __construct($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->{'set' . Str::studly($key)}($value);
        }
    }

    /**
     * @return DateTime
     */
    public function convertDate($date)
    {
        return ($date instanceof DateTime ? $date : new DateTime($date));
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setStartDate($value): Config
    {
        $this->startDate = $this->convertDate($value);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setEndDate($value): Config
    {
        $this->endDate = $this->convertDate($value);

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $value
     *
     * @return Config
     */
    public function setTimezone($value): Config
    {
        $this->timezone = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    /**
     * @param int $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setFrequency($value): Config
    {
        $this->frequency = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getByDay(): string
    {
        return $this->byDay;
    }

    /**
     * @param string $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setByDay($value): Config
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        $this->byDay = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getUntil(): string
    {
        return $this->until;
    }

    /**
     * @param string $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setUntil($value): Config
    {
        $this->until = $this->convertDate($value);

        return $this;
    }

    /**
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * @param int $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setInterval($value): Config
    {
        $this->interval = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setCount($value): Config
    {
        $this->count = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getExceptions(): string
    {
        return $this->exceptions;
    }

    /**
     * @param string $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setExceptions($value): Config
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        } elseif (is_a($value, 'Illuminate\Database\Eloquent\Collection')) {
            $value = $value->pluck('date')->toArray();
        }
        $this->exceptions = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getInclusions(): string
    {
        return $this->inclusions;
    }

    /**
     * @param string $value
     *
     * @return \BrianFaust\Recurring\Config
     */
    public function setInclusions($value): Config
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        } elseif (is_a($value, 'Illuminate\Database\Eloquent\Collection')) {
            $value = $value->pluck('date')->toArray();
        }
        $this->inclusions = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getFrequencies(): array
    {
        return $this->frequencies;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'timezone' => $this->timezone,
            'frequency' => $this->frequency,
            'by_day' => $this->byDay,
            'until' => $this->until,
            'interval' => $this->interval,
            'count' => $this->count,
            'exceptions' => $this->exceptions,
            'inclusions' => $this->inclusions,
        ];
    }
}
