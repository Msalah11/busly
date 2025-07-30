<?php

declare(strict_types=1);

use App\Enums\BusType;
use App\Queries\Builders\BusQueryBuilder;

beforeEach(function (): void {
    $this->builder = new BusQueryBuilder;
});

it('can be constructed with default columns', function (): void {
    $builder = new BusQueryBuilder;

    expect($builder)->toBeInstanceOf(BusQueryBuilder::class);
});

it('can be constructed with custom columns', function (): void {
    $builder = new BusQueryBuilder(['id', 'bus_code', 'capacity']);

    expect($builder)->toBeInstanceOf(BusQueryBuilder::class);
});

it('can be created using static make method', function (): void {
    $builder = BusQueryBuilder::make();

    expect($builder)->toBeInstanceOf(BusQueryBuilder::class);
});

it('can be created using static make method with custom columns', function (): void {
    $builder = BusQueryBuilder::make(['id', 'bus_code']);

    expect($builder)->toBeInstanceOf(BusQueryBuilder::class);
});

describe('method chaining', function (): void {
    it('search method returns self for chaining', function (): void {
        $result = $this->builder->search('BUS001');

        expect($result)->toBe($this->builder);
    });

    it('search method accepts null values', function (): void {
        $result = $this->builder->search(null);

        expect($result)->toBe($this->builder);
    });

    it('ofType method returns self for chaining', function (): void {
        $result = $this->builder->ofType(BusType::STANDARD);

        expect($result)->toBe($this->builder);
    });

    it('active method returns self for chaining', function (): void {
        $result = $this->builder->active();

        expect($result)->toBe($this->builder);
    });

    it('active method accepts boolean parameters', function (): void {
        $result1 = $this->builder->active(true);
        $result2 = BusQueryBuilder::make()->active(false);

        expect($result1)->toBe($this->builder);
        expect($result2)->toBeInstanceOf(BusQueryBuilder::class);
    });

    it('withCapacityFor method returns self for chaining', function (): void {
        $result = $this->builder->withCapacityFor(50);

        expect($result)->toBe($this->builder);
    });

    it('orderByCreated method returns self for chaining', function (): void {
        $result = $this->builder->orderByCreated();

        expect($result)->toBe($this->builder);
    });

    it('orderByCreated method accepts direction parameters', function (): void {
        $result1 = BusQueryBuilder::make()->orderByCreated('asc');
        $result2 = BusQueryBuilder::make()->orderByCreated('desc');

        expect($result1)->toBeInstanceOf(BusQueryBuilder::class);
        expect($result2)->toBeInstanceOf(BusQueryBuilder::class);
    });

    it('can chain multiple methods together', function (): void {
        $result = BusQueryBuilder::make()
            ->search('BUS')
            ->ofType(BusType::VIP)
            ->active(true)
            ->withCapacityFor(30);

        expect($result)->toBeInstanceOf(BusQueryBuilder::class);
    });

    it('can chain complex method combinations', function (): void {
        $result = BusQueryBuilder::make()
            ->search('LUXURY')
            ->ofType(BusType::VIP)
            ->active(true)
            ->withCapacityFor(40)
            ->orderByCreated('asc');

        expect($result)->toBeInstanceOf(BusQueryBuilder::class);
    });
});

describe('parameter validation', function (): void {
    it('search method accepts various parameter combinations', function (): void {
        // Different search terms
        expect(BusQueryBuilder::make()->search('BUS001'))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->search(''))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->search(null))->toBeInstanceOf(BusQueryBuilder::class);

        // Different columns
        expect(BusQueryBuilder::make()->search('BUS', ['bus_code']))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->search('BUS', ['bus_code', 'type']))->toBeInstanceOf(BusQueryBuilder::class);

        // Case sensitivity
        expect(BusQueryBuilder::make()->search('bus', ['bus_code'], true))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->search('bus', ['bus_code'], false))->toBeInstanceOf(BusQueryBuilder::class);
    });

    it('ofType method accepts all bus types', function (): void {
        expect(BusQueryBuilder::make()->ofType(BusType::STANDARD))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->ofType(BusType::VIP))->toBeInstanceOf(BusQueryBuilder::class);
    });

    it('withCapacityFor method accepts positive integers', function (): void {
        expect(BusQueryBuilder::make()->withCapacityFor(1))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->withCapacityFor(50))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->withCapacityFor(100))->toBeInstanceOf(BusQueryBuilder::class);
    });

    it('withCapacityFor method throws exception for invalid values', function (): void {
        expect(fn (): \App\Queries\Builders\BusQueryBuilder => BusQueryBuilder::make()->withCapacityFor(0))
            ->toThrow(InvalidArgumentException::class);

        expect(fn (): \App\Queries\Builders\BusQueryBuilder => BusQueryBuilder::make()->withCapacityFor(-1))
            ->toThrow(InvalidArgumentException::class);
    });

    it('orderByCreated method accepts valid directions', function (): void {
        expect(BusQueryBuilder::make()->orderByCreated('asc'))->toBeInstanceOf(BusQueryBuilder::class);
        expect(BusQueryBuilder::make()->orderByCreated('desc'))->toBeInstanceOf(BusQueryBuilder::class);
    });
});

describe('static factory methods', function (): void {
    it('make method creates new instances', function (): void {
        $builder1 = BusQueryBuilder::make();
        $builder2 = BusQueryBuilder::make();

        expect($builder1)->toBeInstanceOf(BusQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(BusQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });

    it('make method with columns creates new instances', function (): void {
        $builder1 = BusQueryBuilder::make(['id', 'bus_code']);
        $builder2 = BusQueryBuilder::make(['capacity', 'type']);

        expect($builder1)->toBeInstanceOf(BusQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(BusQueryBuilder::class);
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
            'ofType',
            'active',
            'orderByCreated',
            'withCapacityFor',
            'make',
        ];

        foreach ($expectedMethods as $method) {
            expect(method_exists($this->builder, $method))->toBeTrue(sprintf('Method %s should exist', $method));
        }
    });
});
