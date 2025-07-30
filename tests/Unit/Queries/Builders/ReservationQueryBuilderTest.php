<?php

declare(strict_types=1);

use App\Enums\ReservationStatus;
use App\Queries\Builders\ReservationQueryBuilder;
use Carbon\Carbon;

beforeEach(function (): void {
    $this->builder = new ReservationQueryBuilder;
});

it('can be constructed with default columns', function (): void {
    $builder = new ReservationQueryBuilder;

    expect($builder)->toBeInstanceOf(ReservationQueryBuilder::class);
});

it('can be constructed with custom columns', function (): void {
    $builder = new ReservationQueryBuilder(['id', 'reservation_code', 'user_id']);

    expect($builder)->toBeInstanceOf(ReservationQueryBuilder::class);
});

it('can be created using static make method', function (): void {
    $builder = ReservationQueryBuilder::make();

    expect($builder)->toBeInstanceOf(ReservationQueryBuilder::class);
});

it('can be created using static make method with custom columns', function (): void {
    $builder = ReservationQueryBuilder::make(['id', 'reservation_code']);

    expect($builder)->toBeInstanceOf(ReservationQueryBuilder::class);
});

describe('method chaining', function (): void {
    it('search method returns self for chaining', function (): void {
        $result = $this->builder->search('RES-12345');

        expect($result)->toBe($this->builder);
    });

    it('search method accepts null values', function (): void {
        $result = $this->builder->search(null);

        expect($result)->toBe($this->builder);
    });

    it('withStatus method returns self for chaining', function (): void {
        $result = $this->builder->withStatus(ReservationStatus::CONFIRMED);

        expect($result)->toBe($this->builder);
    });

    it('confirmed method returns self for chaining', function (): void {
        $result = $this->builder->confirmed();

        expect($result)->toBe($this->builder);
    });

    it('cancelled method returns self for chaining', function (): void {
        $result = $this->builder->cancelled();

        expect($result)->toBe($this->builder);
    });

    it('forUser method returns self for chaining', function (): void {
        $result = $this->builder->forUser(1);

        expect($result)->toBe($this->builder);
    });

    it('forTrip method returns self for chaining', function (): void {
        $result = $this->builder->forTrip(1);

        expect($result)->toBe($this->builder);
    });

    it('upcoming method returns self for chaining', function (): void {
        $result = $this->builder->upcoming();

        expect($result)->toBe($this->builder);
    });

    it('upcoming method accepts boolean parameters', function (): void {
        $result1 = $this->builder->upcoming(true);
        $result2 = ReservationQueryBuilder::make()->upcoming(false);

        expect($result1)->toBe($this->builder);
        expect($result2)->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('reservedBetween method returns self for chaining', function (): void {
        $result = $this->builder->reservedBetween(Carbon::today(), Carbon::tomorrow());

        expect($result)->toBe($this->builder);
    });

    it('reservedBetween method accepts null values', function (): void {
        $result = $this->builder->reservedBetween(null, null);

        expect($result)->toBe($this->builder);
    });

    it('orderByCreated method returns self for chaining', function (): void {
        $result = $this->builder->orderByCreated();

        expect($result)->toBe($this->builder);
    });

    it('orderByCreated method accepts direction parameters', function (): void {
        $result1 = ReservationQueryBuilder::make()->orderByCreated('asc');
        $result2 = ReservationQueryBuilder::make()->orderByCreated('desc');

        expect($result1)->toBeInstanceOf(ReservationQueryBuilder::class);
        expect($result2)->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('createdToday method returns self for chaining', function (): void {
        $result = $this->builder->createdToday();

        expect($result)->toBe($this->builder);
    });

    it('can chain multiple methods together', function (): void {
        $result = ReservationQueryBuilder::make()
            ->search('RES')
            ->confirmed()
            ->forUser(1)
            ->upcoming();

        expect($result)->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('can chain complex method combinations', function (): void {
        $result = ReservationQueryBuilder::make()
            ->search('RES-2024')
            ->withStatus(ReservationStatus::CONFIRMED)
            ->forUser(1)
            ->forTrip(5)
            ->upcoming(true)
            ->orderByCreated('desc');

        expect($result)->toBeInstanceOf(ReservationQueryBuilder::class);
    });
});

describe('parameter validation', function (): void {
    it('search method accepts various parameter combinations', function (): void {
        // Different search terms
        expect(ReservationQueryBuilder::make()->search('RES-001'))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->search(''))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->search(null))->toBeInstanceOf(ReservationQueryBuilder::class);

        // Different columns
        expect(ReservationQueryBuilder::make()->search('RES', ['reservation_code']))->toBeInstanceOf(ReservationQueryBuilder::class);

        // Case sensitivity
        expect(ReservationQueryBuilder::make()->search('res', ['reservation_code'], true))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->search('res', ['reservation_code'], false))->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('withStatus method accepts all reservation statuses', function (): void {
        expect(ReservationQueryBuilder::make()->withStatus(ReservationStatus::CONFIRMED))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->withStatus(ReservationStatus::CANCELLED))->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('forUser method accepts positive integers', function (): void {
        expect(ReservationQueryBuilder::make()->forUser(1))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->forUser(100))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->forUser(9999))->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('forUser method throws exception for invalid values', function (): void {
        expect(fn (): \App\Queries\Builders\ReservationQueryBuilder => ReservationQueryBuilder::make()->forUser(0))
            ->toThrow(InvalidArgumentException::class);

        expect(fn (): \App\Queries\Builders\ReservationQueryBuilder => ReservationQueryBuilder::make()->forUser(-1))
            ->toThrow(InvalidArgumentException::class);
    });

    it('forTrip method accepts positive integers', function (): void {
        expect(ReservationQueryBuilder::make()->forTrip(1))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->forTrip(100))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->forTrip(9999))->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('forTrip method throws exception for invalid values', function (): void {
        expect(fn (): \App\Queries\Builders\ReservationQueryBuilder => ReservationQueryBuilder::make()->forTrip(0))
            ->toThrow(InvalidArgumentException::class);

        expect(fn (): \App\Queries\Builders\ReservationQueryBuilder => ReservationQueryBuilder::make()->forTrip(-1))
            ->toThrow(InvalidArgumentException::class);
    });

    it('reservedBetween method accepts various date formats', function (): void {
        $carbon = Carbon::now();
        $string = '2023-01-01';

        expect(ReservationQueryBuilder::make()->reservedBetween($carbon, $carbon))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->reservedBetween($string, $string))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->reservedBetween($carbon, $string))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->reservedBetween($string, $carbon))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->reservedBetween(null, $carbon))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->reservedBetween($carbon, null))->toBeInstanceOf(ReservationQueryBuilder::class);

        // Inclusive parameter
        expect(ReservationQueryBuilder::make()->reservedBetween($carbon, $carbon, true))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->reservedBetween($carbon, $carbon, false))->toBeInstanceOf(ReservationQueryBuilder::class);
    });

    it('orderByCreated method accepts valid directions', function (): void {
        expect(ReservationQueryBuilder::make()->orderByCreated('asc'))->toBeInstanceOf(ReservationQueryBuilder::class);
        expect(ReservationQueryBuilder::make()->orderByCreated('desc'))->toBeInstanceOf(ReservationQueryBuilder::class);
    });
});

describe('static factory methods', function (): void {
    it('make method creates new instances', function (): void {
        $builder1 = ReservationQueryBuilder::make();
        $builder2 = ReservationQueryBuilder::make();

        expect($builder1)->toBeInstanceOf(ReservationQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(ReservationQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });

    it('make method with columns creates new instances', function (): void {
        $builder1 = ReservationQueryBuilder::make(['id', 'reservation_code']);
        $builder2 = ReservationQueryBuilder::make(['user_id', 'trip_id']);

        expect($builder1)->toBeInstanceOf(ReservationQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(ReservationQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });
});

describe('inheritance', function (): void {
    it('extends AbstractQueryBuilder', function (): void {
        expect($this->builder)->toBeInstanceOf(\App\Queries\Core\AbstractQueryBuilder::class);
    });

    it('inherits parent methods', function (): void {
        // Test that parent methods are available
        expect(method_exists($this->builder, 'build'))->toBeTrue();
        expect(method_exists($this->builder, 'addFilter'))->toBeTrue();
        expect(method_exists($this->builder, 'clearFilters'))->toBeTrue();
    });
});

describe('method existence', function (): void {
    it('has all expected public methods', function (): void {
        $expectedMethods = [
            'search',
            'withStatus',
            'confirmed',
            'cancelled',
            'forUser',
            'forTrip',
            'upcoming',
            'reservedBetween',
            'orderByCreated',
            'createdToday',
            'make',
        ];

        foreach ($expectedMethods as $method) {
            expect(method_exists($this->builder, $method))->toBeTrue(sprintf('Method %s should exist', $method));
        }
    });
});
