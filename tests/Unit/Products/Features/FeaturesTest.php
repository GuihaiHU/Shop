<?php

/*
 * This file is part of the Antvel Shop package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antvel\Tests\Unit\Products\Features;

use Antvel\Tests\TestCase;
use Antvel\Product\Models\ProductFeatures;

class FeaturesTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->repository = $this->app->make('Antvel\Product\Features');
	}

	/** @test */
	function it_has_the_correct_model()
	{
	    $this->assertNotNull($this->repository->getModel());
		$this->assertInstanceOf(ProductFeatures::class, $this->repository->getModel());
	}

	/** @test */
	function it_can_list_the_saved_features()
	{
		factory(ProductFeatures::class)->create([
			'name' => 'color'
		]);

		$features = $this->repository->all();

		$this->assertCount(1, $features);
		$this->assertEquals('color', $features->first()->name);
	}

	/** @test */
	function it_can_create_a_new_feature_with_default_values()
	{
		$feature = $this->repository->create(['name' => 'feature']);

		$this->assertTrue($feature->exists());
		$this->assertEquals('feature', $feature->name);
	}

	/** @test */
	function it_can_create_a_new_required_feature()
	{
		$feature = $this->repository->create([
			'name' => 'feature',
			'input_type' => 'text',
			'product_type' => 'item',
			'help_message' => 'Tooltip message',
			'status' => 1,
			'validation_rules' => [
				'required' => 1
			]
		]);

		$this->assertEquals('feature', $feature->name);
		$this->assertEquals('text', $feature->input_type);
		$this->assertEquals('item', $feature->product_type);
		$this->assertEquals('Tooltip message', $feature->help_message);
		$this->assertTrue(!! $feature->status);
		$this->assertEquals('required', $feature->validation_rules);
	}

	/** @test */
	function it_can_update_a_given_feature()
	{
		$feature = factory(ProductFeatures::class)->create()->first();

		$this->repository->update([
			'name' => 'foo'
		], $feature);

		$this->assertEquals('foo', $feature->name);
	}

	/** @test */
	function it_can_update_a_given_required_feature()
	{
		$feature = factory(ProductFeatures::class)->create([
			'validation_rules' => 'required'
		])->first();

		$this->repository->update([
			'name' => 'foo'
		], $feature);

		$this->assertEquals('foo', $feature->name);
		$this->assertNull($feature->validation_rules);
	}

	/** @test */
	function exposes_the_filterable_features_that_are_used_in_products_listing()
	{
		$notAllowed = factory(ProductFeatures::class)->create(['name' => 'bar']);

		$allowed = factory(ProductFeatures::class)->create([
			'name' => 'foo',
			'filterable' => true,
		]);

	    $features = $this->repository->filterable();

	    $this->assertCount(1, $features);

	    tap($features->pluck('name'), function($names) {
	    	$this->assertTrue($names->contains('foo'));
	    	$this->assertFalse($names->contains('bar'));
	    });
	}

	/** @test */
	function generates_an_array_with_the_validation_rules_for_the_filterable_features()
	{
		$featureOne = factory(ProductFeatures::class)->create([
			'name' => 'one',
			'filterable' => true,
			'validation_rules' => 'required|max:20|min:10',
		]);

		$featureTwo = factory(ProductFeatures::class)->create([
			'name' => 'two',
			'filterable' => true,
			'validation_rules' => 'required|min:10',
		]);

		$featureThree = factory(ProductFeatures::class)->create([
			'name' => 'three',
			'filterable' => true,
			'validation_rules' => 'required',
		]);

		tap($this->repository->filterableValidationRules(), function($validationRules) {
			$this->assertTrue(is_array($validationRules));
			$this->assertCount(3, $validationRules);
			$this->assertEquals($validationRules['features.one'], 'required|max:20|min:10');
			$this->assertEquals($validationRules['features.two'], 'required|min:10');
			$this->assertEquals($validationRules['features.three'], 'required');
		});
	}
}
