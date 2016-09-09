{extends designs/site.tpl}

{block title}Teacher Dashboard Connector &mdash; {$dwoo.parent}{/block}

{block content}
    <h1>Teacher Dashboard Connector</h1>

    <ul>
        <li><a href="{$connectorBaseUrl}/students.csv" class="button">Download Students Spreadsheet</a></li>
        <li><a href="{$connectorBaseUrl}/classes.csv" class="button">Download Classes Spreadsheet</a></li>
    </ul>
{/block}