<div id="instagramsync">
    {if !empty($images)}
        <!-- <pre>
            {$images|print_r}
        </pre> -->
        <div id="popupoverlay"></div>
        {foreach from=$images item=image}
            <div class="img-container">
                <img class="instapic" src="{$img_base_path}{$image.instagram_id}/thumbnail.jpg" />
                <div class="popup">
                    <div class="col-xs-6">
                        <div class="slidercontainer">
                            <div class="slider">
                                <div class="slider-item">
                                    <img src="{$img_base_path}{$image.instagram_id}/standard_resolution.jpg" />
                                </div>
                                {if isset($image.products) && !empty($image.products)}
                                    {foreach from=$image.products item=product}
                                        <div class="slider-item">
                                            <img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'home_default')|escape:'html':'UTF-8'}" />
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        {if isset($image.products) && !empty($image.products)}
                            {foreach from=$image.products item=product}
                                <div class="related-product">
                                    <p class="product-name">{$product.name}</p>
                                    <p class="product-price">{$product.price}</p>
                                    <a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product-attribute="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
    									<span>{l s='Add to cart'}</span>
    								</a>
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                    <div class="closepopup">
                        <a title="{l s='Close' mod='blockinstasync'}">
                            <span>
                                {l s='x' mod='blockinstasync'}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        {/foreach}
    {else}
        <p class="alert alert-warning">{l s='No se han encontrado imágenes para mostrar' mod='blockinstasync'}</p>
    {/if}
</div>
