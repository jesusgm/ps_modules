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
                                            {if (!$PS_CATALOG_MODE && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                    							<div class="content_price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                    									<span itemprop="price" class="price product-price">
                    										{hook h="displayProductPriceBlock" product=$product type="before_price"}
                    										{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                    									</span>
                    									<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
                    									{if $product.price_without_reduction > 0 && isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
                    										{hook h="displayProductPriceBlock" product=$product type="old_price"}
                    										<span class="old-price product-price">
                    											{displayWtPrice p=$product.price_without_reduction}
                    										</span>
                    										{if $product.specific_prices.reduction_type == 'percentage'}
                    											<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
                    										{/if}
                    									{/if}
                    									{if $PS_STOCK_MANAGEMENT && isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
                    										<span class="unvisible">
                    											{if ($product.allow_oosp || $product.quantity > 0)}
                    													<link itemprop="availability" href="https://schema.org/InStock" />{if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later}{else}{l s='In Stock'}{/if}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now}{else}{l s='In Stock'}{/if}{/if}
                    											{elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
                    													<link itemprop="availability" href="https://schema.org/LimitedAvailability" />{l s='Product available with different options'}

                    											{else}
                    													<link itemprop="availability" href="https://schema.org/OutOfStock" />{l s='Out of stock'}
                    											{/if}
                    										</span>
                    									{/if}
                    									{hook h="displayProductPriceBlock" product=$product type="price"}
                    									{hook h="displayProductPriceBlock" product=$product type="unit_price"}
                    							</div>
                    						{/if}
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
