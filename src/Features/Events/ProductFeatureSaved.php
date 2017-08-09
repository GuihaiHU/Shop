<?php

/*
 * This file is part of the Antvel Shop package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antvel\Features\Events;

use Antvel\Features\Models\Feature;
use Illuminate\Queue\SerializesModels;

class ProductFeatureSaved
{
	use SerializesModels;

    /**
     * The product feature to be updated.
     *
     * @var ProductFeatures
     */
    public $feature = null;

    public $updatedName = '';

    /**
     * Create a new event instance.
     *
     * @param ProductFeatures $feature
     *
     * @return void
     */
    public function __construct(ProductFeatures $feature, $updatedName)
    {
        $this->feature = $feature;
        $this->updatedName = $updatedName;
    }
}
