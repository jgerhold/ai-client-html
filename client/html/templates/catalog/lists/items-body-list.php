<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 */

$enc = $this->encoder();
$position = $this->get( 'itemPosition' );

$detailTarget = $this->config( 'client/html/catalog/detail/url/target' );
$detailController = $this->config( 'client/html/catalog/detail/url/controller', 'catalog' );
$detailAction = $this->config( 'client/html/catalog/detail/url/action', 'detail' );
$detailConfig = $this->config( 'client/html/catalog/detail/url/config', [] );

$basketTarget = $this->config( 'client/html/basket/standard/url/target' );
$basketController = $this->config( 'client/html/basket/standard/url/controller', 'basket' );
$basketAction = $this->config( 'client/html/basket/standard/url/action', 'index' );
$basketConfig = $this->config( 'client/html/basket/standard/url/config', [] );


?>
<?php $this->block()->start( 'catalog/lists/items' ); ?>
<div class="catalog-list-items">

	<ul class="list-items list"><!--

		<?php foreach( $this->get( 'listProductItems', [] ) as $id => $productItem ) : $firstImage = true; ?>
			<?php
				$conf = $productItem->getConfig(); $css = ( isset( $conf['css-class'] ) ? $conf['css-class'] : '' );
				$params = array( 'd_name' => $productItem->getName( 'url' ), 'd_prodid' => $id );
				if( $position !== null ) { $params['d_pos'] = $position++; }

				$url = $this->url( ($productItem->getTarget() ?: $detailTarget ), $detailController, $detailAction, $params, [], $detailConfig );
			?>

			--><li class="product <?= $enc->attr( $css ); ?>"
				data-reqstock="<?= (int) $this->config( 'client/html/basket/require-stock', true ); ?>"
				itemtype="http://schema.org/Product"
				itemscope="">


				<a class="media-list" href="<?= $url; ?>">
					<?php foreach( $productItem->getRefItems( 'media', 'default', 'default' ) as $mediaItem ) : ?>
						<?php $mediaUrl = $enc->attr( $this->content( $mediaItem->getPreview() ) ); ?>
						<?php if( $firstImage === true ) : $firstImage = false; ?>
							<noscript>
								<div class="media-item" style="background-image: url('<?= $mediaUrl; ?>')"
									itemtype="http://schema.org/ImageObject" itemscope="">
									<meta itemprop="contentUrl" content="<?= $mediaUrl; ?>" />
								</div>
							</noscript>
							<div class="media-item lazy-image" data-src="<?= $mediaUrl; ?>"></div>
						<?php else : ?>
							<div class="media-item" data-src="<?= $mediaUrl; ?>"></div>
						<?php endif; ?>
					<?php endforeach; ?>
				</a><!--


				--><a class="text-list" href="<?= $url; ?>">
					<h2 itemprop="name"><?= $enc->html( $productItem->getName(), $enc::TRUST ); ?></h2>
					<?php foreach( $productItem->getRefItems( 'text', 'short', 'default' ) as $textItem ) : ?>
						<div class="text-item" itemprop="description">
							<?= $enc->html( $textItem->getContent(), $enc::TRUST ); ?><br/>
						</div>
					<?php endforeach; ?>
				</a><!--


				--><div class="offer" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
					<div class="stock"
						data-prodid="<?= $enc->attr(
							implode( ' ', array_merge( array( $id ), array_keys( $productItem->getRefItems( 'product', 'default', 'default' ) ) ) )
						); ?>">
					</div>
					<div class="price-list price price-actual">
						<?= $this->partial(
							$this->config( 'client/html/common/partials/price', 'common/partials/price-default.php' ),
							array( 'prices' => $productItem->getRefItems( 'price', null, 'default' ) )
						); ?>
					</div>
				</div>


				<?php if( $this->config( 'client/html/catalog/lists/basket-add', false ) ) : ?>
					<form class="basket" method="POST"
						action="<?= $enc->attr( $this->url( $basketTarget, $basketController, $basketAction, [], [], $basketConfig ) ); ?>">
						<!-- catalog.lists.items.csrf -->
						<?= $this->csrf()->formfield(); ?>
						<!-- catalog.lists.items.csrf -->

						<?php if( $productItem->getType() === 'select' ) : ?>
							<div class="items-selection">
								<?= $this->partial(
									$this->config( 'client/html/common/partials/selection', 'common/partials/selection-default.php' ),
									array(
										'products' => $productItem->getRefItems( 'product', 'default', 'default' ),
										'attributeItems' => $this->get( 'itemsAttributeItems', [] ),
										'productItems' => $this->get( 'itemsProductItems', [] ),
										'mediaItems' => $this->get( 'itemsMediaItems', [] ),
									)
								); ?>
							</div>
						<?php endif; ?>

						<div class="items-attribute">
							<?= $this->partial(
								$this->config( 'client/html/common/partials/attribute', 'common/partials/attribute-default.php' ),
								array(
									'attributeItems' => $this->get( 'itemsAttributeItems', [] ),
									'attributeConfigItems' => $productItem->getRefItems( 'attribute', null, 'config' ),
									'attributeCustomItems' => $productItem->getRefItems( 'attribute', null, 'custom' ),
									'attributeHiddenItems' => $productItem->getRefItems( 'attribute', null, 'hidden' ),
								)
							); ?>
						</div>

						<div class="addbasket">
							<div class="group">
								<input type="hidden"
									name="<?= $enc->attr( $this->formparam( 'b_action' ) ); ?>"
									 value="add"
								/>
								<input type="hidden"
									name="<?= $enc->attr( $this->formparam( array( 'b_prod', 0, 'prodid' ) ) ); ?>"
									value="<?= $id; ?>"
								/>
								<input type="number"
									min="1" max="2147483647" maxlength="10"
									step="1" required="required" value="1"
									name="<?= $enc->attr( $this->formparam( array( 'b_prod', 0, 'quantity' ) ) ); ?>"
								/><!--
								--><button class="standardbutton btn-action" type="submit" value="">
									<?= $enc->html( $this->translate( 'client', 'Add to basket' ), $enc::TRUST ); ?>
								</button>
							</div>
						</div>

					</form>
				<?php endif; ?>


			</li><!--

		<?php endforeach; ?>
	--></ul>

</div>
<?php $this->block()->stop(); ?>
<?= $this->block()->get( 'catalog/lists/items' ); ?>
