<?php

/*
 * This file is part of the Antvel Shop package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Antvel\Http\Routes;

use Antvel\Contracts\ComponentRouter;
use Illuminate\Contracts\Routing\Registrar;

class UtilitiesRouter implements ComponentRouter
{
	/**
	 * Register the address book component routes in the given router.
	 *
	 * @param  Registrar $router
	 *
	 * @return void
	 */
	public function registrar(Registrar $router)
	{
		$router->group([

            'middleware' => ['web'],
            'namespace' => '\Support\Images',

        ], function ($router) {

        	$router->get('images/{file?}', 'RenderController@index')->where('file', '(.*)')->name('images2');

        });
	}
}
