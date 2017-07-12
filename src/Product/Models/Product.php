<?php

/*
 * This file is part of the Antvel Shop package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antvel\Product\Models;

use Antvel\User\Models\User;
use Antvel\Categories\Models\Category;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Concerns\Pictures;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['num_of_reviews']; //while refactoring

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'created_by', 'updated_by', 'name', 'description', 'price', 'cost',
        'stock', 'features', 'barcode', 'condition', 'rate_val', 'tags', 'brand',
        'rate_count', 'low_stock', 'status', 'parent_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['details', 'created_at'];

    /**
     * The default relations.
     *
     * @var array
     */
    protected $with = ['pictures'];

    /**
     * A product belongs to an user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the user who created the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Returns the user who updated the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Returns the category of the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product has many groups.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function group()
    {
        return $this->hasMany($this, 'products_group', 'products_group');
    }

    /**
     * Filter users upon type requested.
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  array $request
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $request)
    {
        return (new QueryFilter($request))->apply($query);
    }

    /**
     * Returns suggestions for a given constraints.
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  string $type
     * @param  array $constraints
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuggestionsFor($query, string $type, array $constraints)
    {
        return (new SuggestionQuery($query))
            ->type($type)
            ->apply($constraints);
    }

    /**
     * Returns the actives products.
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     *
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeActives($query)
    {
        return $query->where('status', 1)->where('stock', '>', 0);
    }

    /**
     * Returns the features product transformed to an array.
     *
     * @return array
     */
    public function getFeaturesAttribute()
    {
        return json_decode($this->attributes['features'], true);
    }

    /**
     * Set the product tags.
     *
     * @param  string  $value
     * @return void
     */
    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = (string) str_replace(' ', ',',
            mb_strtolower($value)
        );
    }

    /**
     * Formats the product cost to dollars.
     *
     * @return mixed
     */
    public function getCostInDollarsAttribute()
    {
        return number_format($this->cost / 100, 2);
    }

    /**
     * Formats the product price to dollars.
     *
     * @return mixed
     */
    public function getPriceInDollarsAttribute()
    {
        return number_format($this->price / 100, 2);
    }

    /////////// while refactoring
    public function details()
    {
        return $this->hasMany('App\OrderDetail');
    }

    public function getNumOfReviewsAttribute()
    {
        return $this->rate_count.' '.\Lang::choice('store.review', $this->rate_count);
    }
}
