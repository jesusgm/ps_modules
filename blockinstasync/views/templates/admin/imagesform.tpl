<style type="text/css">
    #selectall,
    #unselectall{
        cursor: pointer;
    }
    #unselectall{
        display: none;
    }
</style>
<div class="instagramimages panel">
    <div class="panel-heading">{l s='List of images' mod='blockinstasync'}</div>
        <div class="panel">
            <div class="col-xs-2 text-center">
                {l s='Thumbnail' mod='blockinstasync'}
            </div>
            <div class="col-xs-4 text-center">
                {l s='Caption' mod='blockinstasync'}
            </div>
            <div class="col-xs-4 text-center">
                {l s='Productos' mod='blockinstasync'}
            </div>
            <div class="col-xs-2 text-center">
                <a id="selectall" class="pull-right">
                    {l s='Marcar todas' mod='blockinstasync'}
                </a>
                <a id="unselectall" class="pull-right">
                    {l s='Desmarcar todas' mod='blockinstasync'}
                </a>
            </div>
        </div>
        <form id="imagesform" method="post" action="" >
            {foreach from=$imagenes item=imagen}
            <div class="formimageline panel">
                <div class="row">
                    <div class="col-xs-2">
                        <input type="hidden" name="id_image[]" value="{$imagen.instagramsync_images_id}"/>
                        <img src="{$img_base_path}{$imagen.instagram_id}/thumbnail.jpg" />
                    </div>
                    <div class="col-xs-4">
                        <p>{$imagen.caption}</p>
                        <br/>
                        <p>{$imagen.likes} {l s='Likes' mod='blockinstasync'}</p>
                    </div>
                    <div class="col-xs-5">
                        {assign var="selected_prods" value=InstagramImage::getImageProducts($imagen.instagramsync_images_id)}
                        <select name="product_ids[{$imagen.instagramsync_images_id}][]" multiple="multiple">
                            <option value=""> - </option>
                            {foreach from=$products item=p}
                                <option value="{$p.id_product}" {if in_array($p.id_product, $selected_prods)}selected="selected"{/if} > {$p.reference} - {$p.name} </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-xs-1">
                        <input class="checkbox"
                                type="checkbox"
                                name="active[{$imagen.instagramsync_images_id}]"
                                id="active-{$imagen.instagramsync_images_id}" {if $imagen.shown} checked="checked" {/if} value="{$imagen.shown}" />
                        <label for="active-{$imagen.instagramsync_images_id}">
                            {l s='Mostrar' mod='blockinstasync'}
                        </label>
                    </div>
                </div>
            </div>
            {/foreach}
            <button type="submit" value="1" id="configuration_form_submit_btn" name="submitblockinstasync_associations" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>
                {l s='Guardar' mod='blockinstasync'}
            </button>
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $(document).on('click', 'a#selectall', function(e){
        e.preventDefault();
        $(this).hide();
        $('#unselectall').show();
        $('#imagesform .checkbox').each(function(){
            console.log($(this));
            $(this).prop('checked', true);
        })
    });
    $(document).on('click', 'a#unselectall', function(e){
        e.preventDefault();
        $(this).hide();
        $('#selectall').show();
        $('#imagesform .checkbox').each(function(){
            $(this).prop('checked', false);
        })
    });
});
</script>
