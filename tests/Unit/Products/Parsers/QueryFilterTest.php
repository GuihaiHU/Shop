<?php

/*
 * This file is part of the Antvel Shop package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antvel\Tests\Unit\Products\Parsers;

use Antvel\Tests\TestCase;
use Antvel\User\Models\User;
use Antvel\Product\Models\Product;
use Antvel\Categories\Models\Category;

class QueryFilterTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->repository = $this->app->make('Antvel\Product\Products');
	}

	public function test_it_can_retrieve_products_by_conditions()
	{
		factory(Product::class)->create(['condition' => 'new']);
		factory(Product::class)->create(['condition' => 'used']);
		factory(Product::class)->create(['condition' => 'refurbished']);

		$products = $this->repository->filter(['conditions' => 'new'])->get();

		$this->assertCount(1, $products);
		foreach ($products as $product) {
			$this->assertEquals('new', $product->condition);
		}
	}

	public function test_it_can_retrieve_products_by_brands()
	{
		factory(Product::class)->create(['brand' => 'samsung']);
		factory(Product::class)->create(['brand' => 'apple']);
		factory(Product::class)->create(['brand' => 'lg']);

		$products = $this->repository->filter(['brands' => 'apple'])->get();

		$this->assertCount(1, $products);
		foreach ($products as $product) {
			$this->assertEquals('apple', $product->brand);
		}
	}

	public function test_it_can_retrieve_products_by_searching()
	{
		factory(Product::class)->create(['name' => 'iPhone 7','description' => 'The iPhone 7 description']);
		factory(Product::class)->create(['name' => 'Galaxy S8','description' => 'The Galaxy S8 description']);
		factory(Product::class)->create(['name' => 'LG G6','description' => 'The LG G6 description']);

		$products = $this->repository->filter([
			'search' => 'iPhone',
		])->get();

		$this->assertCount(1, $products);
		$this->assertEquals('iPhone 7', $products->pluck('name')->first());
		$this->assertEquals('The iPhone 7 description', $products->pluck('description')->first());
	}

	public function test_it_can_retrieve_products_by_price()
	{
		factory(Product::class)->create(['price' => 1000]);
		factory(Product::class)->create(['price' => 2000]);
		factory(Product::class)->create(['price' => 3000]);

		$byMin = $this->repository->filter(['min' => 1000])->get();
		$byMax = $this->repository->filter(['max' => 2000])->get();
		$byMaxAndMax = $this->repository->filter(['min' => 2200, 'max' => 3000])->get();

		$this->assertCount(3, $byMin);
		$this->assertCount(2, $byMax);
		$this->assertCount(1, $byMaxAndMax);
	}

	public function test_it_can_retrieve_all_products()
	{
		factory(Product::class, 2)->create();

		$products = $this->repository->filter()->get();

		$this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $products);
		$this->assertCount(2, $products);
	}

	public function test_it_can_retrieve_products_by_categories()
	{
		$tools = factory(Category::class)->create(['name' => 'tools']);
		$software = factory(Category::class)->create(['name' => 'software']);
		$toolsProducts = factory(Product::class, 2)->create(['category_id' => $tools->id]);
		$softwareProducts = factory(Product::class, 2)->create(['category_id' => $software->id]);

		$byTools = $this->repository->filter([
			'category' => $tools->id . '|' . $tools->name,
		])->get();

		$this->assertCount(2, $byTools);
		$byTools->each(function ($item) use ($tools) {
			$this->assertEquals($tools->id, $item->category_id);
			$this->assertEquals('tools', $item->category->name);
		});
	}

	public function test_it_can_retrieve_products_by_categories_and_its_children()
	{
		$tools = factory(Category::class)->create(['name' => 'tools']);
		$screes = factory(Category::class)->create(['category_id' => $tools->id]);
		$other = factory(Category::class)->create(['name' => 'other']);

		$toolsProducts = factory(Product::class, 2)->create(['category_id' => $tools->id]);
		$softwareProducts = factory(Product::class, 2)->create(['category_id' => $screes->id]);
		$otherProducts = factory(Product::class, 2)->create(['category_id' => $other->id]);

		$byToolsAndChildren = $this->repository->filter([
			'category' => $tools->id . '|' . $tools->name,
		])->get();

		$this->assertCount(4, $byToolsAndChildren);
		$byToolsAndChildren->each(function ($item) use ($tools, $screes) {
			$this->assertTrue(
				$item->category_id == $tools->id || $item->category_id == $screes->id
			);
		});
	}

	public function test_it_can_retrieve_products_by_a_given_advanced_searching()
	{
		//Categories setup
		$category = factory(Category::class)->create(['name' => 'Entertainment']);
		$subCategories = factory(Category::class, 2)->create(['category_id' => $category->id]);
		$other = factory(Category::class)->create(['name' => 'Other']);
		$otherSubCategories = factory(Category::class, 2)->create(['category_id' => $other->id]);

		//Products setup
		factory(Product::class)->create();

		$list = factory(Product::class)->create([
			'description' => 'Entertainment Product',
			'category_id' => $category->id,
			'condition' => 'new',
			'brand' => 'LG',
		]);

		$products = $this->repository->filter([
			'category' => $category->id .'|'. $category->name,
			'min' => $list->pluck('price')->min(),
			'max' => $list->pluck('price')->max(),
			'search' => 'Entertainment',
			'condition' => 'new',
			'brands' => 'LG',
		])->get();

		$this->assertCount(1, $products);
	}
}
