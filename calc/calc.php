<?php
abstract class FormulaCalculator{
	var $individuals;
	var $numIndividuals;
	public function __construct($individuals){
		$this->individuals = $individuals;
		
		// get the number of individuals
		$this->numIndividuals = 0;
		foreach($this->individuals as $individual){
			$this->numIndividuals += $individual;
		}         
	}
	
	abstract public function calc();
}