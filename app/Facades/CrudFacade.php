<?php 
namespace App\Facades;
use Illuminate\Support\Facades\Facade;

/**  crudfacade
* 
*/
class CrudFacade extends Facade
{
	protected static function getFacadeAccessor() { 
		return 'Crud';
	}
	
}