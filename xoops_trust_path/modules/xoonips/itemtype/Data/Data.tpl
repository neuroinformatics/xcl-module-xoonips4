<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_data" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(data_type,data_type)) != 0)}>
        <{if is_array($item->get(data_type,data_type)) }>
        <{foreach from=$item->get(data_type,data_type) key="key" item="data_type" }>
        <{$data_type|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(data_type,data_type)|xoops_escape}>
        <{/if}>
        <{/if}>
        <br />
        <{if (count($item->get(experimenters,experimenters_name)) != 0)}>
        <{if is_array($item->get(experimenters,experimenters_name)) }>
        <{foreach from=$item->get(experimenters,experimenters_name) key="key" item="experimenters_name" }>
        <{if $key != 0}>,<{/if}>
        <{$experimenters_name|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(experimenters,experimenters_name)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
    <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
