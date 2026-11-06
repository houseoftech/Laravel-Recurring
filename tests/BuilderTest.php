<?php

declare(strict_types=1);

namespace BrianFaust\Tests\Recurring;

use BrianFaust\Recurring\Builder;
use BrianFaust\Recurring\Traits\Recurring;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private function model(array $config): Model
    {
        return new class($config) extends Model {
            use Recurring;

            public function __construct(private array $recurringConfig = [])
            {
                parent::__construct();
            }

            public function getRecurringConfig()
            {
                return $this->recurringConfig + [
                    'timezone' => 'Europe/Berlin',
                    'end_date' => null,
                    'interval' => null,
                    'count' => null,
                    'by_day' => null,
                    'until' => null,
                    'exceptions' => null,
                    'inclusions' => null,
                ];
            }
        };
    }

    public function test_recurr_returns_builder(): void
    {
        $model = $this->model(['start_date' => '2026-01-05 10:00:00', 'frequency' => 'DAILY', 'count' => 3]);

        $this->assertInstanceOf(Builder::class, $model->recurr());
    }

    public function test_daily_schedule_with_count(): void
    {
        $builder = $this->model([
            'start_date' => '2026-01-05 10:00:00',
            'frequency' => 'DAILY',
            'count' => 3,
        ])->recurr();

        $this->assertCount(3, $builder->schedule());
        $this->assertInstanceOf(Carbon::class, $builder->first());
        $this->assertSame('2026-01-05 10:00', $builder->first()->format('Y-m-d H:i'));
        $this->assertSame('2026-01-07 10:00', $builder->last()->format('Y-m-d H:i'));
    }

    public function test_weekly_by_day_until_with_exceptions(): void
    {
        $builder = $this->model([
            'start_date' => '2026-01-05 10:00:00', // Monday
            'frequency' => 'WEEKLY',
            'by_day' => 'MO,WE',
            'until' => '2026-01-18 23:59:59',
            'exceptions' => '2026-01-07 10:00:00',
        ])->recurr();

        $dates = array_map(
            fn ($r) => $r->getStart()->format('Y-m-d'),
            $builder->schedule()->toArray()
        );

        $this->assertSame(['2026-01-05', '2026-01-12', '2026-01-14'], array_values($dates));
    }

    public function test_schedule_between_limits_range(): void
    {
        $builder = $this->model([
            'start_date' => '2026-01-01 08:00:00',
            'frequency' => 'DAILY',
        ])->recurr();

        $between = $builder->scheduleBetween('2026-01-10 00:00:00', '2026-01-12 23:59:59');

        $this->assertCount(3, $between);
    }

    public function test_invalid_frequency_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->model(['start_date' => '2026-01-05 10:00:00', 'frequency' => 'FORTNIGHTLY'])->recurr()->getFrequencyType();
    }
}
