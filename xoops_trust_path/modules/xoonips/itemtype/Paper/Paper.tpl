<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_paper" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(essential_authors,essential_name)) != 0)}>
        <{if is_array($item->get(essential_authors,essential_name)) }>
        <{foreach from=$item->get(essential_authors,essential_name) key="key" item="essential_name" }>
        <{if $key != 0}>,<{/if}>
        <{$essential_name|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(essential_authors,essential_name)|xoops_escape}>
        <{/if}>
        <{/if}>
        <br />
        <{if (count($item->get(jtitle_name,jtitle_name)) != 0)}>
        <{if is_array($item->get(jtitle_name,jtitle_name)) }>
        <{foreach from=$item->get(jtitle_name,jtitle_name) key="key" item="jtitle_name" }>
        <{$jtitle_name|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(jtitle_name,jtitle_name)|xoops_escape}>
        <{/if}>
        <{/if}>
        <{if (count($item->get(publication_year,publication_year)) != 0)}>
        <{if is_array($item->get(publication_year,publication_year)) }>
        <{foreach from=$item->get(publication_year,publication_year) key="key" item="publication_year" }>
        <{$publication_year|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(publication_year,publication_year)|xoops_escape}>
        <{/if}>
        <{/if}>
        <{if (count($item->get(paper_volume,jtitle_volume)) != 0)}>
        ;
        <{if is_array($item->get(paper_volume,jtitle_volume)) }>
        <{foreach from=$item->get(paper_volume,jtitle_volume) key="key" item="jtitle_volume" }>
        <{$jtitle_volume|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(paper_volume,jtitle_volume)|xoops_escape}> 
        <{/if}>
        <{/if}>
        <{if (count($item->get(paper_number,jtitle_issue)) != 0)}>
        (
        <{if is_array($item->get(paper_number,jtitle_issue)) }>
        <{foreach from=$item->get(paper_number,jtitle_issue) key="key" item="jtitle_issue" }>
        <{$jtitle_issue|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(paper_number,jtitle_issue)|xoops_escape}>
        <{/if}>
        ) 
        <{/if}>
        <{if (count($item->get(paper_page,jtitle_epage)) != 0)}>
        :
        <{if is_array($item->get(paper_page,jtitle_epage)) }>
        <{foreach from=$item->get(paper_page,jtitle_epage) key="key" item="jtitle_epage" }>
        <{$jtitle_epage|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(paper_page,jtitle_epage)|xoops_escape}>
        <{/if}>
        <{/if}>
        <{if (count($item->get(pubmedid,pubmedid)) != 0)}>
        [PMID: 
        <{if is_array($item->get(pubmedid,pubmedid)) }>
        <{foreach from=$item->get(pubmedid,pubmedid) key="key" item="pubmedid" }>
        <{$pubmedid|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(pubmedid,pubmedid)|xoops_escape}>
        <{/if}>
        ]
        <{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
