{postEvent name="template_headerVisitsFrequency"}

<a name="evolutionGraph" graphId="VisitFrequencygetEvolutionGraph"></a>
<h2>{'VisitFrequency_ColumnReturningVisits'|translate}</h2>
{'VisitFrequency-getEvolutionGraph'|renderDataTable}
<br />

{include file="VisitFrequency/templates/sparklines.tpl"}
	
{postEvent name="template_footerVisitsFrequency"}
