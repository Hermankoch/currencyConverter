<?php

add_action( 'woocommerce_after_checkout_form', 'custom_currency_conversion');

function custom_currency_conversion() {
  global $woocommerce;
  $cart_total = $woocommerce->cart->total;
  ?>
  <script>
  var country = document.getElementById("billing_country");
  var countryVal = country.value;
  var countryCheckVal = "";
  var countryRequest = false;
  var convertRequest;
  var valCountries;
  var valConvert;
  var apiKey = '62590b1abdad7631ffb2';
  var checkLoaded = false;
  var checkConverted = false;
  var query;
  var finalNum;
  var currencySymbol;
  var timer = true;
  <?php echo 'var amount = '.$cart_total; ?>

  function getRequestCountry(){
    try {
      countryRequest = new XMLHttpRequest();
    }
    catch(requestError){
      console.log('Does not support');
      return false;
    }
    fillCountryValues();
    return countryRequest;
  }

  function fillCountryValues(){
   countryRequest.open('get', 'https://free.currconv.com/api/v7/countries?apiKey=62590b1abdad7631ffb2');
   countryRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   countryRequest.send(null);
   countryRequest.onreadystatechange = fillCountry;
  }

  function fillCountry(){
    if(countryRequest.readyState === 4 && countryRequest.status === 200) {
      valuesCountries = JSON.parse(countryRequest.responseText);
      countryRequest.abort();
      checkLoaded = true;
      convertValues();
    }
  }

  function convertValues(){
   var countryFrom = 'ZAR';
   var currencyId = valuesCountries['results'][countryVal]['currencyId'];
   //To display symbol and name in checkout
   currencySymbol = valuesCountries['results'][countryVal]['currencySymbol'];
   currencyName = valuesCountries['results'][countryVal]['currencyName'];
   //Convert from ZAR-> User selected country
   countryFrom = encodeURIComponent(countryFrom);
   currencyId = encodeURIComponent(currencyId);
   query = countryFrom + '_' + currencyId;
   var url = 'https://free.currconv.com/api/v7/convert?q='+ query + '&compact=ultra&apiKey=' + apiKey;
   convertRequest = new XMLHttpRequest();
   convertRequest.open('get', url);
   convertRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   convertRequest.send(null);
   convertRequest.onreadystatechange = convertPopulate;
  }

  function convertPopulate(){
   if(convertRequest.readyState === 4 && convertRequest.status === 200) {
     valConvert = JSON.parse(convertRequest.responseText);
     convertRequest.abort();
     checkConverted = true;
     convertFinal();
   }
  }

  function convertFinal(){
   if(checkConverted){
     var val = valConvert[query];
     if (val) {
       var total = val * amount;
       finalNum = (Math.round(total * 100)) / 100;
       var xTable = document.getElementsByTagName('tfoot')[0];
       var yTable = document.createElement('tr');
       yTable.id = "convertId";
       yTable.innerHTML = "<th>"+currencyName+"**</th><td><strong><span class='woocommerce-Price-amount amount'><span class='woocommerce-Price-currencySymbol'>"+currencySymbol+"</span>"+finalNum+"</span></strong></td>";
       if(document.getElementById('convertId')) {
        document.getElementById('convertId').innerHTML = "<th>"+currencyName+"**</th><td><strong><span class='woocommerce-Price-amount amount'><span class='woocommerce-Price-currencySymbol'>"+currencySymbol+"</span>"+finalNum+"</span></strong></td>";
        } else {
         xTable.appendChild(yTable);
       }
     }else {
       console.log('Country not found for currency conversion');
     }
   }
 }

  function currencyConverter(){
   if(!checkLoaded){
     if (!countryRequest) {
       countryRequest = getRequestCountry();
     }
   } else {
     convertValues();
   }
  }
  // Check if country selected is South-Africa and add checking every 1 second
  function checkCountry() {
    if(countryVal !== "ZA"){
      var numIndex = country.selectedIndex;
      alert("Country selected: " + document.getElementsByTagName("option")[numIndex].innerHTML + "\n Please pay with your Credit/Debit card only");
      setTimeout(currencyConverter, 1000);
    }
    if(timer){
      timer = setInterval(repeatCountry, 1000);
    }
  }
  // Check for changes in country selected every 1 second and updates the new value.
  function repeatCountry(){
    countryCheckVal = document.getElementById("billing_country").value;
    if (countryCheckVal !== countryVal){
      countryVal = countryCheckVal; // Update to new country selected value
      timer = false;
      checkCountry();
    }
  }
  //Start checking if country is not South-Africa
  setTimeout(checkCountry, 2000);

  </script>
  <?php
}
