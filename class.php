<?php
/*******************************************************************************
* CLASS    CUSTOM                                                                    *
*                                                                              *
* Version: 0.01                                                                *
* Date:    2020-10-20                                                          *
* Author:  Guido Valle                                                         *
*******************************************************************************/

class CUSTOM
{
	/*******************************************************************************
	*                               Public methods                                 *
	*******************************************************************************/

	function __construct($f_name)
	{
		// Some checks
		$this->_dochecks();
		// Verifico che il file che serve alla classe sia esistente e leggibile
		if ( ! is_readable( $f_name ) ) {
 		    die( 'Il file non &egrave; leggibile oppure non esiste!' );
		}
	}
	
	public function intesta_dati($f_name)
	{
		// uso una funzione per estrarre i nomi dei campi dati da un file  
		$csv = array_map(function ($n) {return str_getcsv($n,';','"'); }, file($f_name));
		// print_r($csv);
		return $csv;
	}

	public function carica_dati($f_name)
	{
		// questa funzione carica le righe in un array multidimensionale
		// nel caso di utilizzo di un DB, basta modificare questa funzione per accedere ad una tabella
		$csv = array_map(function ($n) {return str_getcsv($n,';','"'); }, file($f_name));
		array_walk($csv, function(&$a) use ($csv) {
		  $a = array_combine($csv[0], $a);
		});
		array_shift($csv); # remove column header
		return $csv;
	}
	
	public function unique_multidimensional_array($arr,$key)
	{
		// serve una funzione apposita, in quanto la nativa funzione di unique non funziona negli array multidimensionali
		$res = array();
		foreach($arr as $val){
			print_r($val);
			if (!in_array($val[$key], $res)) {
				$res[]=$val[$key];
			}
		}
		return $res;		
	}
	
	function getCurrencyIso ($str){
		// dal simbolo presente nel file csv ottengo la codifica ISO per avere il cambio online e la sigla per la visualizzazione HTML
		// N.B. ci sono problemi di codifica dei simboli, così utilizzo un codice calcolato con la somma degli ASCII code 
		$valute = array(
			array('sym'=>' ','calc'=>0,'iso'=>'','sgl'=>'&#164;'),
			array('sym'=>'€','calc'=>528,'iso'=>'EUR','sgl'=>'&#8364;'),
			array('sym'=>'£','calc'=>357,'iso'=>'GBP','sgl'=>'&#163;'),
			array('sym'=>'$','calc'=>36, 'iso'=>'USD','sgl'=>'&#36;'),
			array('sym'=>'?','calc'=>545,'iso'=>'RUB','sgl'=>'&#8381;'),
			array('sym'=>'¥','calc'=>359,'iso'=>'JPY','sgl'=>'&#165;')
		);
		$id = $this->_chkSimboliValute($str);
		$key = array_search($id, array_column($valute, 'calc'));
		if(is_null($key) or $key<0){
			$iso = "";
			$sgl = "&#164;";
		}else{
			$iso = $valute[$key]['iso'];
			$sgl = $valute[$key]['sgl'];	
		}
		return array($sgl,$iso);
	}

	function convertCurrency($API_FIXER, $amount, $from = 'USD', $to = 'EUR'){
		// uso una chiamata API a fixer.io per avere l*ultima quotazione della valuta richiesta
		$curl = file_get_contents("http://data.fixer.io/api/latest?access_key=$API_FIXER&symbols=$from,$to");
		$arr = json_decode($curl,true);
		$from = $arr['rates'][$from];
		$to = $arr['rates'][$to];
		$rate = $to / $from;
		$result = round($amount * $rate, 2);
		return $result;
	}

	/*******************************************************************************
	*                              Protected methods                               *
	*******************************************************************************/

	protected function _dochecks()
	{
		// Check mbstring overloading
		// Warning This feature has been DEPRECATED as of PHP 7.2.0. Relying on this feature is highly discouraged.
		if(ini_get('mbstring.func_overload') & 2)
 		    die( 'mbstring overloading must be disabled' );
		// Ensure runtime magic quotes are disabled
		// Warning This function has been DEPRECATED as of PHP 7.4.0. Relying on this function is highly discouraged.
		if(get_magic_quotes_runtime())
			@set_magic_quotes_runtime(0);
	}
	
	protected function _chkSimboliValute($var)
	{	
		$ar = str_split($var);
		$res=0;
		for($x=0;$x<count($ar);++$x){
			if(is_numeric($ar[$x]) ) break;
			$res= $res + ord($ar[$x]) ;
		}
		return $res;
	}
}