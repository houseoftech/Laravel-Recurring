<?php

/*
 * This file is part of Laravel Recurring.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Recurring\Traits;

use BrianFaust\Recurring\Builder;

trait Recurring
{
    /**
     * @return Builder
     */
    public function recurr(): Builder
    {
        return new Builder($this);
    }

    /**
     * Configuration for the Recurring Trait.
     *
     * @var array
     */
    public function getRecurringConfig()
    {
        return [
            'start_date' => $this->{config('laravel-recurring.start_date')},
            'end_date'   => $this->{config('laravel-recurring.end_date')},
            'timezone'   => $this->{config('laravel-recurring.timezone')},
            'frequency'  => $this->{config('laravel-recurring.frequency')},
            'interval'   => $this->{config('laravel-recurring.interval')},
            'count'      => $this->{config('laravel-recurring.count')},
            'by_day'     => $this->{config('laravel-recurring.by_day')},
            'until'      => $this->{config('laravel-recurring.until')},
            'exceptions' => $this->{config('laravel-recurring.exceptions')},
            'inclusions' => $this->{config('laravel-recurring.inclusions')},
        ];
    }
}
