<?php               
require_once 'calc.php';
class Simpson extends FormulaCalculator {
	public function calc(){
		// calculate Dsi...
		$dsi = 0;
		foreach($this->individuals as $individual){
			$pi = $individual / $this->numIndividuals;
			$dsi += $pi * $pi;
		}         

		// calculate SiD...
		return 1 - $dsi;
	}
}