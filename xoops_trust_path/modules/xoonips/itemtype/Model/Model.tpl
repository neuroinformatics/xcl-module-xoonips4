<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_model" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(creators,creators_name)) != 0)}>
        <{if is_array($item->get(creators,creators_name)) }>
        <{foreach from=$item->get(creators,creators_name) key="key" item="creators_name" }>
        <{if $key != 0}>,<{/if}>
        <{$creators_name|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(creators,creators_name)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
