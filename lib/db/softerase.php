<?php

/**
 *  SoftErase - "Soft Erase The Model"
 *
 *  The contents of this file are subject to the terms of the GNU General
 *  Public License Version 3.0. You may not use this file except in
 *  compliance with the license. Any of the license terms and conditions
 *  can be waived if you get permission from the copyright holder.
 *
 *  
 *  Copyright (c) 2016 by acemureithi
 *  Geoffrey Mureithi <info@geoffreymureithi.me.ke>
 *  https://github.com/acemureithi/f3-softerase/
 *
 *  @package DB
 *  @version 0.1
 *  @date 01.05.2016
 */
namespace DB;
trait SoftErase {
	protected $deleteTime = 'deleted_at'; //The Field to store the time of erasing.(Timestamp)
	protected $bypass = false; //Bypass soft erase
	
	/**
	 * Get the (not Deleted) Filter.
	 *
	 * @return array|null
	 */
	public function notDeletedFilter(){
		if($this->bypass)
			return null;
		if ($this instanceof \DB\Jig\Mapper)
			return array('@' . $this->deleteTime . ' = ?', 0); // Jig requires @ before fields.
		return array($this->deleteTime . ' = ?', 0);
	}
	
	/**
	 * Force a hard (normal) erase.
	 *
	 * @return bool
	 */
	public function forceErase($filter = null)
	{
		$this->bypass = true;
		if ($filter)
			$result = parent::erase($filter);
		$result = parent::erase();
		$this->bypass = false;
		return $result;
	}
	
	/**
	 * Perform a soft erase.
	 *
	 * @return bool
	 */
	public function erase($filter = NULL){
		$this->{$this->deleteTime} = time();
		$this->bypass = true;
		$result = $this->update() && $this->{$this->deleteTime}>0;
		$this->bypass = false;
		return $result;
	}
	/**
	 * @filesource {ikkez/f3-cortex}
	 * merge multiple filters
	 * @param array $filters
	 * @param string $glue
	 * @return array
	 */
	public function mergeFilter($filters,$glue='and') {
		$crit = array();
		$params = array();
		if ($filters) {
			foreach($filters as $filter) {
				$crit[] = array_shift($filter);
				$params = array_merge($params,$filter);
			}
			array_unshift($params,'( '.implode(' ) '.$glue.' ( ',$crit).' )');
		}
		return $params;
	}
	/**
	 * Retrieve first object that satisfies criteria
	 * @param null  $filter
	 * @param array $options
	 * @param int   $ttl
	 * @return mixed
	 */
	public function load($filter=NULL,array $options=NULL,$ttl=0) {
		if($this->bypass)
			return parent::load($filter,$options,$ttl);
		if(!is_null($filter) && $this->notDeletedFilter())
			return parent::load($this->mergeFilter(array($this->notDeletedFilter(),$filter)),$options,$ttl);
		return parent::load($this->notDeletedFilter(),$options,$ttl);
	}
	
	/**
	 *	Return records (array of mapper objects) that match criteria
	 *	@return mixed
	 *	@param $filter string|array
	 *	@param $options array
	 *	@param $ttl int
	 **/
	public function find($filter=NULL,array $options=NULL,$ttl=0) {
		if($this->bypass)
			return parent::find($filter,$options,$ttl);
		if(!is_null($filter) && $this->notDeletedFilter() )
			return parent::find($this->mergeFilter(array($this->notDeletedFilter(),$filter)),$options,$ttl);
		return parent::find($this->notDeletedFilter(),$options,$ttl);
	}
	
	/**
	 * Restore a soft-erased record.
	 *
	 * @return bool|null
	 */
	public function restore()
	{
		$this->{$this->deleteTime} = 0;
		
		$result = $this->update();
	
		return $result;
	}
	
	/**
	 * Ensure a record is inserted with the not erased state.
	 *
	 * @return bool|null
	 */
	public function save()
	{
		$this->{$this->deleteTime} = @$this->{$this->deleteTime}?$this->{$this->deleteTime}:0;
		$this->bypass = true;
		$result = parent::save();
		$this->bypass = false;
	
		return $result;
	}
	/**
	 * Get a Cursor instance that includes soft erases.
	 * @param \DB\Cursor $mapper
	 * @return array
	 * 
	 */
	public static function onlyErased(\DB\Cursor $mapper)
	{
		$instance = $mapper;
		$instance->bypass = true;
		$filter = array($mapper->deleteTime . ' > ?', 0);
		if ($mapper instanceof \DB\Jig\Mapper)
			$filter = array('@' . $mapper->deleteTime . ' > ?', 0); // Jig requires @ before fields.
		return $instance->find($filter);
	}
	
	/**
	 * Determine if the cursor instance has been soft-deleted.
	 *
	 * @return bool
	 */
	public function erased()
	{
		return !($this->{$this->deleteTime} == 0);
	}
	
}
