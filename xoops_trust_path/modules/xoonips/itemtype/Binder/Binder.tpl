<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_binder" />
    </td>
    <td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
