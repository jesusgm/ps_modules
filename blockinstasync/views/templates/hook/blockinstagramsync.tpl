{if !empty($images)}
    <!-- <pre>
        {$images|print_r}
    </pre> -->
    <div class="grid">
        {foreach from=$images item=image}
            <div class="grid-item">
                <img data-toggle="modal"
                     data-target="#popup{$image.instagram_id}"
                     class="instapic"
                     src="{$img_base_path}{$image.instagram_id}/low_resolution.jpg"
                     alt="{$image.location_name}" />
            </div>
        {/foreach}
    </div>
    <!-- Modals -->
    {foreach from=$images item=image}
        <div id="popup{$image.instagram_id}" class="modal fade" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{l s='Buy this look' mod="blockinstasync"}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="popup">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <div class="instaslider owl-theme">
                                        <div class="item">
                                            <img class="img-responsive" src="{$img_base_path}{$image.instagram_id}/standard_resolution.jpg" alt="{$image.location_name}" />
                                        </div>
                                        {if !empty($image.products)}
                                            {foreach from=$image.products item="product"}
                                            <div class="item">
                                                <img itemprop="image"
                                                     class="img-responsive"
                                                    src="{$link->getImageLink($product.link_rewrite, $product.cover, 'large_default')|escape:'html':'UTF-8'}"
                                                    title="{$product.name}"
                                                    alt="{$product.description_short}"/>
                                            </div>
                                            {/foreach}
                                        {/if}
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    {if !empty($image.products)}
                                        {foreach from=$image.products item="product"}
                                        <div class="product">
                                            <p>{$product.name}</p>
                                            <img itemprop="image"
                                                 class="img-responsive"
                                                src="{$link->getImageLink($product.link_rewrite, $product.cover, 'cart_default')|escape:'html':'UTF-8'}"
                                                title="{$product.name}"
                                                alt="{$product.description_short}"/>
                                            <div class="content_price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                                <span itemprop="price" class="price product-price">
                                                    {convertPrice price=$product.price}
                                                </span>
                                                <meta itemprop="priceCurrency" content="{$currency->iso_code}" />
                                            </div>
                                            <a class="button ajax_add_to_cart_button btn btn-default"
                                                href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}"
                                                rel="nofollow"
                                                title="{l s='Add to cart'}"
                                                data-id-product-attribute="{$product.id_product_attribute|intval}"
                                                data-id-product="{$product.id_product|intval}"
                                                data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
								                    <span>{l s='Add to cart'}</span>
            								</a>
                                        </div>
                                        {/foreach}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div> -->
                </div>
            </div>
        </div>
    {/foreach}
{else}
    <p class="alert alert-warning">
        {l s='No se han encontrado imágenes para mostrar' mod='blockinstasync'}
    </p>
{/if}
