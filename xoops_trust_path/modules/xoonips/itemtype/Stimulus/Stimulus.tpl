<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_stimulus" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(stimulus_type,stimulus_type)) != 0)}>
        <{if is_array($item->get(stimulus_type,stimulus_type)) }>
        <{foreach from=$item->get(stimulus_type,stimulus_type) key="key" item="stimulus_type" }>
        <{$stimulus_type|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(stimulus_type,stimulus_type)}>
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
