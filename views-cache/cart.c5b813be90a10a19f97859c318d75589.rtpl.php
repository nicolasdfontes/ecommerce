<?php if(!class_exists('Rain\Tpl')){exit;}?>	<div class="product-big-title-area">
	    <div class="container">
	        <div class="row">
	            <div class="col-md-12">
	                <div class="product-bit-title text-center">
	                    <h2>Carrinho de Compras</h2>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
	<div class="single-product-area">
	    <div class="zigzag-bottom"></div>
	    <div class="container">
	        <div class="row">
	            <div class="col-md-12">
	                <div class="product-content-right">
	                    <div class="woocommerce">
	                        <form action="/checkout">
	                            <div class="alert alert-danger" role="alert">Erro!</div>
	                            <table cellspacing="0" class="shop_table cart">
	                                <thead>
	                                    <tr>
	                                        <th class="product-remove">&nbsp;</th>
	                                        <th class="product-thumbnail">&nbsp;</th>
	                                        <th class="product-name">&nbsp;</th>
	                                        <th class="product-price">&nbsp;</th>
	                                        <th class="product-quantity">Qtd</th>
	                                        <th class="product-subtotal">Total</th>
	                                    </tr>
	                                </thead>
	                                <tbody>
	                                    <tr class="cart_item">
	                                        <td class="product-remove"><a title="Remove this item" class="remove" href="#">X</a></td>
	                                        <td class="product-thumbnail"><a href="/res/site/img/product-1.jpg"><img width="145" height="145" alt="produto" class="shop_thumbnail" src="/res/site/img/product-1.jpg"></a></td>
	                                        <td class="product-name"><a href="#">Produto</a></td>
	                                        <td class="product-price"><span class="amount">R$100,00</span></td>
	                                        <td class="product-quantity">
	                                            <div class="quantity buttons_added">
	                                                <input type="button" class="minus" value="-" onclick="window.location.href='#'">
	                                                <input type="number" size="2" class="input-text qty text" title="Qty" value="1" min="1" step="1">
	                                                <input type="button" class="plus" value="+" onclick="window.location.href='#'">
	                                            </div>
	                                        </td>
	                                        <td class="product-subtotal"><span class="amount">R$100,00</span></td>
	                                    </tr>
	                                </tbody>
	                            </table>
	                            <div class="cart-collaterals">
	                                <div class="cross-sells">
	                                    <h2>Cálculo do Frete</h2>
	                                    <div class="coupon">
	                                        <label for="cep">CEP:</label>
	                                        <input type="text" placeholder="00000-000" value="" id="cep" class="input-text" name="zipcode">
	                                        <input type="submit" formmethod="post" formaction="/cart/freight" value="CALCULAR" class="button">
	                                    </div>
	                                </div>
	                                <div class="cart_totals">
	                                    <h2>Resumo da Compra</h2>
	                                    <table cellspacing="0">
	                                        <tbody>
	                                            <tr class="cart-subtotal"><th>Subtotal</th> <td><span class="amount">R$100,00</span></td></tr>
	                                            <tr class="shipping"><th>Frete</th> <td>R$5,00 <small>prazo de x dias</small></td></tr>
	                                            <tr class="order-total"><th>Total</th> <td><strong><span class="amount">R$105,00</span></strong></td></tr>
	                                        </tbody>
	                                    </table>
	                                </div>
	                            </div>
	                            <div class="pull-right"><input type="submit" value="Finalizar Compra" name="proceed" class="checkout-button button alt wc-forward"></div>
	                        </form>
	                    </div>                        
	                </div>                    
	            </div>
	        </div>
	    </div>
	</div>