<table><tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
        <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_file" />
    </td>
    <td>
        <{if ($item->get(title,title)|count_characters != 0)}>
        <a href="<{$item->getItemUrl()}>"><{$item->get(title,title)|xoops_escape}></a><br />
        <{else}>
        <{foreach item=file from=$item->get(essential_files,essential_file)}>
        <{if $smarty.foreach.file.iteration > 1 }> . <{/if}>
        <a href="<{$item->getItemUrl()}>"><{$file.original_file_name}></a><br />
        <{/foreach}>
        <{/if}>
        (
        <{foreach item=contributor from=$item->get(contributors,contributor) name=contributor}>
        <{if !$smarty.foreach.contributor.first}> . <{/if}>
        <{$contributor.name|xoops_escape}>
        <{if !empty($contributor.name)}>(<{/if}>
        <{$contributor.uname|xoops_escape}>
        <{if !empty($contributor.name)}>)<{/if}>
        <{/foreach}>
        )<br />
        (
        <{foreach item=file from=$item->get(essential_files,essential_file)}>
        <{if $smarty.foreach.file.iteration > 1 }> . <{/if}>
        <{$file.mime_type}>
        <{/foreach}>
        )
    </td>
    <td width="65">
        <{if $item->isPending()}>(pending)<{/if}>
    </td>
</tr></table>
