<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_tool" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(tool_type,tool_type)) != 0)}>
        <{if is_array($item->get(tool_type,tool_type)) }>
        <{foreach from=$item->get(tool_type,tool_type) key="key" item="tool_type" }>
        <{$tool_type|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(tool_type,tool_type)|xoops_escape}>
        <{/if}>
        <{/if}>
        <br />
        <{if (count($item->get(developers,developer)) != 0)}>
        <{if is_array($item->get(developers,developer)) }>
        <{foreach from=$item->get(developers,developer) key="key" item="developer" }>
        <{if $key != 0}>,<{/if}>
        <{$developer|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(developers,developer)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
