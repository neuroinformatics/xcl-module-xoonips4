<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_book" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(authors,name)) != 0)}>
        <{if is_array($item->get(authors,name)) }>
        <{foreach from=$item->get(authors,name) key="key" item="name" }>
        <{if $key != 0}>,<{/if}>
        <{$name|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(authors,name)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
