<div id="leftcolumn">
{postEvent name="template_leftColumnUserCountry"}

<h2>{'UserCountry_Continent'|translate}</h2>
{'UserCountry-getContinent'|renderDataTable}

<div class="sparkline">
{sparkline src=$urlSparklineCountries}
{'UserCountry_DistinctCountries'|translate:"<strong>$numberDistinctCountries</strong>"}
</div>

</div>

<div id="rightcolumn">

<h2>{'UserCountry_Country'|translate}</h2>
{'UserCountry-getCountry'|renderDataTable}

<h2>{'UserCountry_Region'|translate}</h2>
{'UserCountry-getRegion'|renderDataTable}

<h2>{'UserCountry_City'|translate}</h2>
{'UserCountry-getCity'|renderDataTable}

</div>

{postEvent name="template_footerUserCountry"}
