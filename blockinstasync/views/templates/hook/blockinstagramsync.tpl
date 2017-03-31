{if !empty($images)}
    {$images|print_r}
{else}
    <p class="alert alert-warning">{l s='No se han encontrado imágenes para mostrar' mod='blockinstasync'}</p>
{/if}
