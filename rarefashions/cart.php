<?php 
extract($_REQUEST);
include 'head/jackus.php'; 
include 'shopfunction.php'; 
include '___class__home.inc';
//print_r($_SESSION); exit();
//echo $sid;

$carthero_text = "Your cart is currently empty!"-;	
$cartdescription = "Please add items that you want to buy";
if($update_cart == 'update_cart'){
	
	foreach ($hidden_cart_ID as $key => $val) {
		
			$count = count($hidden_cart_ID);
			//echo $count.'<br>';
			$selected_CARTID = $_POST['hidden_cart_ID'][$key];

			$selected_PRDTID = $_POST['hidden_PRDT_ID'][$key];
			
			$selected_PRDTQTY = $_POST['quantity'][$key];

			if($selected_PRDTID !='' && $selected_CARTID !=''){
				//echo"select `item_tax`, `item_tax_type`, `pd_id`, `variant_id`, `od_colorid`, `od_size_id`, `od_qty`, `od_price`, `item_tax1`, `item_tax2` from js_shop_order_item where user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'";
			$check_selecteditem = sqlQUERY_LABEL("select `item_tax`, `item_tax_type`, `pd_id`, `variant_id`, `od_colorid`, `od_size_id`, `od_qty`, `od_price`, `item_tax1`, `item_tax2` from js_shop_order_item where user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'") or die(sqlERROR_LABEL());

				while($collect_selecteditem = sqlFETCHARRAY_LABEL($check_selecteditem)) {

					$quantity = $collect_selecteditem['od_qty'];
					$variant_id = $collect_selecteditem['variant_id'];
					$total_price = $collect_selecteditem['od_price'];
					$item_tax1 = $collect_selecteditem['item_tax1'];
					$item_tax2 = $collect_selecteditem['item_tax2'];
					$item_tax = $collect_selecteditem['item_tax'];
					$item_tax_type = $collect_selecteditem['item_tax_type'];
					if($quantity > 1){
						$old_total_price = (($item_tax1 + $item_tax2 + $total_price)/$quantity);
					} else {
						$old_total_price = $item_tax1 + $item_tax2 + $total_price;
					}
					$newtotal_price = $old_total_price * $selected_PRDTQTY;
					// echo $quantity;
					// echo "<br>";
					// echo $qty;
					// echo "<br>";
					// echo $old_total_price;
					// echo "<br>";
					// echo $newtotal_price;
					// echo "<br>";

				$PRDT_CODE = getPRDT_CODE($selected_PRDTID, $variant_id, 'get_prdt_code');
				//echo $PRDT_CODE.'<br>'; 
				///CHECK PRODUCT ESTORE STOCK VIA API
					$list_producttype_data = sqlQUERY_LABEL("select `productopeningstock`,`productavailablestock` from js_product where productsku = '$PRDT_CODE'");
					$count_producttype_list = sqlNUMOFROW_LABEL($list_producttype_data);
					if($count_producttype_list > 0){
						//echo 'A<br>';
						while($get_product_data = sqlFETCHARRAY_LABEL($list_producttype_data)) {
							$prdt_opening_qty = $get_product_data['productopeningstock'];
							$prdt_available_qty = $get_product_data['productavailablestock'];
						}
					} else {
						//echo 'B<br>';
					//	echo "select `variant_available_stock`,`variant_opening_stock` from js_productvariants where variant_code = '$PRDT_CODE'"; exit();
						$list_producttype_data_variant = sqlQUERY_LABEL("select `variant_available_stock`,`variant_opening_stock` from js_productvariants where variant_code = '$PRDT_CODE' and deleted='0'");
						//$count_producttype_list = sqlNUMOFROW_LABEL($list_producttype_data_variant);
						while($get_product_data_variant = sqlFETCHARRAY_LABEL($list_producttype_data_variant)) {
							$prdt_opening_qty = $get_product_data_variant['variant_available_stock'];
							$prdt_available_qty = $get_product_data_variant['variant_opening_stock'];
						}
					}
					if($item_tax_type == 'Y') {
						$taxsplit_price = ($newtotal_price * ($item_tax/100))/2;
						$newtotal_price = ($newtotal_price - ($taxsplit_price * 2));
					} else {
						$taxsplit_price = (($newtotal_price * ($item_tax/100))/2);
						$newtotal_price = $newtotal_price;
					}
				
				}
				//echo "<br />";
				//echo "UPDATE `js_shop_order_item` SET `od_price` = '$newtotal_price', `od_qty`='$selected_PRDTQTY', `item_tax1`='$taxsplit_price', `item_tax2`='$taxsplit_price' WHERE createdby = '$logged_user_id' and user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'<br />";
				//exit();
				if($selected_PRDTQTY <= $prdt_available_qty){
					//echo "UPDATE `js_shop_order_item` SET `od_price` = '$newtotal_price', `od_qty`='$selected_PRDTQTY', `item_tax1`='$taxsplit_price', `item_tax2`='$taxsplit_price' WHERE createdby = '$logged_user_id' and user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'";
					sqlQUERY_LABEL("UPDATE `js_shop_order_item` SET `od_price` = '$newtotal_price', `od_qty`='$selected_PRDTQTY', `item_tax1`='$taxsplit_price', `item_tax2`='$taxsplit_price' WHERE createdby = '$logged_user_id' and user_id = '$logged_user_id' and pd_id = '$selected_PRDTID' and cart_id = '$selected_CARTID'") or die(sqlERROR_LABEL());
					$message = "Cart updated...!!!";
				} else {
					//echo $selected_PRDTID;
					if($variant_id !='0'){
						$get_variant_name = ' ('.getVARIANT_CODE($variant_id,'variant_name_from_variant_ID').')';
					} else {
						$get_variant_name = '';
					}
					$get_prdt_name = getPRDT_CODE($selected_PRDTID,'','get_prdt_name');
					$err .= "Stock not availabe for ".$get_prdt_name.$get_variant_name.'<br />';
				}
			}

		}  //end of 1st for loop
		//exit();
		if(empty($err)) {
		?>
		<script type="text/javascript">window.location = 'cart.php?msg=cart_updated' </script>
		<?php
		} else {
		?>
		<script type="text/javascript">window.location = 'cart.php?errormsg=<?php echo $err; ?>' </script>
		<?php
		}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<?php include '__styles.php'; ?>
</head>

<body>
    <!--[if lte IE 9]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->
	<div class="page-wrapper">
    <?php 
		
		//list of module view templates
		$loadFUNCTIONS = array(
		'__header'
		);
		
		echo $homepage_propertyclass->loadPAGE($loadFUNCTIONS); 
	
	?> 
        <main class="main">
        	<div class="page-header text-center" style="background-image: url('assets/images/page-header-bg.jpg')">
        		<div class="container">
        			<h1 class="page-title">Shopping Cart<span>Shop</span></h1>
        		</div><!-- End .container -->
        	</div><!-- End .page-header -->
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
                    </ol>
                </div><!-- End .container -->
            </nav><!-- End .breadcrumb-nav -->

            <div class="page-content">
			
			<?php
			if(!empty($logged_user_id)){
				$cart_query= sqlQUERY_LABEL("SELECT * FROM `js_shop_order_item` WHERE user_id ='$logged_user_id' and deleted='0' and `status` ='1' ORDER BY pd_id ASC")or die(sqlERROR_LABEL());
				$order_id =  getSINGLEDBVALUE('od_id', " user_id ='$logged_user_id' and `deleted`='0'  and `status` ='1'", 'js_shop_order_item', 'label');
				UPDATE_OFFERS($order_id,$logged_user_id);
 				
			}else{
				$cart_query= sqlQUERY_LABEL("SELECT * FROM `js_shop_order_item` WHERE  od_session = '$sid' and user_id =''and deleted='0' and `status` ='1' ORDER BY pd_id ASC") or die(sqlERROR_LABEL());
				$order_id =  getSINGLEDBVALUE('od_id', "od_session = '$sid' and `deleted`='0' and `status` ='1'", 'js_shop_order_item', 'label');
				UPDATE_OFFERS($order_id,$sid);
			}
//echo $order_id;exit();
         //UPDATE_OFFERS($order_id);
 //include('offer_update_page.php');	
					 
			$count_row = sqlNUMOFROW_LABEL($cart_query);
			  
			 if($count_row > 0){ ?>
            	<div class="cart">
	                <div class="container">
						<form method="post">
	                	<div class="row">
	                		<div class="col-lg-9">
	                			<table class="table table-cart table-mobile" id="table-cart">
									<thead>
										<tr>
											<th>Product</th>
											<th class="text-center">Price</th>
											<th class="text-center">Quantity</th>
											<th class="text-center" style="width: 12%;">Total</th>
											<th></th>
										</tr>
									</thead>
									<?php
										 while($cart_row = sqlFETCHARRAY_LABEL($cart_query)) 
									{  
										 
										$cart_id = $cart_row['cart_id'];
										$product_ID = $cart_row['pd_id'];
										$od_id = $cart_row['od_id'];
										$product_qty = $cart_row['od_qty'];
										$total_product_price = $cart_row['od_price'];
										$product_variant_id = $cart_row['variant_id'];
										$item_tax_type = $cart_row['item_tax_type'];
										$redeem_offer_id = $cart_row['redeem_offer_id'];
										$offers_id = $cart_row['offer_id'];
										
										
										// $total_price = $product_qty * $product_price;
										$cart_product = sqlQUERY_LABEL("SELECT `producttitle`, `productsku`, `productcolor`,  `productcategory`, `productsellingprice` FROM `js_product` where productID='$product_ID' and deleted = '0' and status = '1'") or die("#1-Unable to get records:".mysqli_error());
										
										while($featured_product = sqlFETCHARRAY_LABEL($cart_product)){
										$product_title = $featured_product["producttitle"];
										$product_title = str_replace("\'","'",$product_title); //$row["producttitle"];
										$productsku = $featured_product["productsku"];
										$productcolor = $featured_product["productcolor"];
										$productcategory = $featured_product["productcategory"];
										
										$total_cart_price += ($product_qty * $product_price);
										
										$product_price = $featured_product["productsellingprice"];
										
										$variant_product_price = getVRIANTPRODUCTPRICE($product_ID, $product_variant_id);
										
										$total_price += $product_price + $variant_product_price;
											if($product_variant_id =='0'){
												$get_variant_tax_split1 = $group_limit = getSINGLEDBVALUE('variant_taxsplit1',"parentproduct='$product_ID' and variant_ID = '$product_variant_id'",'js_productvariants','label');
												$get_variant_tax_split2 = $group_limit = getSINGLEDBVALUE('variant_taxsplit2',"parentproduct='$product_ID' and variant_ID = '$product_variant_id'",'js_productvariants','label');
												$get_variant_tax_value = $group_limit = getSINGLEDBVALUE('variant_tax_value',"parentproduct='$product_ID' and variant_ID = '$product_variant_id'",'js_productvariants','label');
												$total_tax_amount = $get_variant_tax_split1 + $get_variant_tax_split2;
												$tot_taxt_amt_for_od_qty = ($total_tax_amount * $product_qty);
												//echo $tot_taxt_amt_for_od_qty;exit();
											} else if($item_tax_type == 'Y') {
												$item_tax1 = $cart_row['item_tax1'];
												$item_tax2 = $cart_row['item_tax2'];
												$tot_taxt_amt_for_od_qty = $item_tax1 + $item_tax2;
											//	echo $tot_taxt_amt_for_od_qty;exit();
											}
										}
										$od_discount_amount = number_format(getSINGLEDBVALUE('od_discount_amount',"od_id='$od_id'",'js_shop_order','label'),2);
										$discount_added = getSINGLEDBVALUE('od_discount_type',"od_id='$od_id'",'js_shop_order','label');
										
										$discount_added = getSINGLEDBVALUE('od_discount_type',"od_id='$od_id'",'js_shop_order','label');
										
 											 
										 
 									?>
									
 									<tbody>

									<?php if($product_qty > 0){ ?>
										<tr id="delete-cart<?php echo $cart_id; ?>" rowspan="2"> 
										
											<td class="product-col">
												<div class="product">
													<figure class="product-media">
														<a href="#">
														<?php
											
														$productmedia_datas = sqlQUERY_LABEL("SELECT productmediagalleryID, productmediagalleryurl, productmediagallerytype FROM `js_productmediagallery` where productID='$product_ID' and deleted = '0' order by productmediagalleryurl ASC") or die("#2-Unable to get records:".mysqli_error());														
														  $count_productmedia_availablecount = sqlNUMOFROW_LABEL($productmedia_datas);
														  	while($row = sqlFETCHARRAY_LABEL($productmedia_datas)){
														  $productmediagalleryID = $row["productmediagalleryID"];
														  $productmediagalleryurl = $row["productmediagalleryurl"];
														  $productmediagallerytype = $row["productmediagallerytype"];
														  //image path
														  $media_path = "uploads/productmediagallery/$productmediagalleryurl";
																												
														} //end of count
														?>													
															<img src="<?php echo $media_path; ?>" alt="product side">
														</a>
													</figure>

													<h3 class="product-title">
													<?php if($redeem_offer_id !='0'){
														
														$offers_name =  getSINGLEDBVALUE('offers_name', " offers_id = '$redeem_offer_id' and `deleted` = '0' and status = '2'", 'js_offers', 'label');
														 
														?>
													<span class="text-warning"><i class="fa fa-gift" aria-hidden="true"></i>
														<?php echo $offers_name  ?> Applied</span></br>
													<?php } ?>
														<a href="product.php?token=<?php echo $product_ID.'-'.convertSEOURL($product_title).'-'.$productsku; ?>" title="<?php echo $product_title; ?>"><?php echo html_entity_decode($product_title); ?></a>
														<br><small><b>Category: </b>
														<?php 
														$categories = '';
														$cats = explode(",", $productcategory);
														foreach($cats as $cat) {
															$cat = trim($cat);
															$__category_title = getSINGLEDBVALUE('categoryname', "categoryID='$cat' and `deleted`='0' and status = '1'", 'js_category', 'label');
															$categories .= " <a target='_blank' href='category.php?token=".$cat."-".convertSEOURL($__category_title)."'>" . $__category_title . "</a>,";
														}
														echo substr($categories, 0, -1);
														?>
														</small>
														<br><?php if($product_variant_id !='0'){ ?><small><?php echo '<b>Size:</b> '.getVARIANT_CODE($product_variant_id,'variant_name_from_variant_ID'); ?></small><?php } ?>
														<br><small> <?php echo '<b>Product SKU Code: </b>'. $productsku; ?> </small>
														<?php 
															$color = '';
															$colors = explode(",", $productcolor);
															foreach($colors as $colors_cat) {
																$colors_cat = trim($colors_cat);
																$__color_title = getSINGLEDBVALUE('productfiltercolortitle', "productfiltercolorID='$colors_cat' and deleted='0' and status = '1'", 'js_productfiltercolor', 'label');
																$color .= $__color_title . ", ";
															}
															$color_with_comma = substr($color, 0, -2);
															if($color_with_comma != EMPTYFIELD) {
														?>
														<br><small> <?php echo '<b>Color: </b>'. $color_with_comma; ?> </small>
															<?php } ?>
														
													</h3><!-- End .product-title -->
												</div><!-- End .product -->
												
											</td>
											<?php 
											
											?>
											<td class="price-col" id="product-price" style="width:170px;"><span class="product-price text-dark justify-content-center" style="padding-top:15px;"> <?php if($product_variant_id == '0'){echo checkproductPRICE($product_ID);}else{echo checkproductPRICE1($product_ID,$product_variant_id);} ?></span></td>
											<td class="quantity-col pl-4" width="10%">
												<div class="cart-product-quantity" cart_id="<?php echo $cart_id; ?>" product_id="<?php echo $product_ID; ?>">
                                                    <input type="number" class="form-control" id="dispaly-qty<?php echo $cart_id; ?>" value="<?php echo $product_qty; ?>" min="1" max="10" step="1" data-decimals="0" name="quantity[]" required onclick = "cart_quantity_update(<?php echo $cart_id; ?>, <?php echo $product_ID; ?>)">
													
                                                </div>	
                                            </td>
											<?php if($redeem_offer=='2'){ ?>
											<td class="total-col" id="total-price"><span class="product-price justify-content-center" id="qty_total_update<?php echo $cart_id; ?>" style="padding-top:15px;"> <strike> <?php echo general_currency_symbol; ?> <?php echo number_format($total_product_price+$tot_taxt_amt_for_od_qty,2); ?> </strike> </span></td>
											<?php }else{ ?>
											<td class="total-col" id="total-price"><span class="product-price justify-content-center" id="qty_total_update<?php echo $cart_id; ?>" style="padding-top:15px;">  <?php echo general_currency_symbol; ?> <?php echo number_format($total_product_price+$tot_taxt_amt_for_od_qty,2); ?></span></td>
											<?php } ?>
											<td class="remove-col"><button class="btn-remove" id="remove_product" type="button" onClick="remove_quickitem_List(<?php echo $cart_id; ?>);"><!--<i class="icon-close"></i>--><img src="assets/images/cart_delete_icon.png"/></button></td>
											<input type="hidden" id="hidden_cart_ID" name="hidden_cart_ID[]" value="<?php echo $cart_id; ?>">
											<input type="hidden" id="hidden_PRDT_ID" name="hidden_PRDT_ID[]" value="<?php echo $product_ID; ?>">
										
										
										</tr>
									<?php }?>
									
									<?php 
								
									 } 
									 ?>
									 <?php 
								
									 
									 if($avail_offer_check_1!='' || $avail_offer_check_2!='' || $avail_offer_check_3 !=''){ ?>
										<tr>
										<td colspan="5">
										<?php if($avail_offer_check_1!=''){ ?>
										
												<div class="card_details ml-auto mr-auto mb-1" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);text-align: center;border-radius: 2px;">
													 

													<div class="container_details" style="background-color: #fff;  padding: 10px 5px 10px;font-size: 1.25rem;">
														<b>  You got <?php echo $offer1; ?> by adding <?php echo $offer_qty1; ?> Product,<a href="offers.php?type=1">View Offer Available Product</a>, Happy Shopping ;) </b>
													</div>
												</div>
											 	
											<?php 
											//$avail_offer_check = '0';
											}
											if($avail_offer_check_2){ ?>
										 
												<div class="card_details ml-auto mr-auto mb-1" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);text-align: center;border-radius: 2px;">
													 

													<div class="container_details" style="background-color: #fff;  padding: 10px 5px 10px;font-size: 1.25rem;">
														<b> You got <?php echo $offer2; ?> by adding <?php echo $offer_qty2; ?> more Product,<a href="offers.php?type=2">View Offer Available Product</a>, Happy Shopping ;) </b>
													</div>
												</div>
											 	
											<?php 
											//$avail_offer_check = '0';
											}
											if($avail_offer_check_3 !=''){ ?>
										 
												<div class="card_details ml-auto mr-auto mb-1" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);text-align: center;border-radius: 2px;">
													 

													<div class="container_details" style="background-color: #fff;  padding: 10px 5px 10px;font-size: 1.25rem;">
														<b>  You got <?php echo $offer3; ?> by adding <?php echo $offer_qty3; ?> more Product,<a href="offers.php?type=3">View Offer Available Product</a>, Happy Shopping ;) </b>
													</div>
												</div>
												
											<?php 
											//$avail_offer_check = '0';
											}
											?>
											</td>
										</tr>
									 <?php } 

									 $offer_AVAILABLE_qty =  getSINGLEDBVALUE('sum(od_qty)', "redeem_offer ='' and offer_type != '' and `od_id`= '$od_id' and od_session = '$sid' and `deleted`='0'", 'js_shop_order_item', 'label');
									
										
										if($offer_AVAILABLE_qty > 0 ){
                                        	$sql_query_token = sqlQUERY_LABEL("SELECT `offer_id` FROM `js_shop_order_item` where redeem_offer='' and offer_type !='' and od_id = '$order_id' and deleted = '0'") or die("#STATUS-SELECT: Getting Status: ".sqlERROR_LABEL());
											while($set_sql_query_token = sqlFETCHARRAY_LABEL($sql_query_token)){
												 
												$offer_id[] = $set_sql_query_token['offer_id'];
											}
										
										  $offers_id = implode(",", $offer_id);

										  $offers_id = rtrim($offers_id,',');
										 
										  $sql_query  = sqlQUERY_LABEL("SELECT `offers_id`,`offers_name`,`offers_type` FROM `js_offers` where  offers_id IN($offers_id) and deleted ='0'") or die("#STATUS-SELECT: Getting Status: ".sqlERROR_LABEL());
											while($set_sql_query = sqlFETCHARRAY_LABEL($sql_query)){
												 
												 $offers_id = $set_sql_query['offers_id'];
												 $offers_name = $set_sql_query['offers_name'];
												 $offers_type = $set_sql_query['offers_type'];
											
											?>
										<tr>
										<td colspan="5">
										<?php 
										
										?>
										
												<div class="card_details ml-auto mr-auto mb-1" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);text-align: center;border-radius: 2px;">
													 

													<div class="container_details" style="background-color: #fff;  padding: 10px 5px 10px;font-size: 1.25rem;">
														<b>Hey! You got <?php echo $offers_name; ?> Offer,<a href="offers.php?type=<?php echo $offers_type ?>&offerID=<?php echo $offers_id ?>">View Offer Available Product</a>, Happy Shopping ;) </b>
													</div>
												</div>
											 	
											<?php 
											//$avail_offer_check = '0';
											 
											 
											?>
											</td>
										</tr>
									 <?php } 
									 }?>
									 </tbody>
								</table><!-- End .table table-wishlist -->
										<?php					
									 if($count_row != 0 || $count_row != '') { ?>
	                			<div class="cart-bottom">
										<?php if($discount_added == 0 ) {
										 	
											$offer_check_qty =  getSINGLEDBVALUE('sum(od_qty)', "offer_id !='0' and offer_type !='0' and od_id = '$od_id' and deleted = '0'", 'js_shop_order_item', 'label');
											if($offer_check_qty =='' ){
												$offer_check_qty ='0';
											}
											// echo $offer_check_qty ;
											?>
			            			<div class="cart-discount">
			            					<div class="input-group">
											
				        						<input type="text" <?php if($offer_check_qty > '0' ){ echo "disabled"; }?> class="form-control" placeholder="coupon code" id="coupon_code">
				        						<input type="hidden"  class="form-control" id="order_total" value="<?php echo number_format(getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total'), 2); ?>">
				        						<input type="hidden" class="form-control" id="od_id" value="<?php echo $od_id; ?>">
				        						<div class="input-group-append">
													<button class="btn btn-outline-primary-2" type="button" onclick = "validate_coupon()">&nbsp;&nbsp;Apply<i class="icon-long-arrow-right"></i></button>
												</div><!-- .End .input-group-append -->
												<?php 
												if($offer_check_qty > '0' ){ ?>
												<span class="text-primary"> Coupon is not Available for these products, Offer already Applied .</span>
												<?php  } ?>
			        						</div><!-- End .input-group -->
											
			        						</div>
											<?php } else { 
											
											$featured_product_data2 = sqlQUERY_LABEL("SELECT od_discount_promo_ID FROM `js_shop_order` WHERE `od_sesid`='$sid' and od_userid = '$logged_user_id' and od_id = '$od_id'") or die("#1-Unable to get records:".sqlERROR_LABEL());
											$check_product_data_num_rows2 = sqlNUMOFROW_LABEL($featured_product_data2);
											if($check_product_data_num_rows2 > 0 ) {
											  while($featured_data2 = sqlFETCHARRAY_LABEL($featured_product_data2)){
												  //$cart_id = $featured_data["cart_id"];
												  $od_discount_promo_ID = $featured_data2["od_discount_promo_ID"];
											  }
											}

											$list_dicount_data_display = sqlQUERY_LABEL("SELECT `promocode_name`,`promocode_code`, `promocode_value`, `promocode_type` FROM `js_promocode` where `promocode_id` = '$od_discount_promo_ID' and `promocode_id` != '0'") or die("Unable to get records:".sqlERROR_LABEL());    
												$check_dicount_record_display = sqlNUMOFROW_LABEL($list_dicount_data_display);      
												  while($row_data = sqlFETCHARRAY_LABEL($list_dicount_data_display)){
													$promocode_name = $row_data["promocode_name"];
													$promocode_code = $row_data["promocode_code"];
													$promocode_value = $row_data["promocode_value"];
													$promocode_type = $row_data["promocode_type"];
												  }
											
											?>
											
				        						<input type="hidden" class="form-control" id="order_total" value="<?php echo number_format(getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total'), 2); ?>">
				        						<input type="hidden" class="form-control" id="od_id" value="<?php echo $od_id; ?>">
												<b>Coupon Applied Successfully (<?php echo $promocode_code; ?>)</b> &nbsp;&nbsp;
												<a href="javascript:;" onclick="remove_coupon(<?php echo $od_id_; ?>)"><span>X Remove Coupon</span></a>
													<?php } ?>
			            		<!--<button type="submit" class="btn btn-outline-dark-2" name="update_cart" value="update_cart"><span>UPDATE CART</span><i class="icon-refresh"></i></button>-->
		            			</div><!-- End .cart-bottom -->
	                		</div><!-- End .col-lg-9 -->
	                		<aside class="col-lg-3">
							<a href="shop.php" class="btn btn-outline-primary-2 btn-order btn-block" >CONTINUE SHOPPING</a>
							<?php if($od_discount_amount == '0.00'){
								$total_shipping = getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total');
							}
							else{ 
								$total_shipping = getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total')-($od_discount_amount);
							 } 
							 if($total_shipping > '500') {
							 ?>
								<!--<span id="summary-cart">
									<div class="summary summary-cart" style="margin-top:10px; margin-bottom:0px; padding:1rem 1rem 1rem;">
										<h3 class="summary-title pb-1">Order Summary</h3>
										<table class="table table-summary">
											<tbody>
												<tr class="summary-subtotal">
													<h6 class="mt-1 mb-1 text-center">Order value is more than 500</h6>
												</tr>
											
												<tr class="summary-total">
													<td style="text-align:center; font-size:1.5rem;">Cheers ! you got free delivery, Happy Shopping</td>
												</tr>
												<tr><img class="ml-auto mr-auto" src="assets/images/free_delivery_giphy.webp" style="height: 80%; width:60%;"/></tr>
											</tbody>
										</table>
									</div>
								</span>-->
							 <?php } ?>
								<span  id="summary-cart">
	                			<div class="summary summary-cart" style="margin-top:10px;">
	                				<h3 class="summary-title">Cart Total</h3><!-- End .summary-title -->
	                				<table class="table table-summary">
	                					<tbody>
	                						<tr class="summary-subtotal">
	                							<td>Subtotal:</td>
												<?php if(sqlNUMOFROW_LABEL($cart_product) == 0) {?>
												<td ><span class="product-price text-dark" style="padding-top:15px;"><?php echo general_currency_symbol; ?> 0.00</span></td>
												<?php } else { ?>
	                							<td id="sub-total"><span class="product-price text-dark" style="padding-top: 15px;"><?php echo general_currency_symbol; ?> <?php echo getTotal_cart_amount($product_ID, $logged_user_id, $sid,'subtotal'); ?></span></td>
												<?php }?>
	                						</tr>
											<!-- End .summary-subtotal -->
	                						<?php /*<tr class="summary-shipping">
	                							<td>Shipping:</td>
	                							<td>&nbsp;</td>
	                						</tr>

	                						<tr class="summary-shipping-row">
	                							<td>
													<div class="custom-control custom-radio">
														<input type="radio" id="free-shipping" name="shipping" data-target="#free-shiping-collapse" data-toggle="collapse" class="custom-control-input">
														<label class="custom-control-label" for="free-shipping">Free Shipping</label>
													</div><!-- End .custom-control -->
	                							</td>
	                							<td>INR 0.00</td>
	                						</tr><!-- End .summary-shipping-row -->
											<tr class="summary-shipping-row">
												<td>
													<div class="collapse " id="free-shiping-collapse" style="width: 120%;text-align: left !important;">
														<div class="card card-body custom-control">
														Free shipping is a marketing tactic used primarily by online vendors and mail-order catalogs as a sales strategy to attract customers.
														</div>
													</div>
												</td>
											</tr>
											<tr class="summary-shipping-row">
	                							<td>
	                								<div class="custom-control custom-radio">
														<input type="radio" id="standart-shipping" name="shipping" data-target="#standard-shiping-collapse" data-toggle="collapse" class="custom-control-input">
														<label class="custom-control-label" for="standart-shipping">Standard</label>
													</div><!-- End .custom-control -->
	                							</td>
	                							<td>INR 10.00</td>
	                						</tr><!-- End .summary-shipping-row -->
											<tr class="summary-shipping-row">
												<td>
													<div class="collapse " id="standard-shiping-collapse" style="width: 120%;text-align: left !important;">
														<div class="card card-body custom-control">
														Free shipping is a marketing tactic used primarily by online vendors and mail-order catalogs as a sales strategy to attract customers.
														</div>
													</div>
												</td>
											</tr>
	                						<tr class="summary-shipping-row">
	                							<td>
	                								<div class="custom-control custom-radio">
														<input type="radio" id="express-shipping" name="shipping" data-target="#express-shiping-collapse" data-toggle="collapse" class="custom-control-input">
														<label class="custom-control-label" for="express-shipping">Express</label>
													</div><!-- End .custom-control -->
	                							</td>
	                							<td>INR 20.00</td>
	                						</tr><!-- End .summary-shipping-row -->
											<tr class="summary-shipping-row">
												<td>
													<div class="collapse " id="express-shiping-collapse" style="width: 120%;text-align: left !important;">
														<div class="card card-body custom-control">
														Free shipping is a marketing tactic used primarily by online vendors and mail-order catalogs as a sales strategy to attract customers.
														</div>
													</div>
												</td>
											</tr>
	                						<tr class="summary-shipping-estimate">
	                							<td><a href="">Change address</a></td>
	                							<td>&nbsp;</td>
	                						</tr><!-- End .summary-shipping-estimate -->*/?>  
											<?php 
											// echo "select od_discount_amount AS OD_TOTAL_DISCOUNT, od_discount_promo_ID FROM `js_shop_order` where `od_sesid`='$sid' and od_userid = '$logged_user_id' and od_id = '$od_id'";
												$selectod_discount_data_total = sqlQUERY_LABEL("select od_discount_amount, od_discount_promo_ID FROM `js_shop_order` where `od_sesid`='$sid' and od_userid = '$logged_user_id' and od_id = '$od_id'") or die(sqlERROR_LABEL());

												$collect_od_discount_data_total = sqlFETCHASSOC_LABEL($selectod_discount_data_total);
												
												$OD_TOTAL_DISCOUNT = $collect_od_discount_data_total['od_discount_amount'];
											?>
											<?php if($OD_TOTAL_DISCOUNT > 0){ ?>
											<tr class="summary-total">
	                							<td>Discount:</td>
												<?php if(sqlNUMOFROW_LABEL($cart_product) == 0) {?>
	                							<td ><span class="product-price text-dark" style="padding-top:15px;"><?php echo general_currency_symbol; ?> 0.00</span></td>
												<?php } else { ?>
												<td ><span class="product-price" style="padding-top:15px;"><?php echo general_currency_symbol; ?> <?php echo $od_discount_amount; ?></span></td>
												<?php } ?>
	                						</tr><!-- End .summary-total -->
											<?php } ?>
										
	                						<tr class="summary-total">
	                							<td>Total:</td>
												<?php if(sqlNUMOFROW_LABEL($cart_product) == 0) {?>
	                							<td ><span class="product-price text-dark" style="padding-top:15px;"><?php echo general_currency_symbol; ?> 0.00</span></td>
												<?php } else {?>
												<?php if($od_discount_amount == '0.00'){?>
												<td ><span class="product-price" style="padding-top:15px;"><?php echo general_currency_symbol; ?> <?php echo number_format(getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total'), 2); ?></span></td>
												<?php }
												else{?>
												
												<td ><span class="product-price" style="padding-top:15px;"><?php echo general_currency_symbol; ?> <?php echo number_format(getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total')-($od_discount_amount),2); ?></span></td>
												
												<?php }
                                                     } ?>
	                						</tr><!-- End .summary-total -->	
											<!--<tr>
													<h6 class="mt-1 mb-1 text-center">Order value is more than 500</h6>
												</tr>
											
												<tr class="summary-total">
													<td style="text-align:center; font-size:1.5rem;">Cheers ! you got free delivery, Happy Shopping</td>
												</tr>
												<tr><img class="ml-auto mr-auto" src="assets/images/free_delivery_giphy.webp" style="height: 80%; width:60%;"/></tr>-->
												<?php if($od_discount_amount == '0.00'){
														$total_shipping = getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total');
													}
													else{ 
														$total_shipping = getTotal_cart_amount($product_ID, $logged_user_id,$sid,'total')-($od_discount_amount);
													 } 
													 if($total_shipping > '1500') { ?>
												<div class="card_details ml-auto mr-auto mb-1" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);text-align: center;border-radius: 2px;">
													<div class="header_details" style="padding: 7px; background-color: #E3E4E8;color: #333;">
														<div style="font-size: 1.2rem;">Order value is more than <small  style="font-family: 'Jost', sans-serif; font-size : 100% !important; color: #333;"><?php echo general_currency_symbol; ?></small> 1500</div>
													</div>

													<div class="container_details" style="background-color: #fff; color: #c66; padding: 10px 5px 10px;font-size: 1.25rem;">
														<b>Cheers ! You got free delivery, Happy Shopping</b>
													</div>
												</div>
												 <?php } else { ?>
									
									<div class="card_details ml-auto mr-auto mb-1" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);text-align: center;border-radius: 2px;">
										<div class="header_details" style="padding: 7px; background-color: #E3E4E8;color: #333;">
										  <div style="font-size: 1.1rem;">Free shipping, order value must be greater than <b><small  style="font-family: 'Jost', sans-serif; font-size : 100% !important; color: #666;"><?php echo general_currency_symbol; ?></small> 1500</b></div>
										  </div>

										  <div class="container_details" style="background-color: #fff; color: #c66; padding: 10px 10px 10px;font-size: 1.25rem;">
											<b>Free delivery waiting! Add <b><small  style="font-family: 'Jost', sans-serif; font-size : 100% !important;"><?php echo general_currency_symbol; ?></small> <?php echo (1500 - $total_shipping) ?></b> more to get it</b>
										  </div>
										</div>
												 <?php } 
												 
												 
												
												 ?>
										 
										
	                					</tbody>
	                				</table><!-- End .table table-summary -->
									<?php if(sqlNUMOFROW_LABEL($cart_product) == 0) { ?>
	                				<a href="shop.php" class="btn btn-outline-primary-2 btn-order btn-block">CONTINUE SHOPPING</a>
									<?php } else { ?>
									<?php if($logged_user_id) {?>
									<a href="checkout.php" class="btn btn-outline-primary-2 btn-order btn-block">CONTINUE TO CHECKOUT</a>
									<?php } else { ?> 
									<a href="#signin-modal" data-toggle="modal" class="btn btn-outline-primary-2 btn-order btn-block" role="button" aria-haspopup="true" aria-expanded="false" data-display="static" title="Compare Products" aria-label="Compare Products">CONTINUE TO CHECKOUT</a>
									
									<?php } }?>
	                			</div><!-- End .summary -->
								</span>
						<?php  } ?>
	                		</aside><!-- End .col-lg-3 -->
							
	                	</div><!-- End .row -->
						</form>
	                </div><!-- End .container -->
                </div><!-- End .cart -->
				<?php }  
				  else { ?>
					<div class="text-center mt-150 mb-150">
					<h2><?php echo $carthero_text; ?></h2>
					<div style="padding-left:500px;">
					<img src="<?php echo SITEHOME; ?>assets/images/no-item-cart.png" />
					</div>
					<p><?php echo $cartdescription; ?></p>
					<a href="shop.php" class="btn button btn-outline-primary-2 col-2 mt-2 mb-2"> Shop Now </a>
					</div>
				<?php } ?>
            </div><!-- End .page-content -->
        </main><!-- End .main -->

    <?php

		//list of module view templates
		$loadFUNCTIONS = array(
		'__footer',
		'__scripts'
		);
		
		echo $homepage_propertyclass->loadPAGE($loadFUNCTIONS); 
	
	?>
</div><!-- End .page-wrapper -->
</body>

</html>
