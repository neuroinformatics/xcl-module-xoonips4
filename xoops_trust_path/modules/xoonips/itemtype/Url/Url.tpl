<div class="content">
<table>
  <tr>
    <td width="65" style="vertical-align:middle; text-align:center;">
      <img src="<{$item->getIconUrl()|xoops_escape}>" alt="icon_url" />
    </td>
    <td>
      <a href="<{$item->getItemUrl()|xoops_escape}>"><{$item->get(essential_titles,essential_title)|xoops_escape}></a><br />
       Link to
<{foreach item=url from=$item->get(essential_url, essential_url)}>
       <a href="<{$url|xoops_escape}>" target="_blank" onclick="return XoonipsLibrary.linkCountUp(<{$item->getItemId()|xoops_escape}>, 'xml', 'essential_url:essential_url');"><{$url|xoops_escape}></a>
<{/foreach}>
    </td>
    <td width="65">
      <{if $item->isPending()}>(pending)<{/if}>
    </td>
  </tr>
</table>
</div>
