<?php

declare(strict_types=1);

use App\Queries\Builders\TripQueryBuilder;
use Carbon\Carbon;

beforeEach(function (): void {
    $this->builder = new TripQueryBuilder;
});

it('can be constructed with default columns', function (): void {
    $builder = new TripQueryBuilder;

    expect($builder)->toBeInstanceOf(TripQueryBuilder::class);
});

it('can be constructed with custom columns', function (): void {
    $builder = new TripQueryBuilder(['id', 'origin', 'destination']);

    expect($builder)->toBeInstanceOf(TripQueryBuilder::class);
});

it('can be created using static make method', function (): void {
    $builder = TripQueryBuilder::make();

    expect($builder)->toBeInstanceOf(TripQueryBuilder::class);
});

it('can be created using static make method with custom columns', function (): void {
    $builder = TripQueryBuilder::make(['id', 'origin']);

    expect($builder)->toBeInstanceOf(TripQueryBuilder::class);
});

describe('method chaining', function (): void {
    it('search method returns self for chaining', function (): void {
        $result = $this->builder->search('Cairo');

        expect($result)->toBe($this->builder);
    });

    it('search method accepts null values', function (): void {
        $result = $this->builder->search(null);

        expect($result)->toBe($this->builder);
    });

    it('byRoute method returns self for chaining', function (): void {
        $result = $this->builder->byRoute('Cairo', 'Alexandria');

        expect($result)->toBe($this->builder);
    });

    it('byRoute method accepts null values', function (): void {
        $result = $this->builder->byRoute(null, null);

        expect($result)->toBe($this->builder);
    });

    it('active method returns self for chaining', function (): void {
        $result = $this->builder->active();

        expect($result)->toBe($this->builder);
    });

    it('active method accepts boolean parameters', function (): void {
        $result1 = $this->builder->active(true);
        $result2 = TripQueryBuilder::make()->active(false);

        expect($result1)->toBe($this->builder);
        expect($result2)->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('upcoming method returns self for chaining', function (): void {
        $result = $this->builder->upcoming();

        expect($result)->toBe($this->builder);
    });

    it('upcoming method accepts boolean parameters', function (): void {
        $result1 = $this->builder->upcoming(true);
        $result2 = TripQueryBuilder::make()->upcoming(false);

        expect($result1)->toBe($this->builder);
        expect($result2)->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('byDepartureDate method returns self for chaining', function (): void {
        $result = $this->builder->byDepartureDate(Carbon::today());

        expect($result)->toBe($this->builder);
    });

    it('byDepartureDate method accepts null values', function (): void {
        $result = $this->builder->byDepartureDate(null);

        expect($result)->toBe($this->builder);
    });

    it('departureBetween method returns self for chaining', function (): void {
        $result = $this->builder->departureBetween(Carbon::today(), Carbon::tomorrow());

        expect($result)->toBe($this->builder);
    });

    it('departureBetween method accepts null values', function (): void {
        $result = $this->builder->departureBetween(null, null);

        expect($result)->toBe($this->builder);
    });

    it('withAvailableSeats method returns self for chaining', function (): void {
        $result = $this->builder->withAvailableSeats(2);

        expect($result)->toBe($this->builder);
    });

    it('orderByDeparture method returns self for chaining', function (): void {
        $result = $this->builder->orderByDeparture();

        expect($result)->toBe($this->builder);
    });

    it('orderByDeparture method accepts direction parameters', function (): void {
        $result1 = TripQueryBuilder::make()->orderByDeparture('asc');
        $result2 = TripQueryBuilder::make()->orderByDeparture('desc');

        expect($result1)->toBeInstanceOf(TripQueryBuilder::class);
        expect($result2)->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('can chain multiple methods together', function (): void {
        $result = TripQueryBuilder::make()
            ->search('Cairo')
            ->byRoute('Cairo', 'Alexandria')
            ->active(true)
            ->upcoming(true);

        expect($result)->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('can chain complex method combinations', function (): void {
        $result = TripQueryBuilder::make()
            ->search('Express')
            ->byRoute('Cairo', 'Alexandria')
            ->active(true)
            ->upcoming(true)
            ->withAvailableSeats(2)
            ->orderByDeparture('asc');

        expect($result)->toBeInstanceOf(TripQueryBuilder::class);
    });
});

describe('parameter validation', function (): void {
    it('search method accepts various parameter combinations', function (): void {
        // Different search terms
        expect(TripQueryBuilder::make()->search('Cairo'))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->search(''))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->search(null))->toBeInstanceOf(TripQueryBuilder::class);

        // Different columns
        expect(TripQueryBuilder::make()->search('Cairo', ['origin']))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->search('Cairo', ['origin', 'destination']))->toBeInstanceOf(TripQueryBuilder::class);

        // Case sensitivity
        expect(TripQueryBuilder::make()->search('cairo', ['origin'], true))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->search('cairo', ['origin'], false))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('byRoute method accepts various combinations', function (): void {
        expect(TripQueryBuilder::make()->byRoute('Cairo', 'Alexandria'))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->byRoute('Cairo', null))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->byRoute(null, 'Alexandria'))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->byRoute(null, null))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('byDepartureDate method accepts various date formats', function (): void {
        $carbon = Carbon::now();
        $string = '2023-01-01';

        expect(TripQueryBuilder::make()->byDepartureDate($carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->byDepartureDate($string))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->byDepartureDate(null))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('departureBetween method accepts various date formats', function (): void {
        $carbon = Carbon::now();
        $string = '2023-01-01';

        expect(TripQueryBuilder::make()->departureBetween($carbon, $carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->departureBetween($string, $string))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->departureBetween($carbon, $string))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->departureBetween($string, $carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->departureBetween(null, $carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->departureBetween($carbon, null))->toBeInstanceOf(TripQueryBuilder::class);

        // Inclusive parameter
        expect(TripQueryBuilder::make()->departureBetween($carbon, $carbon, true))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->departureBetween($carbon, $carbon, false))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('withAvailableSeats method accepts positive integers', function (): void {
        expect(TripQueryBuilder::make()->withAvailableSeats(1))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->withAvailableSeats(5))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->withAvailableSeats(50))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('withAvailableSeats method throws exception for invalid values', function (): void {
        expect(fn (): \App\Queries\Builders\TripQueryBuilder => TripQueryBuilder::make()->withAvailableSeats(0))
            ->toThrow(InvalidArgumentException::class);

        expect(fn (): \App\Queries\Builders\TripQueryBuilder => TripQueryBuilder::make()->withAvailableSeats(-1))
            ->toThrow(InvalidArgumentException::class);
    });

    it('orderByDeparture method accepts valid directions', function (): void {
        expect(TripQueryBuilder::make()->orderByDeparture('asc'))->toBeInstanceOf(TripQueryBuilder::class);
        expect(TripQueryBuilder::make()->orderByDeparture('desc'))->toBeInstanceOf(TripQueryBuilder::class);
    });
});

describe('static factory methods', function (): void {
    it('make method creates new instances', function (): void {
        $builder1 = TripQueryBuilder::make();
        $builder2 = TripQueryBuilder::make();

        expect($builder1)->toBeInstanceOf(TripQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(TripQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });

    it('make method with columns creates new instances', function (): void {
        $builder1 = TripQueryBuilder::make(['id', 'origin']);
        $builder2 = TripQueryBuilder::make(['destination', 'departure_time']);

        expect($builder1)->toBeInstanceOf(TripQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(TripQueryBuilder::class);
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
            'byRoute',
            'active',
            'upcoming',
            'byDepartureDate',
            'departureBetween',
            'withAvailableSeats',
            'orderByDeparture',
            'make',
        ];

        foreach ($expectedMethods as $method) {
            expect(method_exists($this->builder, $method))->toBeTrue(sprintf('Method %s should exist', $method));
        }
    });
});
