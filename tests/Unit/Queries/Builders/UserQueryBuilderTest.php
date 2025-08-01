<?php

declare(strict_types=1);

use App\Queries\Builders\UserQueryBuilder;
use Carbon\Carbon;

beforeEach(function (): void {
    $this->builder = new UserQueryBuilder;
});

it('can be constructed with default columns', function (): void {
    $builder = new UserQueryBuilder;

    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

it('can be constructed with custom columns', function (): void {
    $builder = new UserQueryBuilder(['id', 'name', 'email']);

    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

it('can be created using constructor', function (): void {
    $builder = new UserQueryBuilder;

    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

it('can be created using constructor with custom columns', function (): void {
    $builder = new UserQueryBuilder(['id', 'name']);

    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

describe('method chaining', function (): void {
    it('search method returns self for chaining', function (): void {
        $result = $this->builder->search('john');

        expect($result)->toBe($this->builder);
    });

    it('search method accepts null values', function (): void {
        $result = $this->builder->search(null);

        expect($result)->toBe($this->builder);
    });

    it('verified method returns self for chaining', function (): void {
        $result = $this->builder->verified();

        expect($result)->toBe($this->builder);
    });

    it('verified method accepts boolean parameters', function (): void {
        $result1 = $this->builder->verified(true);
        $result2 = (new UserQueryBuilder)->verified(false);

        expect($result1)->toBe($this->builder);
        expect($result2)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('createdBetween method returns self for chaining', function (): void {
        $result = $this->builder->createdBetween(Carbon::now()->subDays(7), Carbon::now());

        expect($result)->toBe($this->builder);
    });

    it('createdBetween method accepts null values', function (): void {
        $result = $this->builder->createdBetween(null, null);

        expect($result)->toBe($this->builder);
    });

    it('convenience date methods return self for chaining', function (): void {
        expect($this->builder->createdToday())->toBe($this->builder);

        // Create new instances to avoid filter conflicts
        expect((new UserQueryBuilder)->createdThisWeek())->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdThisMonth())->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('ordering methods return self for chaining', function (): void {
        expect($this->builder->orderByName())->toBe($this->builder);
        expect((new UserQueryBuilder)->orderByCreated())->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('ordering methods accept direction parameters', function (): void {
        $result1 = (new UserQueryBuilder)->orderByName('desc');
        $result2 = (new UserQueryBuilder)->orderByCreated('asc');

        expect($result1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($result2)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('active method returns self for chaining', function (): void {
        $result = $this->builder->active();

        expect($result)->toBe($this->builder);
    });

    it('active method accepts custom parameters', function (): void {
        $result1 = (new UserQueryBuilder)->active(7);
        $result2 = (new UserQueryBuilder)->active(30, false);
        $result3 = (new UserQueryBuilder)->active(14, true);

        expect($result1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($result2)->toBeInstanceOf(UserQueryBuilder::class);
        expect($result3)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('can chain multiple methods together', function (): void {
        $result = (new UserQueryBuilder)
            ->search('john')
            ->verified(true)
            ->orderByName('asc');

        expect($result)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('can chain complex method combinations', function (): void {
        $result = (new UserQueryBuilder)
            ->search('admin')
            ->verified(false)
            ->createdThisMonth()
            ->orderByCreated('asc')
            ->active(7, false);

        expect($result)->toBeInstanceOf(UserQueryBuilder::class);
    });
});

describe('parameter validation', function (): void {
    it('search method accepts various parameter combinations', function (): void {
        // Different search terms
        expect((new UserQueryBuilder)->search('test'))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->search(''))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->search(null))->toBeInstanceOf(UserQueryBuilder::class);

        // Different columns
        expect((new UserQueryBuilder)->search('test', ['name']))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->search('test', ['name', 'email']))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->search('test', ['username', 'full_name']))->toBeInstanceOf(UserQueryBuilder::class);

        // Case sensitivity
        expect((new UserQueryBuilder)->search('test', ['name'], true))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->search('test', ['name'], false))->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('createdBetween method accepts various date formats', function (): void {
        $carbon = Carbon::now();
        $string = '2023-01-01';

        expect((new UserQueryBuilder)->createdBetween($carbon, $carbon))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdBetween($string, $string))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdBetween($carbon, $string))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdBetween($string, $carbon))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdBetween(null, $carbon))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdBetween($carbon, null))->toBeInstanceOf(UserQueryBuilder::class);

        // Inclusive parameter
        expect((new UserQueryBuilder)->createdBetween($carbon, $carbon, true))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->createdBetween($carbon, $carbon, false))->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('ordering methods accept valid directions', function (): void {
        expect((new UserQueryBuilder)->orderByName('asc'))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->orderByName('desc'))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->orderByCreated('asc'))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->orderByCreated('desc'))->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('active method accepts various parameter combinations', function (): void {
        expect((new UserQueryBuilder)->active())->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->active(7))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->active(30, true))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->active(30, false))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->active(1, true))->toBeInstanceOf(UserQueryBuilder::class);
        expect((new UserQueryBuilder)->active(365, false))->toBeInstanceOf(UserQueryBuilder::class);
    });
});

describe('constructor methods', function (): void {
    it('constructor creates new instances', function (): void {
        $builder1 = new UserQueryBuilder;
        $builder2 = new UserQueryBuilder;

        expect($builder1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });

    it('constructor with columns creates new instances', function (): void {
        $builder1 = new UserQueryBuilder(['id', 'name']);
        $builder2 = new UserQueryBuilder(['email', 'created_at']);

        expect($builder1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(UserQueryBuilder::class);
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
            'verified',
            'createdBetween',
            'createdToday',
            'createdThisWeek',
            'createdThisMonth',
            'orderByName',
            'orderByCreated',
            'active',
        ];

        foreach ($expectedMethods as $method) {
            expect(method_exists($this->builder, $method))->toBeTrue(sprintf('Method %s should exist', $method));
        }
    });
});
