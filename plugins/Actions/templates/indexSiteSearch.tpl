<div id='leftcolumn'>
    <h2>{'Actions_WidgetSearchKeywords'|translate}</h2>
    {'Actions-getSiteSearchKeywords'|renderDataTable}

    <h2>{'Actions_WidgetSearchNoResultKeywords'|translate}</h2>
    {'Actions-getSiteSearchNoResultKeywords'|renderDataTable}

    {if isset($categories)}
        <h2>{'Actions_WidgetSearchCategories'|translate}</h2>
        {'Actions-getSiteSearchCategories'|renderDataTable}
    {/if}

</div>

<div id='rightcolumn'>
    <h2>{'Actions_WidgetPageUrlsFollowingSearch'|translate}</h2>
    {'Actions-getPageUrlsFollowingSiteSearch'|renderDataTable}

</div>
