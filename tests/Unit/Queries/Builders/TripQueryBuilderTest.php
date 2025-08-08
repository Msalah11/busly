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
    $builder = new TripQueryBuilder(['id', 'origin_city_id', 'destination_city_id']);

    expect($builder)->toBeInstanceOf(TripQueryBuilder::class);
});

it('can be created using constructor', function (): void {
    $builder = new TripQueryBuilder;

    expect($builder)->toBeInstanceOf(TripQueryBuilder::class);
});

it('can be created using constructor with custom columns', function (): void {
    $builder = new TripQueryBuilder(['id', 'origin']);

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
        $result2 = (new TripQueryBuilder)->active(false);

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
        $result1 = (new TripQueryBuilder)->orderByDeparture('asc');
        $result2 = (new TripQueryBuilder)->orderByDeparture('desc');

        expect($result1)->toBeInstanceOf(TripQueryBuilder::class);
        expect($result2)->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('can chain multiple methods together', function (): void {
        $result = (new TripQueryBuilder)
            ->search('Cairo')
            ->byRoute('Cairo', 'Alexandria')
            ->active(true);

        expect($result)->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('can chain complex method combinations', function (): void {
        $result = (new TripQueryBuilder)
            ->search('Express')
            ->byRoute('Cairo', 'Alexandria')
            ->active(true)
            ->withAvailableSeats(2)
            ->orderByDeparture('asc');

        expect($result)->toBeInstanceOf(TripQueryBuilder::class);
    });
});

describe('parameter validation', function (): void {
    it('search method accepts various parameter combinations', function (): void {
        // Different search terms
        expect((new TripQueryBuilder)->search('Cairo'))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->search(''))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->search(null))->toBeInstanceOf(TripQueryBuilder::class);

        // Different columns (note: search method still exists for backward compatibility)
        expect((new TripQueryBuilder)->search('Cairo', ['origin_city_id']))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->search('Cairo', ['origin_city_id', 'destination_city_id']))->toBeInstanceOf(TripQueryBuilder::class);

        // Case sensitivity
        expect((new TripQueryBuilder)->search('cairo', ['origin_city_id'], true))->toBeInstanceOf(TripQueryBuilder::class);

        // New city-based search
        expect((new TripQueryBuilder)->searchByRoute('Cairo'))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('byRoute method accepts various combinations', function (): void {
        expect((new TripQueryBuilder)->byRoute('Cairo', 'Alexandria'))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->byRoute('Cairo', null))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->byRoute(null, 'Alexandria'))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->byRoute(null, null))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('byDepartureDate method accepts various date formats', function (): void {
        $carbon = Carbon::now();
        $string = '2023-01-01';

        expect((new TripQueryBuilder)->byDepartureDate($carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->byDepartureDate($string))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->byDepartureDate(null))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('departureBetween method accepts various date formats', function (): void {
        $carbon = Carbon::now();
        $string = '2023-01-01';

        expect((new TripQueryBuilder)->departureBetween($carbon, $carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->departureBetween($string, $string))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->departureBetween($carbon, $string))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->departureBetween($string, $carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->departureBetween(null, $carbon))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->departureBetween($carbon, null))->toBeInstanceOf(TripQueryBuilder::class);

        // Inclusive parameter
        expect((new TripQueryBuilder)->departureBetween($carbon, $carbon, true))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->departureBetween($carbon, $carbon, false))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('withAvailableSeats method accepts positive integers', function (): void {
        expect((new TripQueryBuilder)->withAvailableSeats(1))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->withAvailableSeats(5))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->withAvailableSeats(50))->toBeInstanceOf(TripQueryBuilder::class);
    });

    it('withAvailableSeats method throws exception for invalid values', function (): void {
        expect(fn (): \App\Queries\Builders\TripQueryBuilder => (new TripQueryBuilder)->withAvailableSeats(0))
            ->toThrow(InvalidArgumentException::class);

        expect(fn (): \App\Queries\Builders\TripQueryBuilder => (new TripQueryBuilder)->withAvailableSeats(-1))
            ->toThrow(InvalidArgumentException::class);
    });

    it('orderByDeparture method accepts valid directions', function (): void {
        expect((new TripQueryBuilder)->orderByDeparture('asc'))->toBeInstanceOf(TripQueryBuilder::class);
        expect((new TripQueryBuilder)->orderByDeparture('desc'))->toBeInstanceOf(TripQueryBuilder::class);
    });
});

describe('constructor methods', function (): void {
    it('constructor creates new instances', function (): void {
        $builder1 = new TripQueryBuilder;
        $builder2 = new TripQueryBuilder;

        expect($builder1)->toBeInstanceOf(TripQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(TripQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });

    it('constructor with columns creates new instances', function (): void {
        $builder1 = new TripQueryBuilder(['id', 'origin']);
        $builder2 = new TripQueryBuilder(['destination', 'departure_time']);

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

            'byDepartureDate',
            'departureBetween',
            'withAvailableSeats',
            'orderByDeparture',
        ];

        foreach ($expectedMethods as $method) {
            expect(method_exists($this->builder, $method))->toBeTrue(sprintf('Method %s should exist', $method));
        }
    });
});
