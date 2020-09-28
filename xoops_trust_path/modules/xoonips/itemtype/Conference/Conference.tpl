<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_conference" />
    </td>
    <td>
        <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(ctitle,ctitle)|xoops_escape}></a><br />
        <{$item->get(ctitle, ctitle)|xoops_escape}>
        <{if (count($item->get(conference_presentation_type,conference_presentation_type)) != 0)}>
        <{if is_array($item->get(conference_presentation_type,conference_presentation_type)) }>
        (
        <{foreach from=$item->get(conference_presentation_type,conference_presentation_type) key="key" item="conference_presentation_type" }>
        <{$conference_presentation_type|xoops_escape}>
        <{/foreach}>
        )<br />
        <{else}>
        (<{$item->get(conference_presentation_type,conference_presentation_type)|xoops_escape}>)<br />
        <{/if}>
        <{/if}>
        <{if (count($item->get(presenters,presenter)) != 0)}>
        <{if is_array($item->get(presenters,presenter)) }>
        <{foreach from=$item->get(presenters,presenter) key="key" item="presenter" }>
        <{if $key != 0}>,<{/if}>
        <{$presenter|xoops_escape}>
        <{/foreach}>
        <{else}>
        <{$item->get(presenters,presenter)|xoops_escape}>
        <{/if}>
        <{/if}>
    </td>
    <td width="65">
    <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
