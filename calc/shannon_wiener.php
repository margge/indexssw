<?php        
require_once 'calc.php';
class ShannonWiener extends FormulaCalculator {
	
	public function calc(){
		// calculate sum...
		$sum = 0;
		foreach($this->individuals as $individual){
			$pi = $individual / $this->numIndividuals;
			$sum += $pi * log($pi, 2);
		}         

		// calculate H'
		return $sum * -1;
	}
}