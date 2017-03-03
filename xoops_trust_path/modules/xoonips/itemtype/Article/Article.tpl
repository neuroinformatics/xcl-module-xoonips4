<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_article" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()}>"><{$item->get(article_title,essential_title)|xoops_escape}></a><br>
        <{if (count($item->get(article_authors,name)) != 0)}>
        <{if is_array($item->get(article_authors,name)) }>
        <{foreach from=$item->get(article_authors,name) key="key" item="name" }>
        <{if $key != 0}>,<{/if}>
        <{$name|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(article_authors,name)|xoops_escape}>
        <{/if}>
        <{/if}>
        <br>
        <{if ($item->get(jtitle,name)|count_characters != 0)}>
        <{if is_array($item->get(jtitle,name)) }>
        <{foreach from=$item->get(jtitle,name) key="key" item="name" }>
        <{$name|xoops_escape}>.
        <{/foreach}>
        <{else}>
        <{$item->get(jtitle,name)|xoops_escape}>.
        <{/if}>
        <{/if}>
        <{if ($item->get(jtitle,jtitle_volume)|count_characters != 0)}>
        <{if is_array($item->get(jtitle,jtitle_volume)) }>
        <{foreach from=$item->get(jtitle,jtitle_volume) key="key" item="jtitle_volume" }>
        <{$jtitle_volume|xoops_escape}>,
        <{/foreach}>
        <{else}>
        <{$item->get(jtitle,jtitle_volume)|xoops_escape}>,
        <{/if}>
        <{/if}>
        <{if (count($item->get(jtitle,jtitle_issue)) != 0)}>
        <{if is_array($item->get(jtitle,jtitle_issue)) }>
        <{foreach from=$item->get(jtitle,jtitle_issue) key="key" item="jtitle_issue" }>
        <{$jtitle_issue|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(jtitle,jtitle_issue)|xoops_escape}>
        <{/if}>
        <{/if}>
        <{if (count($item->get(jtitle,jtitle_year)) != 0)}>
        <{if is_array($item->get(jtitle,jtitle_year)) }>
        (
        <{foreach from=$item->get(jtitle,jtitle_year) key="key" item="jtitle_year" }>
        <{$jtitle_year|xoops_escape}>
        <{/foreach}>
        <{else}>
        (<{$item->get(jtitle,jtitle_year)|xoops_escape}>
        <{/if}>
        <{/if}>
        <{if (count($item->get(jtitle,jtitle_month)) != 0)}>
        <{if is_array($item->get(jtitle,jtitle_month)) }>
        .
        <{foreach from=$item->get(jtitle,jtitle_month) key="key" item="jtitle_month" }>
        <{$jtitle_month|xoops_escape}>
        <{/foreach}>
        <{else}>
        .<{$item->get(jtitle,jtitle_month)|xoops_escape}>
        <{/if}>
        <{/if}>
        <{if (count($item->get(jtitle,jtitle_year)) != 0) && (count($item->get(jtitle,jtitle_month)) != 0) }>)<{/if}>
        <{if (count($item->get(jtitle,jtitle_spage)) != 0) || (count($item->get(jtitle,jtitle_epage)) != 0)}>,p.<{/if}>
        <{if (count($item->get(jtitle,jtitle_spage)) != 0) }>
        <{if is_array($item->get(jtitle,jtitle_spage)) }>
        <{foreach from=$item->get(jtitle,jtitle_spage) key="key" item="jtitle_spage" }>
        <{$jtitle_spage|xoops_escape}>
        <{/foreach}>
        -  
        <{else}>
        <{$item->get(jtitle,jtitle_spage)|xoops_escape}>-
        <{/if}>
        <{/if}>
        <{if ($item->get(jtitle,jtitle_epage)|count_characters != 0)}>
        <{if is_array($item->get(jtitle,jtitle_epage)) }>
        <{foreach from=$item->get(jtitle,jtitle_epage) key="key" item="jtitle_epage" }>
        <{$jtitle_epage|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(jtitle,jtitle_epage)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
