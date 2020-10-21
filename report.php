<?php
/*******************************************************************************
* report.php                                                                   *
*                                                                              *
* Version: 0.01                                                                *
* Date:    2020-10-20                                                          *
* Author:  Guido Valle                                                         *
*******************************************************************************/

// nome del file contenente i dati
$f_name="data.csv";

// chiave API per accedere ai dati di cambio su fixer.io
$API_FIXER="fec9f28948bc45fb29df3435df876c16";
// valore con cui vengono filtrati i dati, con * o nulla vengono presentati tutti
$filtro = isset($_GET['filtro'])?$_GET['filtro']:"*";
$cambio = isset($_GET['cambio'])?$_GET['cambio']:"EUR";
$filtro = isset($_POST['customer'])?$_POST['customer']:$filtro;
$cambio = isset($_POST['valuta'])?$_POST['valuta']:$cambio;
$cb_valute = array('EUR'=>'EURO','USD'=>'US DOLLAR','GBP'=>'UK POUND','RUB'=>'RUBLO','JPY'=>'JAPAN YEN');
if (!in_array($cambio, $cb_valute)) { $cambio = "EUR";}
//
print("<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='it'>\n");
setlocale(LC_TIME, 'ita', 'it_IT.ISO-8859-1');
date_default_timezone_set('Europe/Rome');
//
print("<head>\n");
print("<meta charset='utf-8'>\n");
print("<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n");
print("<Content-Disposition: inline;  >\n");
print("<link rel='stylesheet' type='text/css' href='https://www.w3schools.com/w3css/4/w3.css'>\n");
print("</head>\n");
//
print("<body>\n");
// includo il file con la classe
include "class.php";
// apro una nuova istanza della classe
$custom = new CUSTOM($f_name);
if($custom==false){
	echo "Errore nell'apertura della classe !";
}else{
	$fields = $custom->intesta_dati($f_name);
	if(!$fields){
		echo "File Dati Inesistente !";
	}else{
		$dati = $custom->carica_dati($f_name);
		if(!$dati){
			echo "File Dati Inesistente !";
		}else{
			print("<form id='form' name='form' action='report.php' method='POST' >");
				print("<div class='w3-container w3-center'>");
				print("<center>");
					print("<div class='w3-cell-row' style='width:75%' >");					
						print("<div class='w3-container w3-cell w3-pale-blue' style='width:50%'  >");
						    print("<h3>Customer</h3>");
							print("<select name='customer' id='customer' onchange='submit();'>");
							print("<option value='*'>--TUTTI--</option>");
							$arr = $custom->unique_multidimensional_array($dati,'customer');
							foreach($arr as $val){
								print("<option value=$val ");
								if($filtro==$val){
									print(" SELECTED "); 
								}
								print(">$val</option>"); 
							}
							print("</select>");							
						print("</div>");						
						print("<div  class='w3-container w3-cell w3-pale-yellow' style='width:50%'  >");
						    print("<h3>Valuta di cambio</h3>");
							print("<select name='valuta' id='valuta' onchange='submit();'>");
							foreach($cb_valute as $key=>$val){
								print("<option value=$key ");
								if($cambio==$key){
									print(" SELECTED "); 
								}
								print(">$val</option>"); 
							}
							print("</select>");							
						print("</div>");
					print("</div>");
					print("<h2>Report Table</h2>");					
					print("<table class='w3-table w3-striped w3-bordered w3-border' style='width:60%;' >");
					print("<thead>");
					print("<tr>");
					print("<th style='text-align:center;' >");
					print("Customer");
					print("</th>");
					print("<th style='text-align:center;' >");
					print("Data");
					print("</th>");
					print("<th style='text-align:center;' >");
					print("Importo");
					print("</th>");
					print("<th style='text-align:center;' >");
					print("Valuta");
					print("</th>");
					print("<th style='text-align:center;' >");
					print("cambio in $cb_valute[$cambio] ");
					print("</th>");
					print("</tr>");
					print("</thead>");
					print("<tbody>");
					foreach ($dati as $val) {
						if($filtro=='*' or $val['customer']==$filtro){
								print("<tr>");
								print("<td style='text-align:center;' >");
								print($val['customer']);
								print("</td>");
								print("<td style='text-align:center;' >");
								print($val['date']);
								print("</td>");
								list($sgl,$iso) = $custom->getCurrencyIso($val['value']);
								$value = (float) preg_replace('/[^\d\.]/', '', $val['value']);
								print("<td style='text-align:right;' >");
								print($value." ".$sgl);
								print("</td>");
								print("<td style='text-align:center;' >");
								print($iso);
								print("</td>");
								print("<td style='text-align:right;' >");
								if($iso==""){
									echo "Valuta NON riconosciuta!";
								}else if($iso==$cambio){							
									echo number_format($value,2);
									echo " $cambio";
								}else{
									echo number_format($custom->convertCurrency($API_FIXER, $value, $iso,$cambio),2);
									echo " $cambio";
								}				
								print("</td>");
							}
							print("</tr>");
					}
					print("</tbody>");
					print("</table>");
					print("</center>");
				print("</div>");
			print("</form>");
		}
	}
}
print("</body>\n");
print("</html>\n");