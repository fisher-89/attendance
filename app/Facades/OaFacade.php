<?php 
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class OaFacade extends Facade{
	protected static function getFacadeAccessor() { return 'Oa'; }
}