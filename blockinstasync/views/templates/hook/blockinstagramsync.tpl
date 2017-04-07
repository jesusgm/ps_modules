{if !empty($images)}
    <!-- <pre>
        {$images|print_r}
    </pre> -->
    {foreach from=$images item=image}
        <img src="{$img_base_path}{$image.instagram_id}/thumbnail.jpg" />
    {/foreach}
{else}
    <p class="alert alert-warning">{l s='No se han encontrado imágenes para mostrar' mod='blockinstasync'}</p>
{/if}
