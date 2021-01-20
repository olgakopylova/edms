<tr>
    <td>{#id}</td>
    <td><a {if btn}href="/edms/{#id_user}/assignment/{#id}"{/if}>{#user}</a></td>
    <td>{#start}</td>
    <td>{#end}</td>
    <td>{#type}</td>
    <td>{#status}</td>
    <td>{if btn}<button class="btn btn-sm btn-outline-danger  float-right" title="Удалить" onclick='Delete({#id})'><i class="fa fa-times" aria-hidden="true"></i></button>{/if}</td>
</tr>