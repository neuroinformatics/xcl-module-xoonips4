<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_memo" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if count(($item->get(link,item_link)) != 0)}><{if is_array($item->get(link,item_link)) }><{foreach from=$item->get(link,item_link) key="key" item="item_link" }>Link to <a href="<{$item_link|xoops_escape}>"><{$item_link|xoops_escape}></a>
　　　　<{/foreach}>
        <{else}>Link to <a href="<{$item->get(link,item_link)}>"><{$item->get(link,item_link)}></a>
        <{/if}>
　　　　<{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
