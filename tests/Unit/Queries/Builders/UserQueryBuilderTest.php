<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\Builders\UserQueryBuilder;
use Carbon\Carbon;

beforeEach(function () {
    $this->builder = new UserQueryBuilder();
});

it('can be constructed with default columns', function () {
    $builder = new UserQueryBuilder();
    
    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

it('can be constructed with custom columns', function () {
    $builder = new UserQueryBuilder(['id', 'name', 'email']);
    
    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

it('can be created using static make method', function () {
    $builder = UserQueryBuilder::make();
    
    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

it('can be created using static make method with custom columns', function () {
    $builder = UserQueryBuilder::make(['id', 'name']);
    
    expect($builder)->toBeInstanceOf(UserQueryBuilder::class);
});

describe('method chaining', function () {
    it('search method returns self for chaining', function () {
        $result = $this->builder->search('john');
        
        expect($result)->toBe($this->builder);
    });

    it('search method accepts null values', function () {
        $result = $this->builder->search(null);
        
        expect($result)->toBe($this->builder);
    });

    it('verified method returns self for chaining', function () {
        $result = $this->builder->verified();
        
        expect($result)->toBe($this->builder);
    });

    it('verified method accepts boolean parameters', function () {
        $result1 = $this->builder->verified(true);
        $result2 = UserQueryBuilder::make()->verified(false);
        
        expect($result1)->toBe($this->builder);
        expect($result2)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('createdBetween method returns self for chaining', function () {
        $result = $this->builder->createdBetween(Carbon::now()->subDays(7), Carbon::now());
        
        expect($result)->toBe($this->builder);
    });

    it('createdBetween method accepts null values', function () {
        $result = $this->builder->createdBetween(null, null);
        
        expect($result)->toBe($this->builder);
    });

    it('convenience date methods return self for chaining', function () {
        expect($this->builder->createdToday())->toBe($this->builder);
        
        // Create new instances to avoid filter conflicts
        expect(UserQueryBuilder::make()->createdThisWeek())->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdThisMonth())->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('ordering methods return self for chaining', function () {
        expect($this->builder->orderByName())->toBe($this->builder);
        expect(UserQueryBuilder::make()->orderByCreated())->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('ordering methods accept direction parameters', function () {
        $result1 = UserQueryBuilder::make()->orderByName('desc');
        $result2 = UserQueryBuilder::make()->orderByCreated('asc');
        
        expect($result1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($result2)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('active method returns self for chaining', function () {
        $result = $this->builder->active();
        
        expect($result)->toBe($this->builder);
    });

    it('active method accepts custom parameters', function () {
        $result1 = UserQueryBuilder::make()->active(7);
        $result2 = UserQueryBuilder::make()->active(30, false);
        $result3 = UserQueryBuilder::make()->active(14, true);
        
        expect($result1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($result2)->toBeInstanceOf(UserQueryBuilder::class);
        expect($result3)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('can chain multiple methods together', function () {
        $result = UserQueryBuilder::make()
            ->search('john')
            ->verified(true)
            ->orderByName('asc');
        
        expect($result)->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('can chain complex method combinations', function () {
        $result = UserQueryBuilder::make()
            ->search('admin')
            ->verified(false)
            ->createdThisMonth()
            ->orderByCreated('asc')
            ->active(7, false);
        
        expect($result)->toBeInstanceOf(UserQueryBuilder::class);
    });
});

describe('parameter validation', function () {
    it('search method accepts various parameter combinations', function () {
        // Different search terms
        expect(UserQueryBuilder::make()->search('test'))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->search(''))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->search(null))->toBeInstanceOf(UserQueryBuilder::class);
        
        // Different columns
        expect(UserQueryBuilder::make()->search('test', ['name']))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->search('test', ['name', 'email']))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->search('test', ['username', 'full_name']))->toBeInstanceOf(UserQueryBuilder::class);
        
        // Case sensitivity
        expect(UserQueryBuilder::make()->search('test', ['name'], true))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->search('test', ['name'], false))->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('createdBetween method accepts various date formats', function () {
        $carbon = Carbon::now();
        $string = '2023-01-01';
        
        expect(UserQueryBuilder::make()->createdBetween($carbon, $carbon))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdBetween($string, $string))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdBetween($carbon, $string))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdBetween($string, $carbon))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdBetween(null, $carbon))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdBetween($carbon, null))->toBeInstanceOf(UserQueryBuilder::class);
        
        // Inclusive parameter
        expect(UserQueryBuilder::make()->createdBetween($carbon, $carbon, true))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->createdBetween($carbon, $carbon, false))->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('ordering methods accept valid directions', function () {
        expect(UserQueryBuilder::make()->orderByName('asc'))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->orderByName('desc'))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->orderByCreated('asc'))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->orderByCreated('desc'))->toBeInstanceOf(UserQueryBuilder::class);
    });

    it('active method accepts various parameter combinations', function () {
        expect(UserQueryBuilder::make()->active())->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->active(7))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->active(30, true))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->active(30, false))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->active(1, true))->toBeInstanceOf(UserQueryBuilder::class);
        expect(UserQueryBuilder::make()->active(365, false))->toBeInstanceOf(UserQueryBuilder::class);
    });
});

describe('static factory methods', function () {
    it('make method creates new instances', function () {
        $builder1 = UserQueryBuilder::make();
        $builder2 = UserQueryBuilder::make();
        
        expect($builder1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });

    it('make method with columns creates new instances', function () {
        $builder1 = UserQueryBuilder::make(['id', 'name']);
        $builder2 = UserQueryBuilder::make(['email', 'created_at']);
        
        expect($builder1)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder2)->toBeInstanceOf(UserQueryBuilder::class);
        expect($builder1)->not->toBe($builder2);
    });
});

describe('inheritance', function () {
    it('extends AbstractQueryBuilder', function () {
        expect($this->builder)->toBeInstanceOf(\App\Queries\Core\AbstractQueryBuilder::class);
    });

    it('inherits parent methods', function () {
        // Test that parent methods are available
        expect(method_exists($this->builder, 'build'))->toBeTrue();
        expect(method_exists($this->builder, 'addFilter'))->toBeTrue();
        expect(method_exists($this->builder, 'clearFilters'))->toBeTrue();
    });
});

describe('method existence', function () {
    it('has all expected public methods', function () {
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
            'make',
        ];
        
        foreach ($expectedMethods as $method) {
            expect(method_exists($this->builder, $method))->toBeTrue("Method {$method} should exist");
        }
    });
}); 