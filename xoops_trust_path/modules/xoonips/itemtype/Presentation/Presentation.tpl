<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_presentation" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
        <{if (count($item->get(presentation_type,presentation_type)) != 0)}>
        <{if is_array($item->get(presentation_type,presentation_type)) }>
        <{foreach from=$item->get(presentation_type,presentation_type) key="key" item="presentation_type" }>
        <{$presentation_type|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(presentation_type,presentation_type)|xoops_escape}>
        <{/if}>
        <{/if}>
　　　　<br />
        <{if (count($item->get(presentation_creators,creators_name)) != 0)}>
        <{if is_array($item->get(presentation_creators,creators_name)) }>
        <{foreach from=$item->get(presentation_creators,creators_name) key="key" item="creators_name" }>
        <{if $key != 0}>,<{/if}>
        <{$creators_name|xoops_escape}>
        <{/foreach}>
        <{else}>
　　　　<{$item->get(presentation_creators,creators_name)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
